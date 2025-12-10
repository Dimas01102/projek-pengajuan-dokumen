<?php
// includes/functions.php
// Fungsi-fungsi utilitas

// ============================================
// FORMAT TANGGAL
// ============================================

/**
 * Format tanggal Indonesia
 * @param string $date - String tanggal
 * @return string - Format Indonesia
 */
function format_tanggal($date)
{
    $bulan = array(
        1 => 'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    );

    $timestamp = strtotime($date);
    $hari = date('d', $timestamp);
    $bulan_num = date('n', $timestamp);
    $tahun = date('Y', $timestamp);

    return $hari . ' ' . $bulan[$bulan_num] . ' ' . $tahun;
}

// ============================================
// GENERATE NOMOR
// ============================================

/**
 * Generate nomor pengajuan unik
 * @return string - Nomor pengajuan
 */
function generate_nomor_pengajuan()
{
    $date = date('Ymd');
    $random = rand(1000, 9999);
    return 'DOC-' . $date . '-' . $random;
}

/**
 * Generate nomor surat
 * @param string $jenis_dokumen - Nama jenis dokumen
 * @return string - Nomor surat
 */
function generate_nomor_surat($jenis_dokumen)
{
    $date = date('Y/m');
    $random = rand(100, 999);

    // Kode jenis dokumen
    $kode = '';
    if (strpos($jenis_dokumen, 'SKTM') !== false) {
        $kode = 'SKTM';
    } elseif (strpos($jenis_dokumen, 'SKU') !== false) {
        $kode = 'SKU';
    } elseif (strpos($jenis_dokumen, 'SKD') !== false) {
        $kode = 'SKD';
    } else {
        $kode = 'DOC';
    }

    return $random . '/' . $kode . '/' . $date;
}

// ============================================
// FILE UPLOAD FUNCTIONS
// ============================================

/**
 * Upload file - HANYA PDF
 * @param array $file - File dari $_FILES
 * @param string $id_pengajuan - ID pengajuan
 * @return array - Status upload
 */
function upload_file($file, $id_pengajuan)
{
    global $conn;

    $target_dir = UPLOAD_PATH;
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // Hanya izinkan PDF
    $allowed = array('pdf');

    if (!in_array($file_ext, $allowed)) {
        return array('status' => false, 'message' => 'Hanya file PDF yang diperbolehkan!');
    }

    // Validasi MIME type untuk keamanan ekstra
    $mime_type = mime_content_type($file_tmp);
   

    if ($mime_type !== 'application/pdf') {
        return array('status' => false, 'message' => 'File yang diupload bukan PDF yang valid!');
    }

    if ($file_size > 5000000) { // 5MB
        return array('status' => false, 'message' => 'Ukuran file terlalu besar (maksimal 5MB)');
    }

    // Generate unique filename
    $new_filename = 'DOC_' . $id_pengajuan . '_' . time() . '.' . $file_ext;
    $target_file = $target_dir . $new_filename;

    if (move_uploaded_file($file_tmp, $target_file)) {
        // Save to database
        $file_name_escaped = mysqli_real_escape_string($conn, $file_name);
        $query = "INSERT INTO t_berkas_dokumen (id_pengajuan, nama_file, path_file, ukuran_file, tipe_file) 
                  VALUES ('$id_pengajuan', '$file_name_escaped', '$new_filename', '$file_size', '$file_ext')";
        mysqli_query($conn, $query);

        return array('status' => true, 'message' => 'File berhasil diupload', 'filename' => $new_filename);
    } else {
        return array('status' => false, 'message' => 'Gagal mengupload file');
    }
}

/**
 * Upload file dengan label - untuk multiple upload
 * @param array $file - File dari $_FILES
 * @param string $id_pengajuan - ID pengajuan
 * @param string $label - Label dokumen
 * @return array - Status upload
 */
function upload_file_with_label($file, $id_pengajuan, $label = 'Dokumen Pendukung')
{
    global $conn;

    $target_dir = UPLOAD_PATH;
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // Hanya izinkan PDF
    if ($file_ext !== 'pdf') {
        return array('status' => false, 'message' => 'Hanya file PDF yang diperbolehkan!');
    }

     // Validasi MIME type untuk keamanan ekstra
    $mime_type = mime_content_type($file_tmp);

    if ($mime_type !== 'application/pdf') {
        return array('status' => false, 'message' => 'File yang diupload bukan PDF yang valid!');
    }

    if ($file_size > 5000000) { // 5MB
        return array('status' => false, 'message' => 'Ukuran file terlalu besar (maksimal 5MB)');
    }

    // Generate unique filename dengan label
    $label_slug = strtolower(str_replace([' ', '.', ','], '_', $label));
    $new_filename = 'DOC_' . $id_pengajuan . '_' . $label_slug . '_' . time() . '.' . $file_ext;
    $target_file = $target_dir . $new_filename;

    if (move_uploaded_file($file_tmp, $target_file)) {
        //  Simpan nama_file dengan format: LABEL|||FILENAME
        // Ini untuk menyimpan label tanpa perlu alter database
        $nama_file_with_label = $label . '|||' . $file_name;
        $nama_file_escaped = mysqli_real_escape_string($conn, $nama_file_with_label);

        $query = "INSERT INTO t_berkas_dokumen (id_pengajuan, nama_file, path_file, ukuran_file, tipe_file) 
                  VALUES ('$id_pengajuan', '$nama_file_escaped', '$new_filename', '$file_size', '$file_ext')";
        
        if (!mysqli_query($conn, $query)) {
            // Jika gagal insert, hapus file yang sudah terupload
            unlink($target_file);
            return array('status' => false, 'message' => 'Gagal menyimpan ke database: ' . mysqli_error($conn));
        }

        return array('status' => true, 'message' => 'File berhasil diupload', 'filename' => $new_filename);
    } else {
        return array('status' => false, 'message' => 'Gagal mengupload file');
    }
}

// ============================================
// FILE PARSING 
// ============================================

/**
 * Helper untuk parse label dari nama_file
 * @param string $nama_file - Nama file dengan format "LABEL|||FILENAME" atau "FILENAME"
 * @return array - Array dengan key 'label' dan 'filename'
 */
function parse_file_label($nama_file)
{
    // Cek apakah ada delimiter |||
    if (strpos($nama_file, '|||') !== false) {
        $parts = explode('|||', $nama_file, 2);
        return array(
            'label' => trim($parts[0]),      // Label bersih TANPA |||
            'filename' => trim($parts[1])    // Filename asli TANPA |||
        );
    }
    
    // Jika tidak ada delimiter, return default
    return array(
        'label' => 'Dokumen Pendukung',
        'filename' => $nama_file
    );
}

// ============================================
// DISPLAY HELPERS
// ============================================

/**
 * Get status badge HTML
 * @param string $status_name - Nama status
 * @param string $warna - Warna badge
 * @return string - HTML badge
 */
function get_status_badge($status_name, $warna)
{
    return '<span class="badge bg-' . $warna . '">' . $status_name . '</span>';
}

/**
 * Format file size ke format readable
 * @param int $bytes - Ukuran dalam bytes
 * @return string - Format readable
 */
function format_file_size($bytes)
{
    if (!$bytes || $bytes == 0) return '0 Bytes';
    
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

// ============================================
// JSON SANITIZATION
// ============================================

/**
 * Sanitize JSON input
 * @param array $data - Data array
 * @return array - Sanitized data
 */
function sanitize_json_input($data)
{
    $sanitized = array();
    foreach ($data as $key => $value) {
        $sanitized[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }
    return $sanitized;
}

// ============================================
// CONFIG ENCODING
// ============================================

/**
 * Encode deskripsi dengan field config DAN upload config
 * @param string $deskripsi_text - Teks deskripsi
 * @param array $field_config_array - Array field config
 * @param array $upload_config_array - Array upload config (optional)
 * @return string - Encoded string
 */
function encode_deskripsi_with_config($deskripsi_text, $field_config_array, $upload_config_array = null)
{
    $config = array(
        'field_config' => $field_config_array
    );

    // Tambahkan upload config jika ada
    if ($upload_config_array !== null) {
        $config['upload_config'] = $upload_config_array;
    } else {
        // Default: 1 file dengan label "Dokumen Pendukung"
        $config['upload_config'] = array(
            'jumlah' => 1,
            'labels' => array('Dokumen Pendukung')
        );
    }

    $config_json = json_encode($config);
    return $deskripsi_text . "###JSON_CONFIG###" . $config_json;
}

/**
 * Decode deskripsi dengan config
 * @param string $deskripsi_gabungan - String gabungan deskripsi + config
 * @return array - Array dengan key 'deskripsi', 'field_config', 'upload_config'
 */
function decode_deskripsi_with_config($deskripsi_gabungan)
{
    if (strpos($deskripsi_gabungan, "###JSON_CONFIG###") !== false) {
        $parts = explode("###JSON_CONFIG###", $deskripsi_gabungan, 2);
        $decoded_config = json_decode($parts[1], true);

        // Backward compatibility: jika format lama (langsung array field_config)
        if (isset($decoded_config[0])) {
            return [
                'deskripsi' => $parts[0],
                'field_config' => $decoded_config,
                'upload_config' => array(
                    'jumlah' => 1,
                    'labels' => array('Dokumen Pendukung')
                )
            ];
        }

        // Format baru (object dengan field_config dan upload_config)
        return [
            'deskripsi' => $parts[0],
            'field_config' => $decoded_config['field_config'] ?? [],
            'upload_config' => $decoded_config['upload_config'] ?? array(
                'jumlah' => 1,
                'labels' => array('Dokumen Pendukung')
            )
        ];
    }

    // Jika tidak ada config sama sekali (format paling lama)
    return [
        'deskripsi' => $deskripsi_gabungan,
        'field_config' => [],
        'upload_config' => array(
            'jumlah' => 1,
            'labels' => array('Dokumen Pendukung')
        )
    ];
}
?>