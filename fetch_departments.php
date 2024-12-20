<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=localhost:3306;dbname=usjr", "root", "root");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $collid = $_GET['collid'] ?? null;
    if ($collid) {
        $stmt = $pdo->prepare("SELECT deptid, deptfullname, deptshortname FROM departments WHERE deptcollid = ?");
        $stmt->execute([$collid]);
    } else {
        $stmt = $pdo->query("SELECT deptid, deptfullname, deptshortname FROM departments");
    }
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'departments' => $departments]);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An error occurred while fetching departments']);
}
?>