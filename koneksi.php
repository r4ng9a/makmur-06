<?php
// Railway otomatis menyediakan env variables ini saat database MySQL ditambahkan
$host = getenv('MYSQLHOST') ?: 'localhost';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';
$db = getenv('MYSQLDATABASE') ?: 'railway';
$port = (int)(getenv('MYSQLPORT') ?: 3306);

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die(json_encode([
        "error" => "Koneksi gagal: " . $conn->connect_error
    ]));
}
?>