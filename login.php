<?php

/**
 * Fungsi: Auto-login dan Login User
 * 
 * Deskripsi:
 * Modul ini menangani proses login untuk pengguna, termasuk fitur auto-login menggunakan cookie, 
 * serta proses login manual dengan verifikasi email dan password.
 * 
 * 1. **Auto-login dengan Cookie**:
 *    Jika pengguna sudah login sebelumnya dan cookie `remember_user` ada, sistem akan mencoba 
 *    untuk melakukan login otomatis menggunakan ID pengguna yang disimpan di cookie.
 * 
 * 2. **Login Manual**:
 *    Pengguna dapat melakukan login dengan memasukkan email dan password. Jika data cocok, 
 *    sesi akan dimulai, dan pengguna akan diarahkan ke halaman utama.
 * 
 * Parameter:
 *   Tidak ada parameter khusus yang diperlukan. Fungsi ini bekerja berdasarkan data yang 
 *   ada di session dan cookie.
 * 
 * Proses:
 *   - Mengecek apakah pengguna sudah login, jika sudah maka diarahkan ke halaman utama.
 *   - Jika tidak login, sistem mencoba melakukan auto-login dengan cookie `remember_user`.
 *   - Jika auto-login gagal atau tidak ada cookie, sistem akan menunggu input dari form login.
 *   - Setelah form login di-submit, sistem memeriksa email dan password yang dimasukkan dan 
 *     mengverifikasi apakah keduanya sesuai dengan data yang ada di database.
 *   - Jika login berhasil, sesi pengguna dibuat dan (jika dicentang) cookie `remember_user` disimpan 
 *     untuk auto-login di lain waktu.
 * 
 * Fungsi yang Digunakan:
 *   - `writeLog()`: Mencatat aktivitas login dalam log untuk keperluan audit.
 *   - `getAppCookie()`: Mengambil nilai cookie yang diset sebelumnya.
 *   - `setAppCookie()`: Menyimpan cookie di browser untuk auto-login.
 *   - `clearAppCookie()`: Menghapus cookie untuk memastikan tidak ada auto-login yang terjadi.
 */ 

session_start();
define('SECURE_ACCESS', true);
require_once 'private/db.php'; // sesuaikan pathnya
require_once 'log_helper.php';
require_once 'cookie_helper.php';

// Mengecek jika sudah login, maka redirect ke halaman utama
if (isset($_SESSION['user_id'])) {
    writeLog("User {$_SESSION['user_name']} (ID: {$_SESSION['user_id']}) mencoba mengakses login, tetapi sudah login.");
    header('Location: index.php');
    exit();
}

// Auto login dengan cookie
if (isset($_COOKIE['remember_user'])) {
    $user_id = getAppCookie('remember_user');

    // Verifikasi user_id dari database
    $stmt = $conn->prepare("SELECT id, name, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];

        writeLog("Auto-login berhasil dari cookie untuk user: {$user['name']}");
        header("Location: index.php");
        exit();
    } else {
        clearAppCookie('remember_user');
        writeLog("Auto-login gagal: user_id dari cookie tidak valid");
    }
}


// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $remember = isset($_POST['remember']); // ← ⬅ cek checkbox

    $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Verifikasi email dan password
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

         // Membandingkan password yang dimasukkan dengan yang ada di database
        if (md5($password) === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];

            
            // Jika "Ingat saya" dicentang, simpan cookie untuk auto-login
            if ($remember) {
                setAppCookie('remember_user', $user['id'], 2592000); // Simpan cookie
            } else {
                clearAppCookie('remember_user'); // Hapus cookie kalau tidak dicentang
            }


            writeLog("User {$user['name']} login berhasil.");
            header('Location: index.php');
            exit();
        } else {
            $error = "Password salah.";
            writeLog("Login gagal untuk email $email (password salah).");
        }
    } else {
        $error = "Email tidak ditemukan.";
        writeLog("Login gagal untuk email $email (tidak terdaftar).");
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ToDo App</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="vaficon.png" type="image/x-icon">
    <style>
        body {
            background: linear-gradient(135deg, #e0eafc, #cfdef3);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
        }

        .login-container {
            background: #ffffff;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 420px;
        }

        .login-container h2 {
            margin-bottom: 25px;
            color: #333;
            text-align: center;
        }

        .login-container input[type="email"],
        .login-container input[type="password"] {
            width: 92%;
            padding: 12px 15px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
        }

        .login-container .remember {
            display: flex;
            align-items: center;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .login-container .remember input {
            margin-right: 8px;
        }

        .login-container button {
            width: 100%;
            background-color: #007bff;
            color: #fff;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .login-container button:hover {
            background-color: #0056b3;
        }

        .login-container .error-message {
            color: #e74c3c;
            font-size: 14px;
            margin-bottom: 10px;
            text-align: center;
        }

        .login-container .register {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
        }

        .login-container .register a {
            color: #007bff;
            text-decoration: none;
        }

        .login-container .register a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h2>Masuk ke Akun</h2>
        <form action="login.php" method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>

            <div class="remember">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember">Ingat saya</label>
            </div>

            <?php if (isset($error)): ?>
                <div class="error-message">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <button type="submit">Login</button>
        </form>

        <div class="register">
            Belum punya akun? <a href="register.php">Daftar di sini</a>
        </div>
    </div>
</body>

</html>