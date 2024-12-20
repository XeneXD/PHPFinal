<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=localhost:3306;dbname=usjr", "root", "root");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT collid, collfullname FROM colleges");
    $colleges = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'colleges' => $colleges]);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An error occurred while fetching colleges']);
}
?>
