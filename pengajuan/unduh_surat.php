<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

require_once '../vendor/autoload.php';

require_login();
check_role(['warga']);

if (!isset($_GET['id'])) redirect('lihat_status.php');

$id_pengajuan = clean_input($_GET['id']);
$user_id = $_SESSION['user_id'];

// Query untuk ambil data 
$query = "SELECT p.*, j.nama_dokumen, j.deskripsi as deskripsi_jenis, 
          u.nama_lengkap, u.nik, u.alamat, s.nomor_surat, s.file_path
          FROM t_pengajuan p
          JOIN t_jenis_dokumen j ON p.id_jenis = j.id_jenis
          JOIN t_pengguna u ON p.id_pengguna = u.id_pengguna
          LEFT JOIN t_surat_terbit s ON p.id_pengajuan = s.id_pengajuan
          WHERE p.id_pengajuan = '$id_pengajuan' 
          AND p.id_pengguna = '$user_id'
          AND p.id_status IN (2, 3)";

$result = mysqli_query($conn, $query);
if (mysqli_num_rows($result) == 0) {
    echo "<script>alert('Surat tidak ditemukan atau belum disetujui'); window.location.href = 'lihat_status.php';</script>";
    exit;
}

$data = mysqli_fetch_assoc($result);
$keterangan = json_decode($data['keterangan'], true) ?: [];
$decoded_jenis = decode_deskripsi_with_config($data['deskripsi_jenis']);
$field_config = $decoded_jenis['field_config'] ?: [];

// Fungsi ambil nilai field 
function get_field_val($ket, $label)
{
    if (isset($ket[$label])) return $ket[$label];
    $old_key = strtolower(str_replace([' ', '.'], ['_', ''], $label));
    return $ket[$old_key] ?? '';
}

// Fungsi untuk convert PNG dengan alpha channel ke JPEG untuk TCPDF
function convertPNGtoJPG($imagePath) {
    if (!file_exists($imagePath)) {
        return null;
    }
    
    $ext = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
    
    // Jika bukan PNG, return langsung
    if ($ext !== 'png') {
        return $imagePath;
    }
    
    // Cek apakah GD tersedia
    if (!extension_loaded('gd') || !function_exists('imagecreatefrompng')) {
        return null;
    }
    
    try {
        // Buat temporary file
        $tempPath = sys_get_temp_dir() . '/tcpdf_converted_' . md5($imagePath . time()) . '.jpg';
        
        // Load PNG
        $source = @imagecreatefrompng($imagePath);
        if (!$source) {
            return null;
        }
        
        // Dapatkan dimensi
        $width = imagesx($source);
        $height = imagesy($source);
        
        // Buat image baru dengan background putih
        $output = imagecreatetruecolor($width, $height);
        
        // Set background putih
        $white = imagecolorallocate($output, 255, 255, 255);
        imagefill($output, 0, 0, $white);
        
        // Disable alpha blending untuk background
        imagealphablending($output, true);
        
        // Copy source ke output (flatten alpha channel)
        imagecopy($output, $source, 0, 0, 0, 0, $width, $height);
        
        // Save sebagai JPEG dengan kualitas tinggi
        $success = imagejpeg($output, $tempPath, 95);
        
        // Cleanup memory
        imagedestroy($source);
        imagedestroy($output);
        
        if ($success && file_exists($tempPath)) {
            return $tempPath;
        }
        
        return null;
    } catch (Exception $e) {
        error_log("Error converting PNG: " . $e->getMessage());
        return null;
    }
}

// Fungsi untuk mendapatkan nama bulan dalam bahasa Indonesia
function getBulanIndonesia($bulan) {
    $bulanIndo = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    return $bulanIndo[$bulan];
}

$tanggal_sekarang = date('d') . ' ' . getBulanIndonesia(date('n')) . ' ' . date('Y');

// Handle PDF Download dengan TCPDF
if (isset($_GET['download']) && $_GET['download'] == 'pdf') {
    try {
        // Buat objek TCPDF
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        
        // Set dokumen info
        $pdf->SetCreator('Sistem Pengajuan Dokumen');
        $pdf->SetAuthor('Pemerintah Desa/Kelurahan');
        $pdf->SetTitle($data['nama_dokumen']);
        
        // Hapus header/footer default
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Set margins - perbesar margin bawah untuk footer
        $pdf->SetMargins(20, 15, 20);
        $pdf->SetAutoPageBreak(true, 25);
        
        // Tambah halaman
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('times', '', 12);
        
        // HEADER dengan logo
        $logo_path = __DIR__ . '/../assets/img/logo.jpg';
        $logo_converted = convertPNGtoJPG($logo_path);
        
        // Simpan posisi Y awal
        $start_y = 15;
        $pdf->SetY($start_y);
        
        // Gambar logo jika ada dan berhasil dikonversi
        $logo_width = 0;
        if ($logo_converted && file_exists($logo_converted)) {
            try {
                $pdf->Image($logo_converted, 20, $start_y, 20, 20, '', '', '', false, 300, '', false, false, 0);
                $logo_width = 25; // 20 (width) + 5 (spacing)
            } catch (Exception $e) {
                error_log("Error inserting logo: " . $e->getMessage());
            }
        }
        
        // Header text - disesuaikan posisi agar tidak tertutup logo
        $text_x = 20 + $logo_width;
        $text_width = 170 - $logo_width;
        
        $pdf->SetY($start_y);
        $pdf->SetX($text_x);
        $pdf->SetFont('times', 'B', 14);
        $pdf->Cell($text_width, 5, 'PEMERINTAH KOTA BATAM', 0, 1, 'C');
        
        $pdf->SetX($text_x);
        $pdf->SetFont('times', 'B', 12);
        $pdf->Cell($text_width, 5, 'KECAMATAN LUBUK BAJA', 0, 1, 'C');
        
        $pdf->SetX($text_x);
        $pdf->SetFont('times', 'B', 11);
        $pdf->Cell($text_width, 5, 'KELURAHAN/DESA BALOI INDAH', 0, 1, 'C');
        
        $pdf->SetX($text_x);
        $pdf->SetFont('times', '', 9);
        $pdf->Cell($text_width, 4, 'Jalan: Jl. Bunga Raya No.03, Baloi Indah, Lubuk Baja, Kota Batam, Kepulauan Riau 29444.', 0, 1, 'C');
        
        $pdf->SetX($text_x);
        $pdf->Cell($text_width, 4, 'Telepon: (0778) 458420 | Email: www.batam.linlk@gmail.com', 0, 1, 'C');
        
        // Garis bawah header (double line)
        $pdf->Ln(2);
        $y_line = $pdf->GetY();
        $pdf->SetLineWidth(0.8);
        $pdf->Line(20, $y_line, 190, $y_line);
        $pdf->SetLineWidth(0.3);
        $pdf->Line(20, $y_line + 1, 190, $y_line + 1);
        
        // Title
        $pdf->Ln(8);
        $pdf->SetFont('times', '', 11);
        $pdf->Cell(0, 5, 'Nomor: ' . ($data['nomor_surat'] ?? 'BELUM DITERBITKAN'), 0, 1, 'C');
        $pdf->SetFont('times', 'BU', 13);
        $pdf->Cell(0, 6, strtoupper($data['nama_dokumen']), 0, 1, 'C');
        
        // Content - PERBAIKAN UTAMA DI SINI
        $pdf->Ln(6);
        $pdf->SetFont('times', '', 12);
        
        // Paragraf pembuka dengan Write() untuk spacing yang lebih baik
        $pdf->Write(6, '       Yang bertanda tangan di bawah ini, Kepala Desa/Lurah Baloi Indah, Kecamatan Lubuk Baja, Kabupaten/Kota Batam, berdasarkan laporan dan keterangan yang diberikan oleh yang bersangkutan, dengan ini menerangkan bahwa:');
        $pdf->Ln(8);
        
        // Data table
        $pdf->SetFont('times', '', 12);
        
        $col1_width = 70;
        $col2_width = 5;
        
        $data_items = [
            ['label' => 'Nama Lengkap', 'value' => strtoupper($data['nama_lengkap'])],
            ['label' => 'Nomor Induk Kependudukan (NIK)', 'value' => $data['nik']],
            ['label' => 'Alamat', 'value' => $data['alamat']]
        ];
        
        foreach ($data_items as $item) {
            $x_start = $pdf->GetX();
            $y_start = $pdf->GetY();
            
            $pdf->Cell($col1_width, 6, $item['label'], 0, 0, 'L');
            $pdf->Cell($col2_width, 6, ':', 0, 0, 'C');
            
            // Gunakan MultiCell untuk value yang panjang
            $pdf->MultiCell(0, 6, $item['value'], 0, 'L', 0, 1);
        }
        
        // Field dinamis
        if (!empty($field_config) && !empty($keterangan)) {
            $pdf->Ln(4);
            $pdf->Write(6, '       Adalah benar merupakan penduduk/warga yang berdomisili di wilayah Kelurahan/Desa kami, dengan rincian keterangan sebagai berikut:');
            $pdf->Ln(8);
            
            foreach ($field_config as $field) {
                $val = get_field_val($keterangan, $field['label']);
                if ($val) {
                    $pdf->Cell($col1_width, 6, $field['label'], 0, 0, 'L');
                    $pdf->Cell($col2_width, 6, ':', 0, 0, 'C');
                    $pdf->MultiCell(0, 6, $val, 0, 'L', 0, 1);
                }
            }
        }
        
        // Paragraf penutup - Gunakan Write() untuk spacing normal
        $pdf->Ln(4);
        $pdf->Write(6, '       Surat keterangan ini diberikan kepada yang bersangkutan berdasarkan fakta dan data yang tertulis dalam administrasi Kelurahan/Desa kami, untuk keperluan ' . strtoupper($data['keperluan']) . '.');
        $pdf->Ln(8);
        
        $pdf->Write(6, '       Surat keterangan ini berlaku sejak tanggal diterbitkan dan dapat digunakan sebagaimana mestinya di hadapan instansi pemerintah maupun non-pemerintah yang memerlukan.');
        $pdf->Ln(8);
        
        $pdf->Write(6, '       Demikian surat keterangan ini kami buat dengan sebenarnya agar dapat dipergunakan sebagaimana mestinya.');
        
        // Tanda tangan - Cek ruang tersisa sebelum menambahkan
        $current_y = $pdf->GetY();
        $page_height = $pdf->getPageHeight();
        $bottom_margin = 25;
        $space_needed = 60; // Ruang yang dibutuhkan untuk TTD + NIP + Footer
        
        // Jika tidak cukup ruang, tambah halaman baru
        if (($current_y + $space_needed) > ($page_height - $bottom_margin)) {
            $pdf->AddPage();
            $current_y = $pdf->GetY();
        }
        
        $pdf->Ln(8);
        
        // Kolom kiri kosong, kolom kanan untuk TTD
        $pdf->Cell(100, 6, '', 0, 0, 'L');
        $pdf->SetFont('times', '', 12);
        $pdf->Cell(0, 6, 'Batam, ' . $tanggal_sekarang, 0, 1, 'C');
        
        $pdf->Ln(1);
        $pdf->Cell(100, 6, '', 0, 0, 'L');
        $pdf->SetFont('times', 'B', 12);
        $pdf->Cell(0, 6, 'KEPALA DESA/LURAH', 0, 1, 'C');
        
        // TTD Image
        $ttd_paths = [
            __DIR__ . '/../assets/img/ttd1.jpg'
        ];
        $ttd_found = false;
        
        foreach ($ttd_paths as $ttd_path) {
            if (file_exists($ttd_path)) {
                $ttd_converted = convertPNGtoJPG($ttd_path);
                if ($ttd_converted && file_exists($ttd_converted)) {
                    try {
                        $current_y = $pdf->GetY();
                        $pdf->Image($ttd_converted, 130, $current_y, 40, 20, '', '', '', false, 300, '', false, false, 0);
                        $ttd_found = true;
                        break;
                    } catch (Exception $e) {
                        error_log("Error inserting signature: " . $e->getMessage());
                    }
                }
            }
        }
        
        $pdf->Ln($ttd_found ? 22 : 15);
        $pdf->Cell(100, 6, '', 0, 0, 'L');
        $pdf->SetFont('times', 'BU', 12);
        $pdf->Cell(0, 6, 'Dimas Dwi Prasetiyo, S.KOM., M.KOM.', 0, 1, 'C');
        $pdf->SetFont('times', '', 11);
        $pdf->Cell(100, 6, '', 0, 0, 'L');
        $pdf->Cell(0, 6, 'NIP: -', 0, 1, 'C');
        
        // Footer - pastikan ada di halaman yang sama atau pindah ke halaman baru jika perlu
        $current_y = $pdf->GetY();
        if (($current_y + 20) > ($page_height - $bottom_margin)) {
            $pdf->AddPage();
        }
        
        $pdf->Ln(8);
        $pdf->SetFont('times', 'I', 9);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 4, 'Dokumen ini diterbitkan secara resmi oleh Pemerintah Desa/Kelurahan berdasarkan peraturan yang berlaku', 0, 1, 'C');
        $pdf->Cell(0, 4, 'Dicetak pada: ' . date('d/m/Y H:i:s') . ' WIB', 0, 1, 'C');
        
        // WATERMARK - Tambahkan ke setiap halaman
        $total_pages = $pdf->getNumPages();
        for ($page_num = 1; $page_num <= $total_pages; $page_num++) {
            $pdf->setPage($page_num);
            $pdf->SetTextColor(200, 200, 200);
            $pdf->SetAlpha(0.15);
            $pdf->SetFont('times', 'B', 60);
            
            // Posisi watermark di tengah halaman
            $page_width = $pdf->getPageWidth();
            $page_height = $pdf->getPageHeight();
            $x_center = $page_width / 2;
            $y_center = $page_height / 2;
            
            $pdf->StartTransform();
            $pdf->Rotate(45, $x_center, $y_center);
            $pdf->Text($x_center - 60, $y_center, 'DOKUMEN RESMI');
            $pdf->StopTransform();
        }
        
        $pdf->SetAlpha(1);
        $pdf->SetTextColor(0, 0, 0);
        
        // Output PDF
        $nama_dokumen_clean = preg_replace('/[^a-zA-Z0-9]/', '_', $data['nama_dokumen']);
        $nama_pemohon_clean = preg_replace('/[^a-zA-Z0-9]/', '_', $data['nama_lengkap']);
        $nomor_surat = $data['nomor_surat'] ? preg_replace('/[^a-zA-Z0-9]/', '_', $data['nomor_surat']) : date('YmdHis');
        
        $filename = $nama_dokumen_clean . '_' . $nama_pemohon_clean . '_' . $nomor_surat . '.pdf';
        $pdf->Output($filename, 'D');
        exit;
        
    } catch (Exception $e) {
        error_log("PDF Generation Error: " . $e->getMessage());
        echo "<script>alert('Gagal generate PDF: " . addslashes($e->getMessage()) . "'); window.location.href = 'lihat_status.php';</script>";
        exit;
    }
}

// Tampilan preview HTML
$ttd_paths = ['../assets/img/ttd1.jpg'];
$ttd_found = false;
$ttd_display = '';
foreach ($ttd_paths as $path) {
    if (file_exists($path)) {
        $ttd_display = $path;
        $ttd_found = true;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['nama_dokumen']) ?> - <?= htmlspecialchars($data['nomor_surat'] ?? 'PREVIEW') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .page {
            width: 210mm;
            min-height: 297mm;
            background: #fff;
            margin: 0 auto;
            padding: 20mm;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }
        /* WATERMARK */
        .page::before {
            content: 'DOKUMEN RESMI';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            font-weight: bold;
            color: rgba(200, 200, 200, 0.15);
            white-space: nowrap;
            z-index: 0;
            pointer-events: none;
        }
        .page > * {
            position: relative;
            z-index: 1;
        }
        .header {
            border-bottom: 3px double #000;
            padding-bottom: 10px;
            margin-bottom: 25px;
            display: flex;
            align-items: flex-start;
            gap: 15px;
        }
        .logo {
            width: 70px;
            height: 70px;
            flex-shrink: 0;
        }
        .logo img {
            width: 150%;
            height: 100%;
            object-fit: contain;
        }
        .header-text {
            flex: 1;
            text-align: center;
            padding-top: 5px;
        }
        .header h1 {
            font-size: 15px;
            margin-bottom: 3px;
            font-weight: bold;
        }
        .header h2 {
            font-size: 13px;
            margin-bottom: 2px;
            font-weight: bold;
        }
        .header h3 {
            font-size: 12px;
            margin-bottom: 8px;
            font-weight: bold;
        }
        .header p {
            font-size: 10px;
            line-height: 1.5;
        }
        .title {
            text-align: center;
            margin: 25px 0 20px;
        }
        .title p {
            font-size: 12px;
            margin-bottom: 8px;
        }
        .title h1 {
            font-size: 14px;
            font-weight: bold;
            text-decoration: underline;
        }
        .content {
            font-size: 12px;
            line-height: 1.8;
            text-align: justify;
            margin: 20px 0;
        }
        .content p {
            margin-bottom: 12px;
            text-indent: 40px;
        }
        .data-table {
            margin: 15px 0 15px 40px;
            width: calc(100% - 40px);
            border-collapse: collapse;
        }
        .data-table tr td {
            padding: 6px 0;
            vertical-align: top;
        }
        .data-table tr td:first-child {
            width: 230px;
        }
        .data-table tr td:nth-child(2) {
            width: 15px;
            text-align: center;
            padding-right: 10px;
        }
        .signature {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .sig-left {
            flex: 0 0 48%;
        }
        .sig-right {
            flex: 0 0 48%;
            text-align: center;
        }
        .sig-right .date {
            font-size: 12px;
            margin-bottom: 6px;
        }
        .sig-right .position {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 8px;
        }
        .ttd-img {
            width: 140px;
            height: 70px;
            margin: 5px auto 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .ttd-img img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .sig-right .name {
            font-weight: bold;
            text-decoration: underline;
            font-size: 12px;
            margin-top: 5px;
        }
        .sig-right .nip {
            font-size: 11px;
            margin-top: 2px;
        }
        .footer {
            margin-top: 25px;
            padding-top: 12px;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 9px;
            font-style: italic;
            color: #888;
        }
        .footer p {
            margin: 2px 0;
        }
        .actions {
            text-align: center;
            margin: 20px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            margin: 0 10px;
            border-radius: 5px;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #007bff;
            color: #fff;
        }
        .btn-secondary {
            background: #6c757d;
            color: #fff;
        }
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        @media print {
            .actions {
                display: none;
            }
            body {
                background: #fff;
                padding: 0;
            }
            .page {
                box-shadow: none;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="actions">
        <a href="lihat_status.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
        <a href="unduh_surat.php?id=<?= $id_pengajuan ?>&download=pdf" class="btn btn-primary"><i class="fa-solid fa-file-pdf"></i> Download PDF</a>
    </div>

    <div class="page">
        <div class="header">
            <div class="logo">
                <?php if (file_exists('../assets/img/logo.jpg')): ?>
                    <img src="../assets/img/logo.jpg" alt="Logo">
                <?php endif; ?>
            </div>
            <div class="header-text">
                <h1>PEMERINTAH KOTA BATAM</h1>
                <h2>KECAMATAN LUBUK BAJA</h2>
                <h3>KELURAHAN BALOI INDAH</h3>
                <p>Jalan: Jl. Bunga Raya No.03, Baloi Indah, Lubuk Baja, Kota Batam, Kepulauan Riau 29444.<br>Telepon: (0778) 458420 | Email: www.batam.linlk@gmail.com</p>
            </div>
        </div>

        <div class="title">
            <p>Nomor: <?= htmlspecialchars($data['nomor_surat'] ?? 'BELUM DITERBITKAN') ?></p>
            <h1><?= strtoupper(htmlspecialchars($data['nama_dokumen'])) ?></h1>
        </div>

        <div class="content">
            <p>Yang bertanda tangan di bawah ini, Kepala Desa/Lurah <strong></strong>, Kecamatan <strong>Lubuk Baja</strong>, Kabupaten/Kota <strong>Batam</strong>, berdasarkan laporan dan keterangan yang diberikan oleh yang bersangkutan, dengan ini menerangkan bahwa:</p>

            <table class="data-table">
                <tr>
                    <td>Nama Lengkap</td>
                    <td>:</td>
                    <td><?= strtoupper(htmlspecialchars($data['nama_lengkap'])) ?></td>
                </tr>
                <tr>
                    <td>Nomor Induk Kependudukan (NIK)</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($data['nik']) ?></td>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($data['alamat']) ?></td>
                </tr>
            </table>

            <?php if (!empty($field_config) && !empty($keterangan)): ?>
                <p>Adalah benar merupakan penduduk/warga yang berdomisili di wilayah Kelurahan/Desa kami, dengan rincian keterangan sebagai berikut:</p>
                <table class="data-table">
                    <?php foreach ($field_config as $field):
                        $val = get_field_val($keterangan, $field['label']);
                        if ($val): ?>
                            <tr>
                                <td><?= htmlspecialchars($field['label']) ?></td>
                                <td>:</td>
                                <td><?= htmlspecialchars($val) ?></td>
                            </tr>
                    <?php endif;
                    endforeach; ?>
                </table>
            <?php endif; ?>

            <p>Surat keterangan ini diberikan kepada yang bersangkutan berdasarkan fakta dan data yang tertulis dalam administrasi Kelurahan/Desa kami, untuk keperluan <strong><?= strtoupper(htmlspecialchars($data['keperluan'])) ?></strong>.</p>
            <p>Surat keterangan ini berlaku sejak tanggal diterbitkan dan dapat digunakan sebagaimana mestinya di hadapan instansi pemerintah maupun non-pemerintah yang memerlukan.</p>
            <p>Demikian surat keterangan ini kami buat dengan sebenarnya agar dapat dipergunakan sebagaimana mestinya.</p>
        </div>

        <div class="signature">
            <div class="sig-left"></div>
            <div class="sig-right">
                <div class="date">Batam, <?= $tanggal_sekarang ?></div>
                <div class="position">KEPALA LURAH BALOI INDAH</div>
                <div class="ttd-img">
                    <?php if ($ttd_found): ?>
                        <img src="<?= $ttd_display ?>" alt="TTD">
                    <?php else: ?>
                        <div style="border:1px dashed #ccc; width:100%; height:100%; display:flex; align-items:center; justify-content:center;">
                            <span style="color:#999">TTD</span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="name">Dimas Dwi Prasetiyo, S.KOM., M.KOM.</div>
                <div class="nip">NIP: -</div>
            </div>
        </div>

        <div class="footer">
            <p>Dokumen ini diterbitkan secara resmi oleh Pemerintah Desa/Kelurahan berdasarkan peraturan yang berlaku</p>
            <p>Dicetak pada: <?= date('d/m/Y H:i:s') ?> WIB</p>
        </div>
    </div>
</body>
</html>