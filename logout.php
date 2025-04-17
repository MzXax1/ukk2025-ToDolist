<?php

/**
 * Fungsi: Logout User dan Hapus Cookie
 * 
 * Deskripsi:
 * Fungsi ini digunakan untuk mengakhiri sesi pengguna dan menghapus cookie yang terkait dengan auto-login.
 * Ketika pengguna melakukan logout, sesi akan dihapus, dan cookie yang menyimpan ID pengguna untuk auto-login 
 * akan dihapus jika ada.
 * 
 * Proses:
 * 1. Mencatat aktivitas logout pengguna ke dalam file log dengan menggunakan fungsi `writeLog()`.
 * 2. Menghancurkan sesi pengguna dengan `session_unset()` dan `session_destroy()` untuk mengakhiri sesi yang sedang berjalan.
 * 3. Menghapus cookie `remember_user` yang digunakan untuk auto-login sebelumnya.
 * 4. Setelah proses logout selesai, pengguna akan diarahkan kembali ke halaman login (`login.php`).
 * 
 * Fungsi yang Digunakan:
 *   - `writeLog()`: Mencatat aktivitas logout pengguna dalam file log untuk keperluan audit.
 *   - `clearAppCookie()`: Menghapus cookie yang digunakan untuk auto-login.
 * 
 * Langkah-Langkah:
 *   - **writeLog** pertama akan mencatat bahwa pengguna dengan ID dan nama tertentu telah melakukan logout.
 *   - **session_unset()** digunakan untuk menghapus semua variabel sesi.
 *   - **session_destroy()** digunakan untuk menghancurkan sesi yang sedang aktif.
 *   - **clearAppCookie('remember_user')** digunakan untuk menghapus cookie yang menyimpan ID pengguna untuk auto-login.
 *   - Setelah itu, **writeLog** kedua akan mencatat bahwa cookie telah dihapus.
 *   - Akhirnya, pengguna akan diarahkan kembali ke halaman login dengan `header("Location: login.php")`.
 * 
 * Keuntungan dari log ini:
 *   - Menghindari potensi penyalahgunaan sesi atau data pengguna yang tertinggal setelah logout.
 *   - Memastikan setiap aktivitas logout dicatat untuk tujuan audit.
 */

session_start();
require_once 'cookie_helper.php';
define('SECURE_ACCESS', true);
require_once 'private/db.php'; // sesuaikan pathnya
require_once 'log_helper.php';

// Menulis log sebelum session dihancurkan
writeLog("User {$_SESSION['user_name']} (ID: {$_SESSION['user_id']}) melakukan logout.");



// Menghapus cookie jika ada
clearAppCookie('remember_user'); 

// Menulis log setelah cookie dihapus
writeLog("Cookie 'remember_user' dihapus setelah logout.");

// Menghapus semua sesi
session_unset();
session_destroy();

// Mengarahkan pengguna ke halaman login
header("Location: login.php");
exit();
?>

