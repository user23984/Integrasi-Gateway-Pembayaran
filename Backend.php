<?php
include "conn.php";

// Tambahkan header CORS untuk akses dari frontend
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Disable error reporting
error_reporting(0);
ini_set('display_errors', 0);

$response = [];

// Cek metode request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Cek apakah ada pembayaran dengan status 'processing'
    $query = $mysqli->query("SELECT id, total_price, created_at FROM pembayaran WHERE status1 = 'processing' ORDER BY id ASC LIMIT 1");

    if (!$query) {
        $response['status'] = 'error';
        $response['message'] = 'Queue query failed: ' . mysqli_error($mysqli);
    } else if ($row = $query->fetch_assoc()) {
        // Jika ada yang 'processing', ambil data tersebut
        $response['status'] = 'success';
        $response['data'] = [
            'id' => $row['id'],
            'total_price' => $row['total_price'], // Pastikan nama kolom benar
            'created_at' => $row['created_at'],
        ];
    } else {
        // Jika tidak ada yang 'processing', cari status 'pending'
        $pendingQuery = $mysqli->query("SELECT id, total_price, created_at FROM pembayaran WHERE status1 = 'pending' ORDER BY id ASC LIMIT 1");

        if (!$pendingQuery) {
            $response['status'] = 'error';
            $response['message'] = 'Queue query failed: ' . mysqli_error($mysqli);
        } else if ($row = $pendingQuery->fetch_assoc()) {
            // Kirim data status pending, tanpa mengubah statusnya
            $response['status'] = 'success';
            $response['data'] = [
                'id' => $row['id'],
                'total_price' => $row['total_price'],
                'created_at' => $row['created_at'],
            ];
        } else {
            // Jika tidak ada yang 'pending' atau 'processing'
            $response['status'] = 'no_tasks';
            $response['message'] = 'No tasks with status pending or processing.';
        }
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method.';
}

// Header JSON dan kirim respons
header('Content-Type: application/json');
echo json_encode($response);
?>
