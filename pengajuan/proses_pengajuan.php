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

    // Validasi file upload
    if (!isset($_FILES['berkas']) || $_FILES['berkas']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Berkas pendukung wajib diupload';
    } elseif ($_FILES['berkas']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Terjadi kesalahan saat upload berkas';
    } else {
        $file_ext = strtolower(pathinfo($_FILES['berkas']['name'], PATHINFO_EXTENSION));
        if (!in_array($file_ext, ['pdf', 'jpg', 'jpeg', 'png'])) {
            $errors[] = 'Format file tidak diizinkan. Hanya PDF, JPG, JPEG, dan PNG';
        }
        if ($_FILES['berkas']['size'] > 5242880) {
            $errors[] = 'Ukuran file terlalu besar. Maksimal 5MB';
        }
    }

    // Validasi field dinamis
    if (!empty($_POST['id_jenis'])) {
        $id_jenis = clean_input($_POST['id_jenis']);
        $jenis_query = "SELECT deskripsi FROM t_jenis_dokumen WHERE id_jenis = '$id_jenis'";
        $jenis_result = mysqli_query($conn, $jenis_query);

        if ($jenis_result && mysqli_num_rows($jenis_result) > 0) {
            $jenis_data = mysqli_fetch_assoc($jenis_result);
            $decoded = decode_deskripsi_with_config($jenis_data['deskripsi']);
            $field_config = $decoded['field_config'];

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
        }
    }

    if (!empty($errors)) {
        echo json_encode(['status' => 'error', 'message' => implode('<br>', $errors)]);
        exit;
    }

    // Proses simpan
    $id_jenis = clean_input($_POST['id_jenis']);
    $keperluan = clean_input($_POST['keperluan']);
    $nomor_pengajuan = generate_nomor_pengajuan();
    $keterangan = array();

    // Ambil field config dan simpan dengan label asli sebagai key
    $jenis_query = "SELECT deskripsi FROM t_jenis_dokumen WHERE id_jenis = '$id_jenis'";
    $jenis_result = mysqli_query($conn, $jenis_query);

    if ($jenis_result && mysqli_num_rows($jenis_result) > 0) {
        $jenis_data = mysqli_fetch_assoc($jenis_result);
        $decoded = decode_deskripsi_with_config($jenis_data['deskripsi']);
        $field_config = $decoded['field_config'];

        if (!empty($field_config)) {
            foreach ($field_config as $field) {
                $field_name = strtolower(str_replace([' ', '.'], ['_', ''], $field['label']));
                if (isset($_POST[$field_name])) {
                    // PENTING: Simpan dengan label asli (bukan field_name)
                    $keterangan[$field['label']] = clean_input($_POST[$field_name]);
                }
            }
        }
    }

    $keterangan_json = json_encode(sanitize_json_input($keterangan), JSON_UNESCAPED_UNICODE);
    $keterangan_escaped = mysqli_real_escape_string($conn, $keterangan_json);

    $query = "INSERT INTO t_pengajuan (nomor_pengajuan, id_pengguna, id_jenis, keperluan, keterangan, id_status) 
              VALUES ('$nomor_pengajuan', '$user_id', '$id_jenis', '$keperluan', '$keterangan_escaped', 1)";

    if (mysqli_query($conn, $query)) {
        $id_pengajuan = mysqli_insert_id($conn);

        $riwayat_query = "INSERT INTO t_riwayat_status (id_pengajuan, id_status, catatan, diubah_oleh) 
                         VALUES ('$id_pengajuan', 1, 'Pengajuan dibuat', '$user_id')";
        mysqli_query($conn, $riwayat_query);

        $upload_result = upload_file($_FILES['berkas'], $id_pengajuan);

        if (!$upload_result['status']) {
            mysqli_query($conn, "DELETE FROM t_pengajuan WHERE id_pengajuan = '$id_pengajuan'");
            mysqli_query($conn, "DELETE FROM t_riwayat_status WHERE id_pengajuan = '$id_pengajuan'");
            echo json_encode(['status' => 'error', 'message' => 'Gagal upload berkas: ' . $upload_result['message']]);
            exit;
        }

        echo json_encode(['status' => 'success', 'message' => 'Pengajuan berhasil dikirim dengan nomor: ' . $nomor_pengajuan]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan pengajuan: ' . mysqli_error($conn)]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>