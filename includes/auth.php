<?php

require_once 'config.php';

// Fungsi Login
function login($username, $password) {
    global $conn;
    
    $username = clean_input($username);
    
    $query = "SELECT * FROM t_pengguna WHERE username = '$username'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // CEK STATUS AKTIF/NONAKTIF
        if ($user['status'] == 'nonaktif') {
            return array(
                'status' => false, 
                'message' => 'Akun Tidak Aktif.'
            );
        }
        
        if (password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id_pengguna'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['status'] = $user['status']; // Simpan status di session
            $_SESSION['login_time'] = time();
            
            return array('status' => true, 'role' => $user['role']);
        } else {
            return array('status' => false, 'message' => 'Username/ Password salah');
        }
    } else {
        return array('status' => false, 'message' => 'Username tidak ditemukan');
    }
}

// Fungsi Register (hanya untuk warga)
function register($data) {
    global $conn;
    
    $username = clean_input($data['username']);
    $password = password_hash($data['password'], PASSWORD_DEFAULT);
    $nama_lengkap = clean_input($data['nama_lengkap']);
    $nik = clean_input($data['nik']);
    $email = clean_input($data['email']);
    $no_hp = clean_input($data['no_hp']);
    $alamat = clean_input($data['alamat']);
    
    // Cek username sudah ada atau belum
    $check_query = "SELECT * FROM t_pengguna WHERE username = '$username' OR nik = '$nik'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        return array('status' => false, 'message' => 'Username atau NIK sudah terdaftar');
    }
    
    // Insert ke database
    $query = "INSERT INTO t_pengguna (username, password, nama_lengkap, nik, email, no_hp, alamat, role, status) 
              VALUES ('$username', '$password', '$nama_lengkap', '$nik', '$email', '$no_hp', '$alamat', 'warga', 'aktif')";
    
    if (mysqli_query($conn, $query)) {
        return array('status' => true, 'message' => 'Registrasi berhasil');
    } else {
        return array('status' => false, 'message' => 'Gagal melakukan registrasi: ' . mysqli_error($conn));
    }
}

// Fungsi Logout
function logout() {
    session_unset();
    session_destroy();
    redirect('login.php');
}

// Cek apakah user sudah login
function require_login() {
    if (!is_logged_in()) {
        redirect('login.php');
    }
    
    // CEK STATUS USER - AUTO LOGOUT JIKA DINONAKTIFKAN
    check_user_status();
}

// Cek role user
function require_role($allowed_roles) {
    require_login();
    
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        redirect('index.php');
    }
}

// Cek status user apakah masih aktif
function check_user_status() {
    global $conn;
    
    if (!isset($_SESSION['user_id'])) {
        return;
    }
    
    $user_id = $_SESSION['user_id'];
    $query = "SELECT status FROM t_pengguna WHERE id_pengguna = '$user_id'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Jika user dinonaktifkan, langsung logout
        if ($user['status'] == 'nonaktif') {
            session_unset();
            session_destroy();
            header("Location: ../auth/login.php?error=akun_dinonaktifkan");
            exit();
        }
        
        // Update status di session
        $_SESSION['status'] = $user['status'];
    }
}
?>