<?php
/* ======================================= 
   TARUH DI ROOT PROJECT (sejajar dengan index.php)
   
   CARA PAKAI:
   1. Buka di browser: http://localhost/project_pengajuan_dokumen/generate_hash.php
   2. Copy hash yang dihasilkan
   3. Update manual di database atau gunakan query yang disediakan
========================================= */

// Password yang akan di-hash
$password = "password123";

// Generate hash
$hash = password_hash($password, PASSWORD_DEFAULT);

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Password Hash Generator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Password Hash Generator</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <strong>PENTING:</strong> Tool ini untuk generate password hash yang benar untuk login.
                        </div>

                        <h5>Password Hash untuk "<?= $password ?>":</h5>
                        <div class="mb-3">
                            <textarea class="form-control" rows="3" readonly><?= $hash ?></textarea>
                        </div>

                        <hr>

                        <h5>Query SQL untuk Update Database:</h5>
                        <div class="alert alert-warning">
                            Copy query di bawah ini dan jalankan di phpMyAdmin:
                        </div>

                        <pre class="bg-dark text-white p-3 rounded"><code>-- Update semua user dengan password baru
UPDATE t_pengguna SET password = '<?= $hash ?>' WHERE username = 'admin';
UPDATE t_pengguna SET password = '<?= $hash ?>' WHERE username = 'petugas1';
UPDATE t_pengguna SET password = '<?= $hash ?>' WHERE username = 'petugas2';
UPDATE t_pengguna SET password = '<?= $hash ?>' WHERE username = 'warga1';

-- Atau update semua sekaligus:
UPDATE t_pengguna SET password = '<?= $hash ?>';</code></pre>

                        <hr>

                        <h5>Generate Hash Kustom:</h5>
                        <form method="POST" class="mb-3">
                            <div class="input-group">
                                <input type="text" name="custom_password" class="form-control" placeholder="Masukkan password..." required>
                                <button type="submit" name="generate" class="btn btn-primary">Generate</button>
                            </div>
                        </form>

                        <?php if (isset($_POST['generate'])): ?>
                            <?php $custom_hash = password_hash($_POST['custom_password'], PASSWORD_DEFAULT); ?>
                            <div class="alert alert-success">
                                <strong>Hash untuk "<?= htmlspecialchars($_POST['custom_password']) ?>":</strong>
                                <textarea class="form-control mt-2" rows="2" readonly><?= $custom_hash ?></textarea>
                            </div>
                        <?php endif; ?>

                        <hr>

                        <h5>Cara Membuat User Baru Manual di Database:</h5>
                        <ol>
                            <li>Generate hash password menggunakan form di atas</li>
                            <li>Buka phpMyAdmin → tabel <code>t_pengguna</code></li>
                            <li>Klik Insert → Isi data</li>
                            <li>Untuk kolom password, paste hash yang sudah di-generate</li>
                        </ol>

                        <div class="alert alert-danger">
                            <strong>HAPUS FILE INI</strong> setelah selesai setup untuk keamanan!
                        </div>
                    </div>
                </div>

                <div class="card shadow mt-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Test Login Credentials</h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Role</th>
                                    <th>Username</th>
                                    <th>Password</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Cek koneksi database
                                $conn = @mysqli_connect('localhost', 'root', '', 'db_pengajuan_dokumen');

                                if ($conn) {
                                    $query = "SELECT username, role, status FROM t_pengguna WHERE role IN ('admin', 'petugas') ORDER BY role, username";
                                    $result = mysqli_query($conn, $query);

                                    while ($user = mysqli_fetch_assoc($result)) {
                                        echo "<tr>";
                                        echo "<td><span class='badge bg-info'>" . ucfirst($user['role']) . "</span></td>";
                                        echo "<td>" . $user['username'] . "</td>";
                                        echo "<td>password123</td>";
                                        echo "<td><span class='badge bg-" . ($user['status'] == 'aktif' ? 'success' : 'secondary') . "'>" . ucfirst($user['status']) . "</span></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center text-danger'>Database belum terkoneksi</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>