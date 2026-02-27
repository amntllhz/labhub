<?php
// web/config/database.php

class Database {
    private $host = "localhost";
    private $db_name = "main"; // Sesuaikan dengan nama DB di phpMyAdmin
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            // Menggunakan DSN (Data Source Name) untuk koneksi PDO
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                $this->username, 
                $this->password
            );
            
            // Mengatur error mode ke Exception agar mudah di-debug
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Mengatur default fetch mode ke Associative Array
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
        } catch(PDOException $exception) {
            // Jika gagal, tampilkan pesan error (saat development)
            echo "Koneksi Database Gagal: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>