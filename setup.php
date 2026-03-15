<?php
// Setup otomatis membaca env Railway
$host = getenv('MYSQLHOST') ?: 'localhost';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';
$db = getenv('MYSQLDATABASE') ?: 'railway';
$port = (int)(getenv('MYSQLPORT') ?: 3306);

// Koneksi ke database (sudah ada, Railway buat otomatis)
$conn = new mysqli($host, $user, $pass, $db, $port);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

echo "Koneksi ke database berhasil.<br>";

// Drop tabel lama (urutan penting: child dulu)
$conn->query("DROP TABLE IF EXISTS shinobi");
$conn->query("DROP TABLE IF EXISTS clan");
$conn->query("DROP TABLE IF EXISTS desa");

// Buat Tabel Desa
$conn->query("CREATE TABLE desa (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_desa VARCHAR(100) UNIQUE,
    kepala_desa VARCHAR(100),
    lokasi_negara VARCHAR(100),
    simbol VARCHAR(50)
)");
echo "Tabel 'desa' berhasil dibuat.<br>";

// Buat Tabel Clan
$conn->query("CREATE TABLE clan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_clan VARCHAR(100) UNIQUE,
    desa_id INT,
    ketua_clan VARCHAR(100),
    jutsu_khas VARCHAR(150),
    FOREIGN KEY (desa_id) REFERENCES desa(id) ON DELETE CASCADE
)");
echo "Tabel 'clan' berhasil dibuat.<br>";

// Buat Tabel Shinobi
$conn->query("CREATE TABLE shinobi (
    id VARCHAR(50) PRIMARY KEY,
    nama_shinobi VARCHAR(150),
    clan_id INT,
    desa_id INT,
    `rank` VARCHAR(50),
    elemen_chakra VARCHAR(100),
    jumlah_misi_selesai INT DEFAULT 0,
    FOREIGN KEY (clan_id) REFERENCES clan(id) ON DELETE CASCADE,
    FOREIGN KEY (desa_id) REFERENCES desa(id) ON DELETE CASCADE
)");
echo "Tabel 'shinobi' berhasil dibuat.<br><br>";

// Insert Desa
$data_desa = [
    "('Konohagakure', 'Hokage', 'Negara Api', 'Daun')",
    "('Sunagakure', 'Kazekage', 'Negara Angin', 'Pasir')",
    "('Kirigakure', 'Mizukage', 'Negara Air', 'Kabut')",
    "('Kumogakure', 'Raikage', 'Negara Petir', 'Awan')",
    "('Iwagakure', 'Tsuchikage', 'Negara Tanah', 'Batu')",
    "('Amegakure', 'Pain', 'Negara Hujan', 'Hujan')",
    "('Otogakure', 'Orochimaru', 'Negara Bunyi', 'Nada')",
    "('Takigakure', 'Shibuki', 'Negara Air Terjun', 'Air Terjun')",
    "('Kusagakure', 'Muku', 'Negara Rumput', 'Rumput')",
    "('Uzushiogakure', 'Ashina Uzumaki', 'Negara Pusaran Air', 'Pusaran')"
];
foreach ($data_desa as $v)
    $conn->query("INSERT INTO desa (nama_desa, kepala_desa, lokasi_negara, simbol) VALUES $v");
echo "10 data Desa berhasil diinsert.<br>";

// Insert Clan
$data_clan = [
    "('Uchiha', 1, 'Fugaku Uchiha', 'Sharingan, Katon')",
    "('Uzumaki', 1, 'Kushina Uzumaki', 'Fuinjutsu, Cakra Besar')",
    "('Hyuga', 1, 'Hiashi Hyuga', 'Byakugan, Juken')",
    "('Nara', 1, 'Shikaku Nara', 'Kagemane no Jutsu')",
    "('Akimichi', 1, 'Choza Akimichi', 'Baika no Jutsu')",
    "('Yamanaka', 1, 'Inoichi Yamanaka', 'Shintenshin no Jutsu')",
    "('Sabaku', 2, 'Rasa', 'Jiton, Pasir Besi')",
    "('Kaguya', 3, 'Kaguya Patriarch', 'Shikotsumyaku')",
    "('Hozuki', 3, 'Mangetsu Hozuki', 'Suika no Jutsu')",
    "('Yotsuki', 4, 'A', 'Nintaijutsu')"
];
foreach ($data_clan as $v)
    $conn->query("INSERT INTO clan (nama_clan, desa_id, ketua_clan, jutsu_khas) VALUES $v");
echo "10 data Clan berhasil diinsert.<br>";

// Insert Shinobi
$data_shinobi = [
    "('REG-001', 'Naruto Uzumaki', 2, 1, 'Kage', 'Angin', 150)",
    "('REG-002', 'Sasuke Uchiha', 1, 1, 'Jonin', 'Api, Petir', 120)",
    "('REG-003', 'Itachi Uchiha', 1, 1, 'Anbu', 'Api, Air', 340)",
    "('REG-004', 'Hinata Hyuga', 3, 1, 'Chunin', 'Listrik, Api', 80)",
    "('REG-005', 'Neji Hyuga', 3, 1, 'Jonin', 'Air, Tanah', 110)",
    "('REG-006', 'Shikamaru Nara', 4, 1, 'Jonin', 'Api, Tanah', 200)",
    "('REG-007', 'Choji Akimichi', 5, 1, 'Chunin', 'Api, Tanah', 85)",
    "('REG-008', 'Ino Yamanaka', 6, 1, 'Chunin', 'Air, Tanah', 90)",
    "('REG-009', 'Gaara', 7, 2, 'Kage', 'Angin, Tanah', 210)",
    "('REG-010', 'Suigetsu Hozuki', 9, 3, 'Missing-Nin', 'Air', 75)"
];
foreach ($data_shinobi as $v)
    $conn->query("INSERT INTO shinobi (id, nama_shinobi, clan_id, desa_id, `rank`, elemen_chakra, jumlah_misi_selesai) VALUES $v");
echo "10 data Shinobi berhasil diinsert.<br>";

echo "<br><h3 style='color:green'>✅ Setup Selesai! Database, tabel, dan data dummy berhasil dibuat.</h3>";
$conn->close();
?>