<?php
// web/api/update_report.php
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? '';

if ($action == 'status') {
    $status = $_GET['status'] ?? 'solved';
    $query = "UPDATE datakendala SET status = :status WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->execute([':status' => $status, ':id' => $id]);
} 

if ($action == 'delete') {
    $query = "DELETE FROM datakendala WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->execute([':id' => $id]);
}

// Kembali ke halaman dashboard
header("Location: ../index.php");
exit;