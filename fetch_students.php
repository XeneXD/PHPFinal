<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=localhost:3306;dbname=usjr", "root", "root");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("
        SELECT c.collfullname, COUNT(s.studid) AS student_count
        FROM colleges c
        LEFT JOIN students s ON c.collid = s.studcollid
        GROUP BY c.collfullname
    ");
    $studentsPerCollege = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['students' => $studentsPerCollege]);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['error' => 'An error occurred while fetching student data.']);
}
?>
