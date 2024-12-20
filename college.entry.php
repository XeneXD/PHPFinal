<?php
session_start();
if (!isset($_SESSION['name'])) {
    header("Location: login.php");
    exit();
}

$collegeId = null;
$collegeFullName = '';
$collegeShortName = '';

if (isset($_GET['id'])) {
    try {
        $pdo = new PDO("mysql:host=localhost:3306;dbname=usjr", "root", "root");
        $stmt = $pdo->prepare("SELECT * FROM colleges WHERE collid = ?");
        $stmt->execute([$_GET['id']]);
        $college = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($college) {
            $collegeId = $college['collid'];
            $collegeFullName = $college['collfullname'];
            $collegeShortName = $college['collshortname'];
        } else {
            echo "College not found.";
        }
    } catch (PDOException $e) {
        echo "Error: " . htmlspecialchars($e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_college'])) {
    $collegeFullName = $_POST['college_fullname'];
    $collegeShortName = $_POST['college_shortname'];

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

        $_SESSION['success'] = "College saved successfully!";
        header("Location: colleges.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error: " . htmlspecialchars($e->getMessage());
        header("Location: college.entry.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($_GET['id']) ? 'Edit College' : 'Add New College' ?></title>
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
        input {
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
</head>
<body>
<div class="form-container">
    <h2><?= isset($_GET['id']) ? 'Edit College' : 'Add New College' ?></h2>
    <form id="collegeForm" action="college.entry.php<?= $collegeId ? '?id=' . $collegeId : '' ?>" method="POST">
        <div class="form-group">
            <label for="college_id">College ID</label>
            <input type="text" id="college_id" name="college_id" value="<?= htmlspecialchars($collegeId ?? '') ?>" readonly>
        </div>
        <div class="form-group">
            <label for="college_fullname">College Full Name</label>
            <input type="text" id="college_fullname" name="college_fullname" value="<?= htmlspecialchars($collegeFullName) ?>" placeholder="Enter college full name">
        </div>
        <div class="form-group">
            <label for="college_shortname">College Short Name</label>
            <input type="text" id="college_shortname" name="college_shortname" value="<?= htmlspecialchars($collegeShortName) ?>" placeholder="Enter college short name">
        </div>
        <div class="button-group">
            <button type="submit" name="save_college"><?= isset($_GET['id']) ? 'Update College' : 'Add College' ?></button>
            <a href="colleges.php" class="cancel-btn">Cancel</a>
        </div>
    </form>
</div>

<div class="overlay" id="successPopup">
    <div class="popup">
        <h3>College saved successfully!</h3>
        <button class="confirm-btn" onclick="closePopup()">OK</button>
    </div>
</div>

<script src="axios.min.js"></script>
<script>
document.getElementById('collegeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    const collegeFullName = document.getElementById('college_fullname').value;
    const collegeShortName = document.getElementById('college_shortname').value;

    let errorMessage = '';

    if (!collegeFullName) {
        errorMessage += 'College Full Name is required.\n';
    }

    if (!collegeShortName) {
        errorMessage += 'College Short Name is required.\n';
    }

    if (errorMessage) {
        alert(errorMessage);
        return;
    }

    axios.post('save.colleges.php', formData)
        .then(response => {
            if (response.data.success) {
                document.getElementById('successPopup').style.display = 'flex';
            } else {
                alert('Failed to save college: ' + (response.data.error || 'Unknown error occurred.'));
            }
        })
        .catch(error => {
            console.error('There was an error!', error);
            alert('Failed to save college: ' + error.message);
        });
});

function closePopup() {
    document.getElementById('successPopup').style.display = 'none';
    window.location.href = 'colleges.php';
}
</script>
</body>
</html>
