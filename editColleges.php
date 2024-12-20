<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['name'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

$collegeFullName = $_POST['college_fullname'];
$collegeId = $_POST['college_id'];

try {
    $pdo = new PDO("mysql:host=localhost:3306;dbname=usjr", "root", "root");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("UPDATE colleges SET collfullname = ? WHERE collid = ?");
    $stmt->execute([$collegeFullName, $collegeId]);

    echo json_encode(['success' => true]);
    exit();
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => htmlspecialchars($e->getMessage())]);
    exit();
}
?>
