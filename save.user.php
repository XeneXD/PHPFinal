<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['user'];
    $pass = $_POST['pass'];
    $verify = $_POST['verify'];

    if (empty($user) || empty($pass) || empty($verify)) {
        echo json_encode(['success' => false, 'error' => 'All fields are required.']);
        exit();
    }

    if ($pass !== $verify) {
        echo json_encode(['success' => false, 'error' => 'Passwords do not match.']);
        exit();
    }

    try {
        $pdo = new PDO("mysql:host=localhost:3306;dbname=usjr", "root", "root");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

       
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM appusers WHERE username = ?");
        $stmt->execute([$user]);
        $userExists = $stmt->fetchColumn();

        if ($userExists) {
            echo json_encode(['success' => false, 'error' => 'Username already exists. Please choose another.']);
        } else {
            $sql = "INSERT INTO appusers (username, password) VALUES (?, ?)";
            $insertPreparedStatement = $pdo->prepare($sql);
            $password = password_hash($pass, PASSWORD_DEFAULT);
            $insertPreparedStatement->bindParam(1, $user, PDO::PARAM_STR);
            $insertPreparedStatement->bindParam(2, $password, PDO::PARAM_STR);

            $result = $insertPreparedStatement->execute();
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Registration successful! You can now log in.']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Registration failed. Please try again.']);
            }
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Connection Failed: ' . htmlspecialchars($e->getMessage())]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}
?>
