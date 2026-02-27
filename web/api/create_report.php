<?php
// web/api/create_report.php
header('Content-Type: application/json');

// Menggunakan path absolut agar lebih stabil
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $database = new Database();
        $db = $database->getConnection();

        // Mengambil data dari aplikasi Python
        $nim     = $_POST['nim'] ?? '';
        $nama    = $_POST['nama'] ?? '';
        $kelas   = $_POST['kelas'] ?? '';
        $lab     = $_POST['lab'] ?? '';
        $keluhan = $_POST['keluhan'] ?? '';

        // Validasi minimal
        if (empty($nim) || empty($nama) || empty($keluhan)) {
            echo json_encode(['status' => 'error', 'message' => 'NIM, Nama, dan Keluhan wajib diisi']);
            exit;
        }

        // Validasi Server-Side
        // ctype_digit: mengecek apakah semua karakter adalah angka
        if (!ctype_digit($nim)) {
            echo json_encode(['status' => 'error', 'message' => 'NIM harus berupa angka']);
            exit;
        }

        // preg_match: mengecek apakah hanya huruf dan spasi (a-z, A-Z)
        if (!preg_match("/^[a-zA-Z\s]*$/", $nama)) {
            echo json_encode(['status' => 'error', 'message' => 'Nama hanya boleh berisi huruf']);
            exit;
        }

        // Query INSERT menggunakan Prepared Statements (Sangat Aman)
        $query = "INSERT INTO datakendala (nim, nama, kelas, lab, keluhan) 
                  VALUES (:nim, :nama, :kelas, :lab, :keluhan)";
        
        $stmt = $db->prepare($query);
        
        // Bind parameter untuk mencegah SQL Injection
        $params = [
            ':nim'     => $nim,
            ':nama'    => $nama,
            ':kelas'   => $kelas,
            ':lab'     => $lab,
            ':keluhan' => $keluhan
        ];

        if ($stmt->execute($params)) {
            echo json_encode(['status' => 'success', 'message' => 'Laporan berhasil terkirim!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan ke database']);
        }

    } catch (PDOException $e) {
        // Mengirim error database dalam format JSON
        echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Metode akses tidak diizinkan']);
}