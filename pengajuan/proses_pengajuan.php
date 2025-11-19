<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');
require_login();
check_role(['warga']);

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];

    // Validasi dasar
    if (empty($_POST['id_jenis'])) $errors[] = 'Jenis dokumen harus dipilih';
    if (empty($_POST['keperluan'])) $errors[] = 'Keperluan harus diisi';

    // Get upload config untuk validasi
    $id_jenis = clean_input($_POST['id_jenis']);
    $jenis_query = "SELECT deskripsi FROM t_jenis_dokumen WHERE id_jenis = '$id_jenis'";
    $jenis_result = mysqli_query($conn, $jenis_query);
    $upload_config = null;
    $field_config = [];

    if ($jenis_result && mysqli_num_rows($jenis_result) > 0) {
        $jenis_data = mysqli_fetch_assoc($jenis_result);
        $decoded = decode_deskripsi_with_config($jenis_data['deskripsi']);
        $field_config = $decoded['field_config'];
        $upload_config = $decoded['upload_config'];
    }

    // Validasi jumlah file upload
    $required_files = $upload_config['jumlah'] ?? 1;

    if (!isset($_FILES['berkas']) || !isset($_FILES['berkas']['name'])) {
        $errors[] = 'File upload wajib diisi';
    } else {
        $uploaded_files = $_FILES['berkas'];
        $total_uploaded = is_array($uploaded_files['name']) ? count($uploaded_files['name']) : 1;

        if ($total_uploaded < $required_files) {
            $errors[] = "Wajib upload $required_files file. Anda hanya upload $total_uploaded file.";
        }

        // Validasi setiap file
        for ($i = 0; $i < $total_uploaded; $i++) {
            $file_name = is_array($uploaded_files['name']) ? $uploaded_files['name'][$i] : $uploaded_files['name'];
            $file_error = is_array($uploaded_files['error']) ? $uploaded_files['error'][$i] : $uploaded_files['error'];
            $file_size = is_array($uploaded_files['size']) ? $uploaded_files['size'][$i] : $uploaded_files['size'];

            $label = isset($upload_config['labels'][$i]) ? $upload_config['labels'][$i] : "File " . ($i + 1);

            if ($file_error === UPLOAD_ERR_NO_FILE) {
                $errors[] = "$label wajib diupload";
                continue;
            }

            if ($file_error !== UPLOAD_ERR_OK) {
                $errors[] = "Terjadi kesalahan saat upload $label";
                continue;
            }

            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            // HANYA PDF
            if ($file_ext !== 'pdf') {
                $errors[] = "$label: Hanya file PDF yang diperbolehkan";
            }

            if ($file_size > 5242880) { // 5MB
                $errors[] = "$label: Ukuran file terlalu besar (maksimal 5MB)";
            }
        }
    }

    // Validasi field dinamis
    if (!empty($field_config)) {
        foreach ($field_config as $field) {
            if ($field['required']) {
                $field_name = strtolower(str_replace([' ', '.'], ['_', ''], $field['label']));
                if (!isset($_POST[$field_name]) || trim($_POST[$field_name]) === '') {
                    $errors[] = $field['label'] . ' harus diisi';
                }
            }
        }
    }

    if (!empty($errors)) {
        echo json_encode(['status' => 'error', 'message' => implode('<br>', $errors)]);
        exit;
    }

    // Proses simpan
    $keperluan = clean_input($_POST['keperluan']);
    $nomor_pengajuan = generate_nomor_pengajuan();
    $keterangan = array();

    // Simpan field config dengan label asli
    if (!empty($field_config)) {
        foreach ($field_config as $field) {
            $field_name = strtolower(str_replace([' ', '.'], ['_', ''], $field['label']));
            if (isset($_POST[$field_name])) {
                $keterangan[$field['label']] = clean_input($_POST[$field_name]);
            }
        }
    }

    $keterangan_json = json_encode(sanitize_json_input($keterangan), JSON_UNESCAPED_UNICODE);
    $keterangan_escaped = mysqli_real_escape_string($conn, $keterangan_json);

    // Insert pengajuan
    $query = "INSERT INTO t_pengajuan (nomor_pengajuan, id_pengguna, id_jenis, keperluan, keterangan, id_status) 
              VALUES ('$nomor_pengajuan', '$user_id', '$id_jenis', '$keperluan', '$keterangan_escaped', 1)";

    if (mysqli_query($conn, $query)) {
        $id_pengajuan = mysqli_insert_id($conn);

        // Insert riwayat status
        $riwayat_query = "INSERT INTO t_riwayat_status (id_pengajuan, id_status, catatan, diubah_oleh) 
                         VALUES ('$id_pengajuan', 1, 'Pengajuan dibuat', '$user_id')";
        mysqli_query($conn, $riwayat_query);

        // Upload semua file
        $uploaded_files = $_FILES['berkas'];
        $upload_success = true;
        $upload_errors = [];

        $total_files = is_array($uploaded_files['name']) ? count($uploaded_files['name']) : 1;

        for ($i = 0; $i < $total_files; $i++) {
            // Reconstruct $_FILES format untuk setiap file
            $single_file = array(
                'name' => is_array($uploaded_files['name']) ? $uploaded_files['name'][$i] : $uploaded_files['name'],
                'type' => is_array($uploaded_files['type']) ? $uploaded_files['type'][$i] : $uploaded_files['type'],
                'tmp_name' => is_array($uploaded_files['tmp_name']) ? $uploaded_files['tmp_name'][$i] : $uploaded_files['tmp_name'],
                'error' => is_array($uploaded_files['error']) ? $uploaded_files['error'][$i] : $uploaded_files['error'],
                'size' => is_array($uploaded_files['size']) ? $uploaded_files['size'][$i] : $uploaded_files['size']
            );

            $label = isset($upload_config['labels'][$i]) ? $upload_config['labels'][$i] : "Dokumen " . ($i + 1);

            $upload_result = upload_file_with_label($single_file, $id_pengajuan, $label);

            if (!$upload_result['status']) {
                $upload_success = false;
                $upload_errors[] = $label . ': ' . $upload_result['message'];
            }
        }

        if (!$upload_success) {
            // Rollback jika ada file gagal
            mysqli_query($conn, "DELETE FROM t_pengajuan WHERE id_pengajuan = '$id_pengajuan'");
            mysqli_query($conn, "DELETE FROM t_riwayat_status WHERE id_pengajuan = '$id_pengajuan'");
            mysqli_query($conn, "DELETE FROM t_berkas_dokumen WHERE id_pengajuan = '$id_pengajuan'");

            echo json_encode(['status' => 'error', 'message' => 'Gagal upload file:<br>' . implode('<br>', $upload_errors)]);
            exit;
        }

        echo json_encode(['status' => 'success', 'message' => 'Pengajuan berhasil dikirim dengan nomor: ' . $nomor_pengajuan]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan pengajuan: ' . mysqli_error($conn)]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
