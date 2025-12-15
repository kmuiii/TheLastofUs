<?php
include 'koneksi.php';

# Kalo admin ke admin-dashboard, kalo user ke index
function redirectByRole() {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin-dashboard.php');
    } else {
        header('Location: index.php');
    }
    exit;
}

# Memastikan user adalah admin
function requireAdmin() {
    requireLogin();

    if ($_SESSION['role'] !== 'admin') {
        header('Location: index.php');
        exit;
    }
}

# Pastiin user udah login
function requireLogin() {
    if (!isset($_SESSION['login_user'])) {
        header('Location: login.php');
        exit;
    }
}

# Mencegah user yang sudah login mengakses halaman auth
function preventAuthPage() {
    if (isset($_SESSION['login_user'])) {
        header('Location: index.php');
        exit;
    }
}

# Login user dan set session
function loginUser($conn, $input, $password) {
    $input = mysqli_real_escape_string($conn, $input);

    $query = "SELECT * FROM users 
              WHERE email = '$input' OR username = '$input'
              LIMIT 1";

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        // VERIFIKASI PASSWORD (HASH)
        if (password_verify($password, $user['password'])) {
            $_SESSION['login_user'] = $user['user_id'];
            $_SESSION['username']   = $user['username'];
            $_SESSION['email']      = $user['email'];
            $_SESSION['role']       = $user['role'];

            return true;
        }
    }

    return false;
}

# logout user
function logoutUser() {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

# Menampilkan user yang login
function getLoggedInUser($conn) {
    if (!isset($_SESSION['login_user'])) {
        return null;
    }

    $userId = $_SESSION['login_user'];
    $query = "SELECT username FROM users WHERE user_id = $userId";
    $result = mysqli_query($conn, $query);

    return mysqli_fetch_assoc($result);
}

# Function register user
function registerUser($conn, $email, $username, $password) {
    $email    = trim($email);
    $username = trim($username);

    // Validasi
    if (empty($email) || empty($username) || empty($password)) {
        return ['status' => false, 'message' => 'Semua field wajib diisi'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['status' => false, 'message' => 'Format email tidak valid'];
    }

    if (strlen($password) < 6) {
        return ['status' => false, 'message' => 'Password minimal 6 karakter'];
    }

    // Cek email sudah ada
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        return ['status' => false, 'message' => 'Email sudah terdaftar'];
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        INSERT INTO users (email, username, password, role)
        VALUES (?, ?, ?, 'user')
    ");
    $stmt->bind_param("sss", $email, $username, $hashedPassword);

    if ($stmt->execute()) {
        return ['status' => true, 'message' => 'Registrasi berhasil'];
    }

    return ['status' => false, 'message' => 'Registrasi gagal'];
}

# Kirim kode reset password
function sendResetCode($conn, $email) {
    // cek email terdaftar
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        return ['status' => false, 'message' => 'Email tidak terdaftar'];
    }

    $code = random_int(100000, 999999);

    $_SESSION['reset_code'] = $code;
    $_SESSION['reset_code_expires'] = time() + (5 * 60); // 5 menit
    $_SESSION['reset_email'] = $email;

    return [
        'status' => true,
        'code'   => $code
    ];
}

# Verifikasi kode reset password
function verifyResetCode($conn, $inputCode) {

    if (!isset($_SESSION['reset_code'], $_SESSION['reset_code_expires'])) {
        return ['status' => false, 'message' => 'Kode tidak ditemukan'];
    }

    if (time() > $_SESSION['reset_code_expires']) {
        return ['status' => false, 'message' => 'Kode sudah kadaluarsa'];
    }

    if ($inputCode != $_SESSION['reset_code']) {
        return ['status' => false, 'message' => 'Kode verifikasi salah'];
    }

    return ['status' => true];
}

# Function reset password baru
function resetPassword($conn, $password, $confirm) {
    if (!isset($_SESSION['reset_verified'], $_SESSION['reset_email'])) {
        return ['status' => false, 'message' => 'Akses tidak sah'];
    }

    if ($password !== $confirm) {
        return ['status' => false, 'message' => 'Password tidak sama'];
    }

    if (strlen($password) < 8) {
        return ['status' => false, 'message' => 'Password minimal 8 karakter'];
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $email = mysqli_real_escape_string($conn, $_SESSION['reset_email']);

    mysqli_query($conn, "
        UPDATE users
        SET password='$hash',
            reset_code=NULL,
            reset_expired=NULL
        WHERE email='$email'
    ");

    session_unset();
    session_destroy();

    return ['status' => true];
}

?>