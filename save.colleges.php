<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['name'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

$collegeFullName = $_POST['college_fullname'] ?? null;
$collegeShortName = $_POST['college_shortname'] ?? null;
$collegeId = $_POST['college_id'] ?? null;

if (!$collegeFullName || !$collegeShortName) {
    echo json_encode(['success' => false, 'error' => 'Required fields are missing']);
    exit();
}

try {
    $pdo = new PDO("mysql:host=localhost:3306;dbname=usjr", "root", "root");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($collegeId) {
        $stmt = $pdo->prepare("UPDATE colleges SET collfullname = ?, collshortname = ? WHERE collid = ?");
        $stmt->execute([$collegeFullName, $collegeShortName, $collegeId]);
    } else {
        $stmt = $pdo->query("SELECT IFNULL(MAX(collid), 0) AS max_collid FROM colleges");
        $new_collid = $stmt->fetch(PDO::FETCH_ASSOC)['max_collid'] + 1;

        $stmt = $pdo->prepare("INSERT INTO colleges (collid, collfullname, collshortname) VALUES (?, ?, ?)");
        $stmt->execute([$new_collid, $collegeFullName, $collegeShortName]);
    }

    echo json_encode(['success' => true]);
    exit();
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => htmlspecialchars($e->getMessage())]);
    exit();
}
?>
