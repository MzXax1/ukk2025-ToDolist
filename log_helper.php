<?php
/**
 * Fungsi: writeLog
 * Menulis aktivitas user ke dalam file log dengan informasi nama user dan tindakan.
 * 
 * Deskripsi:
 * Fungsi ini digunakan untuk mencatat aktivitas yang dilakukan oleh pengguna dalam sistem,
 * seperti menambahkan, mengedit, atau menghapus proyek atau tugas. Semua aktivitas dicatat 
 * dalam file log untuk keperluan audit dan pemantauan.
 * 
 * Parameter:
 *   - $message (string) -> Isi log yang berisi pesan terkait aktivitas yang dilakukan.
 *   - $user (array) -> Informasi pengguna yang melakukan tindakan, berupa array dengan key 'id' dan 'name'.
 *     - 'id' (int) -> ID pengguna.
 *     - 'name' (string) -> Nama pengguna.
 * 
 * Contoh:
 *   writeLog("Proyek baru ditambahkan", ['id' => 1, 'name' => 'John Doe']);
 * 
 * Implementasi:
 * Fungsi ini akan menulis pesan log yang diterima ke dalam file log (misalnya: "activity.log").
 * Setiap entri log akan mencantumkan ID dan nama pengguna serta pesan terkait aktivitas.
 */
function writeLog($message, $user = null, $file = 'logs/app.log') {
    date_default_timezone_set('Asia/Jakarta');

    $logDir = dirname($file);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }

    $timestamp = date('Y-m-d H:i:s'); // Timestamp saat log ditulis

    // Coba ambil dari session jika user tidak diset
    if (!$user && isset($_SESSION['user_id']) && isset($_SESSION['user_name'])) {
        $user = [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name']
        ];
    }

    if ($user && isset($user['id']) && isset($user['name'])) {
        $userInfo = "User ID: {$user['id']}, Name: {$user['name']}";
    } else {
        $userInfo = 'User ID: Unknown, Name: Unknown';
    }
     // Menulis log ke file
    $logEntry = "[$timestamp] [$userInfo] $message" . PHP_EOL;
    file_put_contents($file, $logEntry, FILE_APPEND);
}

