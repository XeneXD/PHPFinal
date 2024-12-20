<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['name'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $deptid = $data['deptid'];

    try {
        $pdo = new PDO("mysql:host=localhost:3306;dbname=usjr", "root", "root");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

       
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM programs WHERE progdeptid = ?");
        $stmt->execute([$deptid]);
        $programCount = $stmt->fetchColumn();

        if ($programCount > 0) {
            echo json_encode(['success' => false, 'error' => 'Cannot delete department with associated programs']);
            exit();
        }

        $stmt = $pdo->prepare("DELETE FROM departments WHERE deptid = ?");
        $stmt->execute([$deptid]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to delete department']);
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'An error occurred while deleting the department']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>