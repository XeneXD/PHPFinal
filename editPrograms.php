<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['name'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $progid = $data['progid'];
    $progfullname = $data['progfullname'];
    $progshortname = $data['progshortname'];
    $progcollid = $data['progcollid'];
    $progcolldeptid = $data['progcolldeptid'];

    try {
        $pdo = new PDO("mysql:host=localhost:3306;dbname=usjr", "root", "root");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM programs WHERE progfullname = ? AND progid != ?");
        $stmt->execute([$progfullname, $progid]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            echo json_encode(['success' => false, 'error' => 'Program name already exists.']);
            exit();
        }

        $stmt = $pdo->prepare("UPDATE programs SET progfullname = ?, progshortname = ?, progcollid = ?, progcolldeptid = ? WHERE progid = ?");
        $stmt->execute([$progfullname, $progshortname, $progcollid, $progcolldeptid, $progid]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update program']);
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'An error occurred while updating the program']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const programForm = document.getElementById('programForm');
        const originalData = new FormData(programForm);
        const originalCollege = document.getElementById('progcollid').value;
        const originalDepartment = document.getElementById('progcolldeptid').value;

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

        programForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            axios.post('editPrograms.php', formData)
                .then(response => {
                    if (response.data.success) {
                        alert('Program edited successfully');
                        window.location.href = 'programs.php';
                    } else {
                        alert('Failed to edit program: ' + response.data.error);
                    }
                })
                .catch(error => {
                    console.error('There was an error!', error);
                });
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
    });
</script>
