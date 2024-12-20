<?php
session_start();
if (!isset($_SESSION['name'])) {
    header("Location: login.php");
    exit();
}

$colleges = [];
$departments = [];
$progfullname = '';
$progshortname = '';
$progcollid = '';
$progcolldeptid = '';
$progid = null;

try {
    $pdo = new PDO("mysql:host=localhost:3306;dbname=usjr", "root", "root");
    $stmt = $pdo->query("SELECT collid, collfullname FROM colleges");
    $colleges = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . htmlspecialchars($e->getMessage());
}

if (isset($_GET['id'])) {
    $progid = $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM programs WHERE progid = ?");
        $stmt->execute([$progid]);
        $program = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($program) {
            $progfullname = $program['progfullname'];
            $progshortname = $program['progshortname'];
            $progcollid = $program['progcollid'];
            $progcolldeptid = $program['progcolldeptid'];

           
            $stmt = $pdo->prepare("SELECT deptid, deptfullname FROM departments WHERE deptcollid = ?");
            $stmt->execute([$progcollid]);
            $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $_SESSION['error'] = "Program not found.";
            header("Location: programs.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error: " . htmlspecialchars($e->getMessage());
        header("Location: programs.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $progid = $_POST['progid'];
    $progfullname = $_POST['progfullname'];
    $progshortname = $_POST['progshortname'];
    $progcollid = $_POST['progcollid'];
    $progcolldeptid = $_POST['progcolldeptid'];
    $original_progid = $_POST['original_progid'];

    $errorMessage = '';

    if (empty($progid) || !is_numeric($progid) || $progid < 0) {
        $errorMessage .= 'Program ID is required and must be a non-negative number.\n';
    }
    if (empty($progfullname)) {
        $errorMessage .= 'Program Full Name is required.\n';
    }
    if (empty($progshortname)) {
        $errorMessage .= 'Program Short Name is required.\n';
    }
    if (empty($progcollid)) {
        $errorMessage .= 'College is required.\n';
    }
    if (empty($progcolldeptid)) {
        $errorMessage .= 'Department is required.\n';
    }

    if ($errorMessage) {
        echo "<script>showPopup('Validation Error', '$errorMessage');</script>";
    } else {
        try {
            $pdo = new PDO("mysql:host=localhost:3306;dbname=usjr", "root", "root");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM programs WHERE progfullname = ? AND progid != ?");
            $stmt->execute([$progfullname, $progid]);
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                echo "<script>showPopup('Error', 'Program name already exists.');</script>";
            } else {
                if (isset($_POST['original_progid']) && !empty($_POST['original_progid'])) {
                    $original_progid = $_POST['original_progid'];
                    $stmt = $pdo->prepare("
                        UPDATE programs
                        SET progfullname = ?, progshortname = ?, progcollid = ?, progcolldeptid = ?
                        WHERE progid = ?
                    ");
                    $stmt->execute([$progfullname, $progshortname, $progcollid, $progcolldeptid, $original_progid]);
                } else {
                    $stmt = $pdo->query("SELECT IFNULL(MAX(progid), 0) AS max_progid FROM programs");
                    $new_progid = $stmt->fetch(PDO::FETCH_ASSOC)['max_progid'] + 1;

                    $stmt = $pdo->prepare("
                        INSERT INTO programs (progid, progfullname, progshortname, progcollid, progcolldeptid)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$new_progid, $progfullname, $progshortname, $progcollid, $progcolldeptid]);
                }

                $_SESSION['success'] = "Program saved successfully!";
                echo json_encode(['success' => true]);
                exit();
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error: " . htmlspecialchars($e->getMessage());
            echo json_encode(['success' => false, 'error' => htmlspecialchars($e->getMessage())]);
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
    <title><?= isset($_GET['id']) ? 'Edit Program' : 'Add New Program' ?></title>
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
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body>
<div class="form-container">
    <h2><?= isset($_GET['id']) ? 'Edit Program' : 'Add New Program' ?></h2>
    <form id="programForm" action="editPrograms.php" method="POST">
        <div class="form-group">
            <label for="progid">Program ID</label>
            <input type="text" id="progid" name="progid" value="<?= htmlspecialchars($progid ?? '') ?>" placeholder="Enter program ID">
            <input type="hidden" id="original_progid" name="original_progid" value="<?= htmlspecialchars($progid ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="progfullname">Program Full Name</label>
            <input type="text" id="progfullname" name="progfullname" value="<?= htmlspecialchars($progfullname ?? '') ?>" placeholder="Enter program full name">
        </div>
        <div class="form-group">
            <label for="progshortname">Program Short Name</label>
            <input type="text" id="progshortname" name="progshortname" value="<?= htmlspecialchars($progshortname ?? '') ?>" placeholder="Enter program short name">
        </div>
        <div class="form-group">
            <label for="progcollid">College</label>
            <select id="progcollid" name="progcollid" onchange="fetchDepartments(this.value)">
                <option value="" disabled <?= empty($progcollid) ? 'selected' : '' ?>>Select college</option>
                <?php foreach ($colleges as $college): ?>
                    <option value="<?= $college['collid'] ?>" <?= $college['collid'] == $progcollid ? 'selected' : '' ?>><?= htmlspecialchars($college['collfullname'] ?? '') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="progcolldeptid">Department</label>
            <select id="progcolldeptid" name="progcolldeptid">
                <option value="" disabled <?= empty($progcolldeptid) ? 'selected' : '' ?>>Select department</option>
                <?php foreach ($departments as $department): ?>
                    <option value="<?= $department['deptid'] ?>" <?= $department['deptid'] == $progcolldeptid ? 'selected' : '' ?>><?= htmlspecialchars($department['deptfullname'] ?? '') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="button-group">
            <button type="submit"><?= isset($_GET['id']) ? 'Update Program' : 'Add Program' ?></button>
            <?php if ($progid): ?>
                <button type="button" id="revertChanges">Revert Changes</button>
            <?php else: ?>
                <button type="button" id="clearForm">Clear Form</button>
            <?php endif; ?>
            <a href="programs.php" class="cancel-btn">Cancel</a>
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

    document.getElementById('programForm').addEventListener('submit', async function(event) {
        event.preventDefault(); 

        const progid = document.getElementById('progid').value;
        const progfullname = document.getElementById('progfullname').value;
        const progshortname = document.getElementById('progshortname').value;
        const progcollid = document.getElementById('progcollid').value;
        const progcolldeptid = document.getElementById('progcolldeptid').value;
        const original_progid = document.getElementById('original_progid').value;

        let errorMessage = '';

        if (!progid || !isNumeric(progid) || progid < 0) {
            errorMessage += 'Program ID is required and must be a non-negative number.\n';
        }

        if (!progfullname) {
            errorMessage += 'Program Full Name is required.\n';
        }

        if (!progshortname) {
            errorMessage += 'Program Short Name is required.\n';
        }

        if (!progcollid) {
            errorMessage += 'College ID is required.\n';
        }

        if (!progcolldeptid) {
            errorMessage += 'Department ID is required.\n';
        }

        if (errorMessage) {
            showPopup('Validation Error', errorMessage);
            return;
        }

        const formData = new FormData();
        formData.append('progid', progid);
        formData.append('progfullname', progfullname);
        formData.append('progshortname', progshortname);
        formData.append('progcollid', progcollid);
        formData.append('progcolldeptid', progcolldeptid);
        formData.append('original_progid', original_progid);

        try {
            const response = await axios.post('save.programs.php', formData);
            if (response.data.success) {
                showPopup('Success', 'Program information saved successfully!');
                document.getElementById('popupButton').addEventListener('click', function() {
                    window.location.href = 'programs.php';
                }, { once: true });
            } else {
                showPopup('Error', 'Error: ' + (response.data.error || 'Unknown error occurred.'));
            }
        } catch (error) {
            showPopup('Error', 'Error: ' + (error.response.data.error || 'Unknown error occurred.'));
        }
    });

    async function fetchDepartments(collegeId, selectedDepartment = '') {
        try {
            const response = await axios.get('fetch_departments.php', { params: { collid: collegeId } });
            const departmentSelect = document.getElementById('progcolldeptid');
            departmentSelect.innerHTML = '<option value="" disabled selected>Select department</option>';
            response.data.departments.forEach(department => {
                const option = document.createElement('option');
                option.value = department.deptid;
                option.textContent = department.deptfullname;
                if (department.deptid == selectedDepartment) {
                    option.selected = true;
                }
                departmentSelect.appendChild(option);
            });
        } catch (error) {
            console.error('There was an error fetching departments!', error);
        }
    }

  
    if (document.getElementById('progcollid').value) {
        fetchDepartments(document.getElementById('progcollid').value, document.getElementById('progcolldeptid').value);
    }

    document.getElementById('progcollid').addEventListener('change', function() {
        fetchDepartments(this.value);
    });

    document.addEventListener('DOMContentLoaded', function() {
        const programForm = document.getElementById('programForm');
        const originalData = new FormData(programForm);
        const originalCollege = document.getElementById('progcollid').value;
        const originalDepartment = document.getElementById('progcolldeptid').value;

        <?php if ($progid): ?>
        document.getElementById('revertChanges').addEventListener('click', function(e) {
            e.preventDefault();
            for (let [key, value] of originalData.entries()) {
                if (programForm.elements[key]) {
                    programForm.elements[key].value = value;
                }
            }
            document.getElementById('progcollid').value = originalCollege;
            fetchDepartments(originalCollege, originalDepartment);
        });
        <?php else: ?>
        document.getElementById('clearForm').addEventListener('click', function(e) {
            e.preventDefault();
            programForm.reset();
            document.getElementById('progcollid').value = '';
            document.getElementById('progcolldeptid').innerHTML = '<option value="" disabled selected>Select department</option>';
        });
        <?php endif; ?>
    });

    function isNumeric(value) {
        return /^-?\d+$/.test(value);
    }
</script>
</body>
</html>
