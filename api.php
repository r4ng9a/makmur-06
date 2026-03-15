<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include 'koneksi.php';

$method = $_SERVER['REQUEST_METHOD'];

function sendResponse($status, $message, $data = null) {
    $response = [
        "meta" => [
            "status" => $status,
            "message" => $message,
            "timestamp" => gmdate("Y-m-d\TH:i:s.v\Z")
        ]
    ];
    if ($data !== null) $response["data"] = $data;
    
    http_response_code($status);
    echo json_encode($response);
    exit;
}

switch ($method) {
    case 'GET':
        if (isset($_GET['action'])) {
            $action = $_GET['action'];
            
            if ($action == 'getAll') {
                // JOIN tabel untuk mendapatkan info lengkap (shinobi -> clan, shinobi -> desa)
                $sql = "SELECT s.id, s.nama_shinobi, s.`rank`, s.elemen_chakra, s.jumlah_misi_selesai, 
                               c.nama_clan, c.jutsu_khas, 
                               d.nama_desa, d.lokasi_negara 
                        FROM shinobi s 
                        JOIN clan c ON s.clan_id = c.id 
                        JOIN desa d ON s.desa_id = d.id";
                $result = $conn->query($sql);
                $data = $result->fetch_all(MYSQLI_ASSOC);
                
                foreach($data as &$row){
                    $row['jumlah_misi_selesai'] = (int)$row['jumlah_misi_selesai'];
                }
                
                sendResponse(200, "Daftar Seluruh Shinobi", $data);
            }
            elseif ($action == 'getById' && isset($_GET['id'])) {
                $id = $conn->real_escape_string($_GET['id']);
                $sql = "SELECT s.id, s.nama_shinobi, s.`rank`, s.elemen_chakra, s.jumlah_misi_selesai, 
                               c.nama_clan, c.jutsu_khas, 
                               d.nama_desa, d.lokasi_negara 
                        FROM shinobi s 
                        JOIN clan c ON s.clan_id = c.id 
                        JOIN desa d ON s.desa_id = d.id 
                        WHERE s.id = '$id'";
                $result = $conn->query($sql);
                $data = $result->fetch_assoc();
                
                if($data) {
                    $data['jumlah_misi_selesai'] = (int)$data['jumlah_misi_selesai'];
                    sendResponse(200, "Profil Shinobi Ditemukan", $data);
                } else {
                    sendResponse(404, "Data Shinobi Tidak Ditemukan");
                }
            }
            elseif ($action == 'count') {
                $result = $conn->query("SELECT COUNT(*) as total FROM shinobi");
                $data = $result->fetch_assoc();
                sendResponse(200, "Total Shinobi Terdaftar", ["total_shinobi" => (int)$data['total']]);
            }
            elseif ($action == 'stats') {
                $result = $conn->query("SELECT 
                                        COUNT(*) as total_shinobi, 
                                        MAX(jumlah_misi_selesai) as misi_terbanyak, 
                                        ROUND(AVG(jumlah_misi_selesai), 0) as rata_rata_misi 
                                        FROM shinobi");
                $data = $result->fetch_assoc();
                
                $formatted_data = [
                    "total_shinobi" => (int)$data['total_shinobi'],
                    "misi" => [
                        "rata_rata" => (int)$data['rata_rata_misi'],
                        "rekor_terbanyak" => (int)$data['misi_terbanyak']
                    ]
                ];
                sendResponse(200, "Statistik Misi Shinobi", $formatted_data);
            }
            elseif ($action == 'top') {
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
                $sql = "SELECT s.id, s.nama_shinobi, s.`rank`, s.jumlah_misi_selesai, c.nama_clan, d.nama_desa 
                        FROM shinobi s 
                        JOIN clan c ON s.clan_id = c.id 
                        JOIN desa d ON s.desa_id = d.id 
                        ORDER BY s.jumlah_misi_selesai DESC LIMIT $limit";
                $result = $conn->query($sql);
                $data = $result->fetch_all(MYSQLI_ASSOC);
                
                foreach($data as &$row){
                    $row['jumlah_misi_selesai'] = (int)$row['jumlah_misi_selesai'];
                }
                sendResponse(200, "Top " . $limit . " Shinobi Paling Berpengalaman", $data);
            }
            // === SEARCH: Pencarian fleksibel berdasarkan kolom apapun ===
            elseif ($action == 'search') {
                $where = [];
                if (isset($_GET['nama'])) {
                    $nama = $conn->real_escape_string($_GET['nama']);
                    $where[] = "s.nama_shinobi LIKE '%$nama%'";
                }
                if (isset($_GET['clan'])) {
                    $clan = $conn->real_escape_string($_GET['clan']);
                    $where[] = "c.nama_clan LIKE '%$clan%'";
                }
                if (isset($_GET['desa'])) {
                    $desa = $conn->real_escape_string($_GET['desa']);
                    $where[] = "d.nama_desa LIKE '%$desa%'";
                }
                if (isset($_GET['rank'])) {
                    $rank = $conn->real_escape_string($_GET['rank']);
                    $where[] = "s.`rank` LIKE '%$rank%'";
                }
                if (isset($_GET['elemen'])) {
                    $elemen = $conn->real_escape_string($_GET['elemen']);
                    $where[] = "s.elemen_chakra LIKE '%$elemen%'";
                }

                $sql = "SELECT s.id, s.nama_shinobi, s.`rank`, s.elemen_chakra, s.jumlah_misi_selesai, 
                               c.nama_clan, c.jutsu_khas, 
                               d.nama_desa, d.lokasi_negara 
                        FROM shinobi s 
                        JOIN clan c ON s.clan_id = c.id 
                        JOIN desa d ON s.desa_id = d.id";
                if (count($where) > 0) {
                    $sql .= " WHERE " . implode(" AND ", $where);
                }

                $result = $conn->query($sql);
                $data = $result->fetch_all(MYSQLI_ASSOC);
                foreach($data as &$row){
                    $row['jumlah_misi_selesai'] = (int)$row['jumlah_misi_selesai'];
                }
                sendResponse(200, "Hasil Pencarian (" . count($data) . " ditemukan)", $data);
            }
            // === GET DESA: Ambil semua data desa ===
            elseif ($action == 'getDesa') {
                $result = $conn->query("SELECT * FROM desa");
                $data = $result->fetch_all(MYSQLI_ASSOC);
                foreach($data as &$row) $row['id'] = (int)$row['id'];
                sendResponse(200, "Daftar Seluruh Desa", $data);
            }
            // === GET CLAN: Ambil semua data clan ===
            elseif ($action == 'getClan') {
                $result = $conn->query("SELECT c.*, d.nama_desa FROM clan c JOIN desa d ON c.desa_id = d.id");
                $data = $result->fetch_all(MYSQLI_ASSOC);
                foreach($data as &$row) {
                    $row['id'] = (int)$row['id'];
                    $row['desa_id'] = (int)$row['desa_id'];
                }
                sendResponse(200, "Daftar Seluruh Clan", $data);
            }
            else {
                sendResponse(400, "Action tidak valid");
            }
        } 
        else {
            // Default Get All tanpa JOIN (Simple View)
            $sql = "SELECT s.*, c.nama_clan, d.nama_desa 
                    FROM shinobi s 
                    JOIN clan c ON s.clan_id = c.id 
                    JOIN desa d ON s.desa_id = d.id";
            $result = $conn->query($sql);
            $data = $result->fetch_all(MYSQLI_ASSOC);
            foreach($data as &$row){
                $row['clan_id'] = (int)$row['clan_id'];
                $row['jumlah_misi_selesai'] = (int)$row['jumlah_misi_selesai'];
            }
            sendResponse(200, "Daftar Shinobi (Lite)", $data);
        }
        break;

    // ===================== POST (Tambah Shinobi Baru) =====================
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data) $data = $_POST;

        if(isset($data['id']) && isset($data['nama_shinobi']) && isset($data['clan_id']) && isset($data['desa_id']) && isset($data['rank'])) {
            $id = $conn->real_escape_string($data['id']);
            $nama = $conn->real_escape_string($data['nama_shinobi']);
            $clan_id = (int)$data['clan_id'];
            $desa_id = (int)$data['desa_id'];
            $rank = $conn->real_escape_string($data['rank']);
            $elemen = isset($data['elemen_chakra']) ? $conn->real_escape_string($data['elemen_chakra']) : '-';
            $misi = isset($data['jumlah_misi_selesai']) ? (int)$data['jumlah_misi_selesai'] : 0;

            $sql = "INSERT INTO shinobi (id, nama_shinobi, clan_id, desa_id, `rank`, elemen_chakra, jumlah_misi_selesai) 
                    VALUES ('$id', '$nama', $clan_id, $desa_id, '$rank', '$elemen', $misi)";
            if ($conn->query($sql) === TRUE) {
                sendResponse(201, "Shinobi baru berhasil didaftarkan", ["id" => $id, "nama_shinobi" => $nama]);
            } else {
                sendResponse(500, "Database Error: " . $conn->error);
            }
        } else {
            sendResponse(400, "Data tidak lengkap. Wajib: id, nama_shinobi, clan_id, desa_id, rank");
        }
        break;

    // ===================== PUT (Update Data Shinobi) =====================
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);

        if(isset($data['id'])) {
            $id = $conn->real_escape_string($data['id']);

            // Cek apakah shinobi ada
            $check = $conn->query("SELECT * FROM shinobi WHERE id = '$id'");
            if ($check->num_rows == 0) {
                sendResponse(404, "Shinobi dengan ID '$id' tidak ditemukan");
            }

            // Bangun query UPDATE secara dinamis
            $fields = [];
            if (isset($data['nama_shinobi'])) $fields[] = "nama_shinobi = '" . $conn->real_escape_string($data['nama_shinobi']) . "'";
            if (isset($data['clan_id'])) $fields[] = "clan_id = " . (int)$data['clan_id'];
            if (isset($data['desa_id'])) $fields[] = "desa_id = " . (int)$data['desa_id'];
            if (isset($data['rank'])) $fields[] = "`rank` = '" . $conn->real_escape_string($data['rank']) . "'";
            if (isset($data['elemen_chakra'])) $fields[] = "elemen_chakra = '" . $conn->real_escape_string($data['elemen_chakra']) . "'";
            if (isset($data['jumlah_misi_selesai'])) $fields[] = "jumlah_misi_selesai = " . (int)$data['jumlah_misi_selesai'];

            if (count($fields) == 0) {
                sendResponse(400, "Tidak ada field yang dikirim untuk di-update");
            }

            $sql = "UPDATE shinobi SET " . implode(", ", $fields) . " WHERE id = '$id'";
            if ($conn->query($sql) === TRUE) {
                sendResponse(200, "Data Shinobi '$id' berhasil diperbarui");
            } else {
                sendResponse(500, "Database Error: " . $conn->error);
            }
        } else {
            sendResponse(400, "Field 'id' wajib dikirim untuk update");
        }
        break;

    // ===================== DELETE (Hapus Shinobi — Fleksibel) =====================
    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data) $data = [];

        // Gabungkan dari query string juga
        if (isset($_GET['id'])) $data['id'] = $_GET['id'];

        // Bangun WHERE secara dinamis
        $where = [];

        if (isset($data['id']) && $data['id'] !== '') {
            $val = $conn->real_escape_string($data['id']);
            $where[] = "s.id = '$val'";
        }
        if (isset($data['nama_shinobi']) && $data['nama_shinobi'] !== '') {
            $val = $conn->real_escape_string($data['nama_shinobi']);
            $where[] = "s.nama_shinobi LIKE '%$val%'";
        }
        if (isset($data['clan']) && $data['clan'] !== '') {
            $val = $conn->real_escape_string($data['clan']);
            // Cari clan_id dari nama clan
            $clanResult = $conn->query("SELECT id FROM clan WHERE nama_clan LIKE '%$val%'");
            $clanIds = [];
            while ($row = $clanResult->fetch_assoc()) $clanIds[] = (int)$row['id'];
            if (count($clanIds) > 0) {
                $where[] = "s.clan_id IN (" . implode(",", $clanIds) . ")";
            } else {
                sendResponse(404, "Tidak ada clan yang cocok dengan '$val'");
            }
        }
        if (isset($data['desa']) && $data['desa'] !== '') {
            $val = $conn->real_escape_string($data['desa']);
            $desaResult = $conn->query("SELECT id FROM desa WHERE nama_desa LIKE '%$val%'");
            $desaIds = [];
            while ($row = $desaResult->fetch_assoc()) $desaIds[] = (int)$row['id'];
            if (count($desaIds) > 0) {
                $where[] = "s.desa_id IN (" . implode(",", $desaIds) . ")";
            } else {
                sendResponse(404, "Tidak ada desa yang cocok dengan '$val'");
            }
        }
        if (isset($data['rank']) && $data['rank'] !== '') {
            $val = $conn->real_escape_string($data['rank']);
            $where[] = "s.`rank` = '$val'";
        }
        if (isset($data['elemen_chakra']) && $data['elemen_chakra'] !== '') {
            $val = $conn->real_escape_string($data['elemen_chakra']);
            $where[] = "s.elemen_chakra LIKE '%$val%'";
        }

        if (count($where) == 0) {
            sendResponse(400, "Minimal 1 kriteria harus dikirim (id, nama_shinobi, clan, desa, rank, elemen_chakra)");
        }

        $whereStr = implode(" AND ", $where);

        // Hitung dulu berapa yang cocok
        $countResult = $conn->query("SELECT COUNT(*) as total FROM shinobi s WHERE $whereStr");
        $totalMatch = (int)$countResult->fetch_assoc()['total'];

        if ($totalMatch == 0) {
            sendResponse(404, "Tidak ada shinobi yang cocok dengan kriteria tersebut");
        }

        // Ambil data yang akan dihapus (untuk info response)
        $previewResult = $conn->query("SELECT s.id, s.nama_shinobi FROM shinobi s WHERE $whereStr");
        $deletedList = $previewResult->fetch_all(MYSQLI_ASSOC);

        // Hapus
        $sql = "DELETE s FROM shinobi s WHERE $whereStr";
        if ($conn->query($sql) === TRUE) {
            sendResponse(200, "$totalMatch shinobi berhasil dihapus", [
                "total_dihapus" => $totalMatch,
                "data_terhapus" => $deletedList
            ]);
        } else {
            sendResponse(500, "Database Error: " . $conn->error);
        }
        break;

    default:
        sendResponse(405, "Method tidak diizinkan. Gunakan: GET, POST, PUT, atau DELETE.");
        break;
}

$conn->close();
?>
