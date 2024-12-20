<?php
session_start();
if (!isset($_SESSION['name'])) {
    header("Location: login.php");
    exit();
}

$selectedCollege = '';
$programs = [];
$firstName = '';
$lastName = '';
$middleName = '';
$yearLevel = '';
$selectedProgram = '';
$studentId = null;

if (isset($_GET['id'])) {
    try {
        $pdo = new PDO("mysql:host=localhost:3306;dbname=usjr", "root", "root");
        $stmt = $pdo->prepare("
            SELECT s.*, p.progcollid 
            FROM students s 
            JOIN programs p ON s.studprogid = p.progid 
            WHERE s.studid = ?
        ");
        $stmt->execute([$_GET['id']]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($student) {
            $studentId = $student['studid'];
            $firstName = $student['studfirstname'];
            $lastName = $student['studlastname'];
            $middleName = $student['studmidname'];
            $selectedProgram = $student['studprogid'];
            $selectedCollege = $student['progcollid'];
            $yearLevel = $student['studyear'];

            $stmt = $pdo->prepare("SELECT progid, progfullname FROM programs WHERE progcollid = ?");
            $stmt->execute([$selectedCollege]);
            $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            echo "Student not found.";
        }
    } catch (PDOException $e) {
        echo "Error: " . htmlspecialchars($e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['college_id']) && !isset($_POST['save_student'])) {
    $selectedCollege = $_POST['college_id'];
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $middleName = $_POST['middle_name'];
    $yearLevel = $_POST['year_level'] ?? ''; 
    $selectedProgram = ''; 

    try {
        $pdo = new PDO("mysql:host=localhost:3306;dbname=usjr", "root", "root");
        $stmt = $pdo->prepare("SELECT progid, progfullname FROM programs WHERE progcollid = ?");
        $stmt->execute([$selectedCollege]);
        $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "<p>Error fetching programs: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_student'])) {
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $middleName = $_POST['middle_name'];
    $yearLevel = $_POST['year_level'] ?? ''; 
    $selectedCollege = $_POST['college_id'];
    $selectedProgram = $_POST['program_id'];

    $errorMessage = '';

    if (!empty($firstName) && preg_match('/\d/', $firstName)) {
        $errorMessage .= 'First Name cannot contain numbers.\n';
    }

    if (!empty($lastName) && preg_match('/\d/', $lastName)) {
        $errorMessage .= 'Last Name cannot contain numbers.\n';
    }

    if (!empty($middleName) && preg_match('/\d/', $middleName)) {
        $errorMessage .= 'Middle Name cannot contain numbers.\n';
    }

    if (!empty($yearLevel) && $yearLevel < 0) {
        $errorMessage .= 'Year Level cannot be negative.\n';
    }

    if (empty($selectedCollege)) {
        $errorMessage .= 'College is required.\n';
    }

    if (empty($selectedProgram)) {
        $errorMessage .= 'Program is required.\n';
    }

    if ($errorMessage) {
        echo "<script>showPopup('Validation Error', '$errorMessage');</script>";
    } else {
        try {
            $pdo = new PDO("mysql:host=localhost:3306;dbname=usjr", "root", "root");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            if ($studentId) {
                $stmt = $pdo->prepare("
                    UPDATE students
                    SET studfirstname = ?, studlastname = ?, studmidname = ?, studprogid = ?, studcollid = ?, studyear = ?
                    WHERE studid = ?
                ");
                $stmt->execute([$firstName, $lastName, $middleName, $selectedProgram, $selectedCollege, $yearLevel, $studentId]);
            } else {
                $stmt = $pdo->query("SELECT IFNULL(MAX(studid), 0) AS max_studid FROM students");
                $new_studid = $stmt->fetch(PDO::FETCH_ASSOC)['max_studid'] + 1;

                $stmt = $pdo->prepare("
                    INSERT INTO students (studid, studfirstname, studlastname, studmidname, studprogid, studcollid, studyear)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$new_studid, $firstName, $lastName, $middleName, $selectedProgram, $selectedCollege, $yearLevel]);
            }

            header("Location: students.php");
            exit();
        } catch (PDOException $e) {
            echo "<p>Error saving student data: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register New Student</title>
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
    input[readonly] {
        background-color: #f0f0f0;
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
    <h2><?= isset($_GET['new_student']) ? "New Student Added" : ($studentId ? "Edit Student" : "Register Student") ?></h2>
    <form id="studentForm" action="student.entry.php<?= $studentId ? '?id=' . $studentId : '' ?>" method="POST">
        <div class="form-group">
            <label for="student_id">Student ID</label>
            <input type="text" id="student_id" name="student_id" value="<?= htmlspecialchars($studentId ?? '') ?>" readonly autocomplete="off">
        </div>
        <div class="form-group">
            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($firstName) ?>" placeholder="Enter first name" autocomplete="given-name">
        </div>

        <div class="form-group">
            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($lastName) ?>" placeholder="Enter last name" autocomplete="family-name">
        </div>

        <div class="form-group">
            <label for="middle_name">Middle Name</label>
            <input type="text" id="middle_name" name="middle_name" value="<?= htmlspecialchars($middleName) ?>" placeholder="Enter middle name" autocomplete="additional-name">
        </div>

        <div class="form-group">
            <label for="college_id">College</label>
            <select id="college_id" name="college_id" autocomplete="organization">
                <option value="" disabled <?= empty($selectedCollege) ? 'selected' : '' ?>>Select college</option>
                <?php
                try {
                    $pdo = new PDO("mysql:host=localhost:3306;dbname=usjr", "root", "root");
                    $stmt = $pdo->query("SELECT collid, collfullname FROM colleges");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $selected = $row['collid'] == $selectedCollege ? 'selected' : '';
                        echo "<option value='{$row['collid']}' $selected>{$row['collid']} - {$row['collfullname']}</option>";
                    }
                } catch (PDOException $e) {
                    echo "<option value='' disabled>Error loading colleges</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="program_id">Program</label>
            <select id="program_id" name="program_id" autocomplete="organization">
                <option value="" selected disabled>Select a program</option>
                <?php
                foreach ($programs as $program) {
                    $isSelected = $program['progid'] == $selectedProgram ? 'selected' : '';
                    echo "<option value='{$program['progid']}' $isSelected>{$program['progid']} - {$program['progfullname']}</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="year_level">Year Level</label>
            <input type="number" id="year_level" name="year_level" value="<?= htmlspecialchars($yearLevel ?? '') ?>" placeholder="Enter year level" autocomplete="off">
        </div>

        <div class="button-group">
            <button type="submit" name="save_student"><?= $studentId ? "Update Student" : "Register" ?></button>
            <?php if ($studentId): ?>
                <button type="button" id="revertChanges">Revert Changes</button>
            <?php else: ?>
                <button type="button" id="clearForm">Clear Form</button>
            <?php endif; ?>
            <a href="students.php" class="cancel-btn">Cancel</a>
        </div>
    </form>
</div>

<div class="overlay" id="successPopup">
    <div class="popup">
        <h3>Student saved successfully!</h3>
        <button class="confirm-btn" onclick="closePopup()">OK</button>
    </div>
</div>

<div class="overlay" id="errorPopup">
    <div class="popup">
        <h3>Validation Error</h3>
        <p id="errorMessage"></p>
        <button class="confirm-btn" onclick="closeErrorPopup()">OK</button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const studentForm = document.getElementById('studentForm');
    const originalData = new FormData(studentForm);
    const originalCollege = document.getElementById('college_id').value;
    const originalProgram = document.getElementById('program_id').value;

    <?php if ($studentId): ?>
    document.getElementById('revertChanges').addEventListener('click', function(e) {
        e.preventDefault();
        for (let [key, value] of originalData.entries()) {
            if (studentForm.elements[key]) {
                studentForm.elements[key].value = value;
            }
        }
        document.getElementById('college_id').value = originalCollege;
        fetchPrograms(originalCollege, originalProgram);
    });
    <?php else: ?>
    document.getElementById('clearForm').addEventListener('click', function(e) {
        e.preventDefault();
        studentForm.reset();
        document.getElementById('college_id').value = '';
        document.getElementById('program_id').innerHTML = '<option value="" selected disabled>Select a program</option>';
    });
    <?php endif; ?>

    studentForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const firstName = document.getElementById('first_name').value;
        const lastName = document.getElementById('last_name').value;
        const middleName = document.getElementById('middle_name').value;
        const yearLevel = document.getElementById('year_level').value;
        const selectedCollege = document.getElementById('college_id').value;
        const selectedProgram = document.getElementById('program_id').value;

        let errorMessage = '';

        if (firstName && /\d/.test(firstName)) {
            errorMessage += 'First Name cannot contain numbers.\n';
        }

        if (lastName && /\d/.test(lastName)) {
            errorMessage += 'Last Name cannot contain numbers.\n';
        }

        if (middleName && /\d/.test(middleName)) {
            errorMessage += 'Middle Name cannot contain numbers.\n';
        }

        if (yearLevel && yearLevel < 0) {
            errorMessage += 'Year Level cannot be negative.\n';
        }

        if (!selectedCollege) {
            errorMessage += 'College is required.\n';
        }

        if (!selectedProgram) {
            errorMessage += 'Program is required.\n';
        }

        if (errorMessage) {
            document.getElementById('errorMessage').innerText = errorMessage;
            document.getElementById('errorPopup').style.display = 'flex';
            return;
        }

        const formData = new FormData(this);
        
        axios.post('save.students.php', formData)
            .then(response => {
                if (response.data.success) {
                    document.getElementById('successPopup').style.display = 'flex';
                } else {
                    alert('Failed to save student: ' + (response.data.error || 'Unknown error occurred.'));
                }
            })
            .catch(error => {
                console.error('There was an error!', error);
                alert('Failed to save student: ' + error.message);
            });
    });

    document.getElementById('college_id').addEventListener('change', function() {
        const collegeId = this.value;
        fetchPrograms(collegeId);
    });

    function fetchPrograms(collegeId, selectedProgram = '') {
        axios.get('fetch_programs.php', { params: { collid: collegeId } })
            .then(response => {
                const programSelect = document.getElementById('program_id');
                programSelect.innerHTML = '<option value="" selected disabled>Select a program</option>';
                response.data.programs.forEach(program => {
                    const option = document.createElement('option');
                    option.value = program.progid;
                    option.textContent = `${program.progid} - ${program.progfullname}`;
                    if (program.progid == selectedProgram) {
                        option.selected = true;
                    }
                    programSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('There was an error fetching programs!', error);
            });
    }
});

function closePopup() {
    document.getElementById('successPopup').style.display = 'none';
    window.location.href = 'students.php';
}

function closeErrorPopup() {
    document.getElementById('errorPopup').style.display = 'none';
}
</script>
</body>
</html>