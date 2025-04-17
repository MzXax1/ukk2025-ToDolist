<?php
session_start();
define('SECURE_ACCESS', true);
require_once 'private/db.php'; // sesuaikan pathnya
require_once 'log_helper.php';

// Proses ketika form registrasi dikirimkan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim(htmlspecialchars($_POST['name'])); // Menghapus spasi dan mengamankan input
    $email = trim(htmlspecialchars($_POST['email']));
    $password = htmlspecialchars($_POST['password']); // Mengamankan input password

    // Validasi input
    if (empty($name) || empty($email) || empty($password)) {
        $error = "Semua field harus diisi."; // Pesan error jika ada input kosong
    } else {
        // Mengecek apakah email sudah digunakan
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $checkResult = $check->get_result();

        if ($checkResult->num_rows > 0) {
            $error = "Email sudah digunakan. Silakan gunakan email lain.";
            writeLog("Registrasi gagal. Email $email sudah digunakan."); // Log kegagalan
        } else {
            $hashed_password = md5($password); // Enkripsi password menggunakan md5 (disarankan menggunakan password_hash() di masa depan)
            $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashed_password);

            if ($stmt->execute()) {
                $_SESSION['user_id'] = $conn->insert_id; // Menyimpan ID pengguna ke sesi
                $_SESSION['user_name'] = $name; // Menyimpan nama pengguna ke sesi
                $_SESSION['user_email'] = $email; // Menyimpan email pengguna ke sesi

                writeLog("User baru berhasil register: $name ($email)"); // Log keberhasilan registrasi
                header('Location: index.php'); // Mengarahkan ke halaman utama setelah registrasi sukses
                exit();
            } else {
                $error = "Gagal mendaftar. Silakan coba lagi.";
                writeLog("Registrasi error untuk email $email - " . $stmt->error); // Log kesalahan saat insert ke database
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun</title>
    <link rel="icon" href="vaficon.png" type="image/x-icon">
    <style>
        body {
            background: linear-gradient(to right, #d3cce3, #e9e4f0);
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .register-container {
            background: #fff;
            padding: 35px 40px;
            border-radius: 12px;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 420px;
            text-align: center;
        }

        .register-container h2 {
            font-size: 26px;
            margin-bottom: 20px;
            color: #333;
        }

        .register-container form input {
            width: 92%;
            padding: 12px 14px;
            margin-bottom: 16px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
        }

        .register-container button {
            width: 100%;
            padding: 12px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }

        .register-container button:hover {
            background-color: #218838;
        }

        .register-container .error-message {
            color: #e74c3c;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .register-container a {
            display: inline-block;
            margin-top: 18px;
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
        }

        .register-container a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Buat Akun Baru</h2>
        <form method="POST" action="register.php">
            <input type="text" name="name" placeholder="Nama lengkap" required>
            <input type="email" name="email" placeholder="Email aktif" required>
            <input type="password" name="password" placeholder="Password" required>

            <?php if (isset($error)): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <button type="submit">Daftar</button>
        </form>

        Sudah punya akun?<a href="login.php"> Masuk di sini</a>
    </div>
</body>
</html>

