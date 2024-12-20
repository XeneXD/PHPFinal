<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['name'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

$deptid = $_POST['deptid'] ?? null;
$deptfullname = $_POST['deptfullname'] ?? null;
$deptshortname = $_POST['deptshortname'] ?? null;
$deptcollid = $_POST['deptcollid'] ?? null;

if (!$deptid || !$deptfullname || !$deptshortname || !$deptcollid) {
    echo json_encode(['success' => false, 'error' => 'All fields are required']);
    exit();
}

try {
    $pdo = new PDO("mysql:host=localhost:3306;dbname=usjr", "root", "root");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM departments WHERE deptfullname = ? AND deptid != ?
    ");
    $stmt->execute([$deptfullname, $deptid]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        echo json_encode(['success' => false, 'error' => 'Department name already exists.']);
        exit();
    }

    if (isset($_POST['original_deptid']) && !empty($_POST['original_deptid'])) {
        $original_deptid = $_POST['original_deptid'];
        $stmt = $pdo->prepare("
            UPDATE departments
            SET deptfullname = ?, deptshortname = ?, deptcollid = ?
            WHERE deptid = ?
        ");
        $stmt->execute([$deptfullname, $deptshortname, $deptcollid, $original_deptid]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO departments (deptid, deptfullname, deptshortname, deptcollid)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$deptid, $deptfullname, $deptshortname, $deptcollid]);
    }

    echo json_encode(['success' => true]);
    exit();
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An error occurred while saving the department']);
    exit();
}
?>