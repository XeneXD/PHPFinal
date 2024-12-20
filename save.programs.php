<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['name'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

$progid = $_POST['progid'] ?? null;
$progfullname = $_POST['progfullname'] ?? null;
$progshortname = $_POST['progshortname'] ?? null;
$progcollid = $_POST['progcollid'] ?? null;
$progcolldeptid = $_POST['progcolldeptid'] ?? null;
$original_progid = $_POST['original_progid'] ?? null;

if (!$progid || !$progfullname || !$progshortname || !$progcollid || !$progcolldeptid) {
    echo json_encode(['success' => false, 'error' => 'All fields are required']);
    exit();
}

try {
    $pdo = new PDO("mysql:host=localhost:3306;dbname=usjr", "root", "root");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM programs WHERE progfullname = ? AND progid != ?");
    $stmt->execute([$progfullname, $original_progid]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        echo json_encode(['success' => false, 'error' => 'Program name already exists.']);
        exit();
    }

    if ($progid !== $original_progid) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM programs WHERE progid = ?");
        $stmt->execute([$progid]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            echo json_encode(['success' => false, 'error' => 'Program ID already exists.']);
            exit();
        }
    }

    if (!empty($original_progid)) {
        $stmt = $pdo->prepare("
            UPDATE programs
            SET progid = ?, progfullname = ?, progshortname = ?, progcollid = ?, progcolldeptid = ?
            WHERE progid = ?
        ");
        $stmt->execute([$progid, $progfullname, $progshortname, $progcollid, $progcolldeptid, $original_progid]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO programs (progid, progfullname, progshortname, progcollid, progcolldeptid)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$progid, $progfullname, $progshortname, $progcollid, $progcolldeptid]);
    }

    echo json_encode(['success' => true]);
    exit();
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An error occurred while saving the program: ' . htmlspecialchars($e->getMessage())]);
    exit();
}
?>
