<?php
if (!defined('SECURE_ACCESS')) {
    die('Akses langsung dilarang!');
}

$host = "localhost";
$username = "root";
$password = "";
$database = "To_do_list";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
