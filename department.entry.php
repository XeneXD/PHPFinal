<?php
session_start();
if (!isset($_SESSION['name'])) {
    header("Location: login.php");
    exit();
}

$colleges = [];
$deptfullname = '';
$deptshortname = '';
$deptcollid = '';
$deptid = null;

try {
    $pdo = new PDO("mysql:host=localhost:3306;dbname=usjr", "root", "root");
    $stmt = $pdo->query("SELECT collid, collfullname FROM colleges");
    $colleges = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . htmlspecialchars($e->getMessage());
}

if (isset($_GET['id'])) {
    $deptid = $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM departments WHERE deptid = ?");
        $stmt->execute([$deptid]);
        $department = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($department) {
            $deptfullname = $department['deptfullname'];
            $deptshortname = $department['deptshortname'];
            $deptcollid = $department['deptcollid'];
        } else {
            $_SESSION['error'] = "Department not found.";
            header("Location: departments.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error: " . htmlspecialchars($e->getMessage());
        header("Location: departments.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deptid = $_POST['deptid'];
    $deptfullname = $_POST['deptfullname'];
    $deptshortname = $_POST['deptshortname'];
    $deptcollid = $_POST['deptcollid'];

    $errorMessage = '';

    if (empty($deptid) || !ctype_digit($deptid) || $deptid < 0) {
        $errorMessage .= 'Department ID is required and must be a non-negative number.\n';
    }
    if (empty($deptfullname)) {
        $errorMessage .= 'Department Full Name is required.\n';
    }
    if (empty($deptshortname)) {
        $errorMessage .= 'Department Short Name is required.\n';
    }
    if (empty($deptcollid)) {
        $errorMessage .= 'College is required.\n';
    }

    if ($errorMessage) {
        echo "<script>showPopup('Validation Error', '$errorMessage');</script>";
    } else {
        try {
            $pdo = new PDO("mysql:host=localhost:3306;dbname=usjr", "root", "root");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

       
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM departments WHERE deptid = ? AND deptcollid = ?");
            $stmt->execute([$deptid, $deptcollid]);
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                echo "<script>showPopup('Error', 'Department ID already exists within this college.');</script>";
            } else {
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

                $_SESSION['success'] = "Department saved successfully!";
                header("Location: departments.php");
                exit();
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error: " . htmlspecialchars($e->getMessage());
            header("Location: departments.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($_GET['id']) ? 'Edit Department' : 'Add New Department' ?></title>
    <style>
        body {
            background-color: #e8f5e9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: 'Poppins', sans-serif;
            margin: 0;
        }
        .form-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 450px;
            width: 100%;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #2e7d32;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #555;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .button-group {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        button, .cancel-btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            margin: 0 5px;
        }
        button {
            background-color: #4caf50;
        }
        button:hover {
            background-color: #388e3c;
        }
        .cancel-btn {
            background-color: #d32f2f;
        }
        .cancel-btn:hover {
            background-color: #b71c1c;
        }
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: none;
            justify-content: center;
            align-items: center;
        }
        .popup {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .popup h3 {
            margin: 0 0 10px;
        }
        .popup button {
            margin: 10px 5px 0;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .popup .confirm-btn {
            background-color: #4caf50;
            color: white;
        }
    </style>
    <script src="axios.min.js"></script>
</head>
<body>
<div class="form-container">
    <h2><?= isset($_GET['id']) ? 'Edit Department' : 'Add New Department' ?></h2>
    <form id="departmentForm" action="save.departments.php" method="POST">
        <div class="form-group">
            <label for="deptid">Department ID</label>
            <input type="text" id="deptid" name="deptid" value="<?= htmlspecialchars($deptid ?? '') ?>" placeholder="Enter department ID">
            <input type="hidden" id="original_deptid" name="original_deptid" value="<?= htmlspecialchars($deptid ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="deptfullname">Department Full Name</label>
            <input type="text" id="deptfullname" name="deptfullname" value="<?= htmlspecialchars($deptfullname ?? '') ?>" placeholder="Enter department full name">
        </div>
        <div class="form-group">
            <label for="deptshortname">Department Short Name</label>
            <input type="text" id="deptshortname" name="deptshortname" value="<?= htmlspecialchars($deptshortname ?? '') ?>" placeholder="Enter department short name">
        </div>
        <div class="form-group">
            <label for="deptcollid">College</label>
            <select id="deptcollid" name="deptcollid">
                <option value="" disabled <?= empty($deptcollid) ? 'selected' : '' ?>>Select college</option>
                <?php foreach ($colleges as $college): ?>
                    <option value="<?= $college['collid'] ?>" <?= $college['collid'] == $deptcollid ? 'selected' : '' ?>><?= htmlspecialchars($college['collfullname'] ?? '') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="button-group">
            <button type="submit"><?= isset($_GET['id']) ? 'Update Department' : 'Add Department' ?></button>
            <a href="departments.php" class="cancel-btn">Cancel</a>
        </div>
    </form>
</div>

<div class="overlay" id="overlay">
    <div class="popup" id="popup">
        <h3 id="popupTitle"></h3>
        <p id="popupMessage"></p>
        <button class="confirm-btn" id="popupButton">OK</button>
    </div>
</div>

<script>
    function showPopup(title, message) {
        document.getElementById('popupTitle').innerText = title;
        document.getElementById('popupMessage').innerText = message;
        document.getElementById('overlay').style.display = 'flex';
    }

    document.getElementById('popupButton').addEventListener('click', function() {
        document.getElementById('overlay').style.display = 'none';
    });

    document.getElementById('departmentForm').addEventListener('submit', async function(event) {
        event.preventDefault(); 

        const deptid = document.getElementById('deptid').value;
        const deptfullname = document.getElementById('deptfullname').value;
        const deptshortname = document.getElementById('deptshortname').value;
        const deptcollid = document.getElementById('deptcollid').value;
        const original_deptid = document.getElementById('original_deptid').value;

        let errorMessage = '';

        if (!deptid || !/^\d+$/.test(deptid) || parseInt(deptid) < 0) {
            errorMessage += 'Department ID is required and must be a non-negative number.\n';
        }

        if (!deptfullname) {
            errorMessage += 'Department Full Name is required.\n';
        }

        if (!deptshortname) {
            errorMessage += 'Department Short Name is required.\n';
        }

        if (!deptcollid) {
            errorMessage += 'College ID is required.\n';
        }

        if (errorMessage) {
            showPopup('Validation Error', errorMessage);
            return;
        }

        const formData = new FormData();
        formData.append('deptid', deptid);
        formData.append('deptfullname', deptfullname);
        formData.append('deptshortname', deptshortname);
        formData.append('deptcollid', deptcollid);
        formData.append('original_deptid', original_deptid);

        try {
            const response = await axios.post('save.departments.php', formData);
            if (response.data.success) {
                showPopup('Success', 'Department information saved successfully!');
                document.getElementById('popupButton').addEventListener('click', function() {
                    window.location.href = 'departments.php';
                }, { once: true });
            } else {
                showPopup('Error', 'Error: ' + (response.data.error || 'Unknown error occurred.'));
            }
        } catch (error) {
            showPopup('Error', 'Error: ' + (error.response.data.error || 'Unknown error occurred.'));
        }
    });
</script>
</body>
</html>