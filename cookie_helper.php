<?php
// Kunci rahasia - GANTI dengan nilai unik dan rahasia
define('SECRET_KEY', 'ganti_dengan_kunci_rahasia_anda');

// Enkripsi
function encryptValue($value) {
    return base64_encode(openssl_encrypt($value, 'aes-256-cbc', SECRET_KEY, 0, substr(SECRET_KEY, 0, 16)));
}

// Dekripsi
function decryptValue($encrypted) {
    return openssl_decrypt(base64_decode($encrypted), 'aes-256-cbc', SECRET_KEY, 0, substr(SECRET_KEY, 0, 16));
}

// Set cookie dengan enkripsi
function setAppCookie($name, $value, $duration = 2592000) {
    $encrypted = encryptValue($value);
    setcookie($name, $encrypted, time() + $duration, "/", "", false, true);
}

// Ambil dan dekripsi cookie
function getAppCookie($name) {
    return isset($_COOKIE[$name]) ? decryptValue($_COOKIE[$name]) : null;
}

// Hapus cookie
function clearAppCookie($name) {
    setcookie($name, '', time() - 3600, "/", "", false, true);
}
