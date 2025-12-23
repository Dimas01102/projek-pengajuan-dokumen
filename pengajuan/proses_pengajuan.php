<?php
// PENTING: Jangan output apapun sebelum ini
ob_start(); // Mulai output buffering

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Clear any previous output
ob_clean();

header('Content-Type: application/json');
require_login();
check_role(['warga']);

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];

    // Debug log
    error_log("=== PROSES PENGAJUAN START ===");
    error_log("POST data: " . print_r($_POST, true));
    error_log("FILES data: " . print_r($_FILES, true));

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

    if (!isset($_FILES['berkas']) || empty($_FILES['berkas']['name'])) {
        $errors[] = 'File upload wajib diisi';
    } else {
        $uploaded_files = $_FILES['berkas'];
        
        // Hitung jumlah file yang benar-benar diupload
        $total_uploaded = 0;
        if (is_array($uploaded_files['name'])) {
            // Multiple files
            foreach ($uploaded_files['name'] as $name) {
                if (!empty($name)) {
                    $total_uploaded++;
                }
            }
        } else {
            // Single file
            if (!empty($uploaded_files['name'])) {
                $total_uploaded = 1;
            }
        }

        error_log("Required files: $required_files, Uploaded: $total_uploaded");

        if ($total_uploaded < $required_files) {
            $errors[] = "Wajib upload $required_files file. Anda hanya upload $total_uploaded file.";
        }

        // Validasi setiap file
        if (is_array($uploaded_files['name'])) {
            for ($i = 0; $i < count($uploaded_files['name']); $i++) {
                if (empty($uploaded_files['name'][$i])) continue;

                $file_name = $uploaded_files['name'][$i];
                $file_error = $uploaded_files['error'][$i];
                $file_size = $uploaded_files['size'][$i];

                $label = isset($upload_config['labels'][$i]) ? $upload_config['labels'][$i] : "File " . ($i + 1);

                if ($file_error !== UPLOAD_ERR_OK) {
                    $errors[] = "Terjadi kesalahan saat upload $label (Error code: $file_error)";
                    continue;
                }

                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                // HANYA PDF
                if ($file_ext !== 'pdf') {
                    $errors[] = "$label: Hanya file PDF yang diperbolehkan (File: $file_name)";
                }

                if ($file_size > 5242880) { // 5MB
                    $errors[] = "$label: Ukuran file terlalu besar (maksimal 5MB, ukuran: " . round($file_size/1048576, 2) . "MB)";
                }
            }
        } else {
            // Single file validation
            if (!empty($uploaded_files['name'])) {
                $file_name = $uploaded_files['name'];
                $file_error = $uploaded_files['error'];
                $file_size = $uploaded_files['size'];

                $label = isset($upload_config['labels'][0]) ? $upload_config['labels'][0] : "File";

                if ($file_error !== UPLOAD_ERR_OK) {
                    $errors[] = "Terjadi kesalahan saat upload $label (Error code: $file_error)";
                } else {
                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                    if ($file_ext !== 'pdf') {
                        $errors[] = "$label: Hanya file PDF yang diperbolehkan";
                    }

                    if ($file_size > 5242880) {
                        $errors[] = "$label: Ukuran file terlalu besar (maksimal 5MB)";
                    }
                }
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
        error_log("Validation errors: " . implode(', ', $errors));
        ob_end_clean();
        echo json_encode(['status' => 'error', 'message' => implode('<br>', $errors)]);
        exit;
    }

    // Proses simpan
    try {
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

        error_log("Insert query: $query");

        if (!mysqli_query($conn, $query)) {
            throw new Exception('Gagal menyimpan pengajuan: ' . mysqli_error($conn));
        }

        $id_pengajuan = mysqli_insert_id($conn);
        error_log("Pengajuan inserted with ID: $id_pengajuan");

        // Insert riwayat status
        $riwayat_query = "INSERT INTO t_riwayat_status (id_pengajuan, id_status, catatan, diubah_oleh) 
                         VALUES ('$id_pengajuan', 1, 'Pengajuan dibuat', '$user_id')";
        
        if (!mysqli_query($conn, $riwayat_query)) {
            throw new Exception('Gagal menyimpan riwayat: ' . mysqli_error($conn));
        }

        // Upload semua file
        $uploaded_files = $_FILES['berkas'];
        $upload_success = true;
        $upload_errors = [];

        // Handle array atau single file
        if (is_array($uploaded_files['name'])) {
            // Multiple files
            $total_files = count($uploaded_files['name']);
            
            for ($i = 0; $i < $total_files; $i++) {
                if (empty($uploaded_files['name'][$i])) continue;

                // Reconstruct $_FILES format untuk setiap file
                $single_file = array(
                    'name' => $uploaded_files['name'][$i],
                    'type' => $uploaded_files['type'][$i],
                    'tmp_name' => $uploaded_files['tmp_name'][$i],
                    'error' => $uploaded_files['error'][$i],
                    'size' => $uploaded_files['size'][$i]
                );

                $label = isset($upload_config['labels'][$i]) ? $upload_config['labels'][$i] : "Dokumen " . ($i + 1);

                error_log("Uploading file $i: " . $single_file['name'] . " with label: $label");

                $upload_result = upload_file_with_label($single_file, $id_pengajuan, $label);

                if (!$upload_result['status']) {
                    $upload_success = false;
                    $upload_errors[] = $label . ': ' . $upload_result['message'];
                    error_log("Upload failed for $label: " . $upload_result['message']);
                }
            }
        } else {
            // Single file
            if (!empty($uploaded_files['name'])) {
                $label = isset($upload_config['labels'][0]) ? $upload_config['labels'][0] : "Dokumen";
                
                error_log("Uploading single file: " . $uploaded_files['name'] . " with label: $label");
                
                $upload_result = upload_file_with_label($uploaded_files, $id_pengajuan, $label);

                if (!$upload_result['status']) {
                    $upload_success = false;
                    $upload_errors[] = $label . ': ' . $upload_result['message'];
                    error_log("Upload failed for $label: " . $upload_result['message']);
                }
            }
        }

        if (!$upload_success) {
            // Rollback jika ada file gagal
            mysqli_query($conn, "DELETE FROM t_pengajuan WHERE id_pengajuan = '$id_pengajuan'");
            mysqli_query($conn, "DELETE FROM t_riwayat_status WHERE id_pengajuan = '$id_pengajuan'");
            mysqli_query($conn, "DELETE FROM t_berkas_dokumen WHERE id_pengajuan = '$id_pengajuan'");

            error_log("Upload failed, rolled back pengajuan ID: $id_pengajuan");
            ob_end_clean();
            echo json_encode(['status' => 'error', 'message' => 'Gagal upload file:<br>' . implode('<br>', $upload_errors)]);
            exit;
        }

        error_log("=== PROSES PENGAJUAN SUCCESS ===");
        ob_end_clean();
        echo json_encode(['status' => 'success', 'message' => 'Pengajuan berhasil dikirim dengan nomor: ' . $nomor_pengajuan]);
        
    } catch (Exception $e) {
        error_log("Exception in proses_pengajuan: " . $e->getMessage());
        ob_end_clean();
        echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
    }
} else {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}