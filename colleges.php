<?php
session_start();
if (!isset($_SESSION['name'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Colleges</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f1f1f1;
            margin: 0;
            padding: 0;
        }

        .wrapper {
            width: 100%;
            max-width: 1200px;
            margin: 40px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 10px;
            border-bottom: 2px solid #ddd;
        }

        .header-title {
            font-size: 24px;
            color: #333;
        }

        .user-info {
            font-size: 14px;
            color: #555;
        }

        .logout-form {
            margin: 0;
        }

        .logout-btn {
            background-color: #d9534f;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .logout-btn:hover {
            background-color: #c9302c;
        }

        .action-bar {
            margin: 20px 0;
            display: flex;
            justify-content: space-between;
        }
        .action-bar .btn {
            flex: 1;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            margin: 0 5px;
            max-width: 200px; 
        }
        .add-btn {
            background-color: #0275d8;
        }
        .add-btn:hover {
            background-color: #025aa5;
        }
        .back-btn {
            background-color: #5bc0de;
        }
        .back-btn:hover {
            background-color: #31b0d5;
        }

        .message {
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 5px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        table th {
            background-color: #00FF00;
            color: white;
        }

        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        table tr:hover {
            background-color: #e6f7ff;
        }

        .edit-btn {
            background-color: #5cb85c;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .edit-btn:hover {
            background-color: #4cae4c;
        }

        .delete-btn {
            background-color: #d9534f;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .delete-btn:hover {
            background-color: #c9302c;
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
            background-color: #d9534f;
            color: white;
        }

        .popup .cancel-btn {
            background-color: #ccc;
            color: black;
        }
    </style>
    <script>
        function showPopupMessage(message, type) {
            const overlay = document.createElement('div');
            overlay.className = 'overlay';
            overlay.innerHTML = `
                <div class="popup">
                    <h3>${message}</h3>
                    <button class="confirm-btn">OK</button>
                </div>
            `;
            document.body.appendChild(overlay);
            overlay.style.display = 'flex';

            overlay.querySelector('.confirm-btn').addEventListener('click', () => {
                document.body.removeChild(overlay);
                if (type === 'success') {
                    location.reload();
                }
            });
        }

        function editCollege(id) {
            window.location.href = `college.entry.php?id=${id}`;
        }

        function deleteCollege(id) {
            const overlay = document.createElement('div');
            overlay.className = 'overlay';
            overlay.innerHTML = `
                <div class="popup">
                    <h3>Are you sure you want to delete this college?</h3>
                    <button class="confirm-btn">Yes</button>
                    <button class="cancel-btn">No</button>
                </div>
            `;
            document.body.appendChild(overlay);
            overlay.style.display = 'flex';

            overlay.querySelector('.confirm-btn').addEventListener('click', () => {
                axios.post('deletecollege.php', { id: id })
                    .then(response => {
                        if (response.data.success) {
                            showPopupMessage('College deleted successfully', 'success');
                        } else {
                            showPopupMessage('Failed to delete college: ' + response.data.error, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('There was an error!', error);
                        showPopupMessage('An error occurred while deleting the college', 'error');
                    });
            });

            overlay.querySelector('.cancel-btn').addEventListener('click', () => {
                document.body.removeChild(overlay);
            });
        }
    </script>
</head>
<body>
<div class="wrapper">
    <header>
        <div>
            <h1 class="header-title">Colleges</h1>
            <p class="user-info">Logged in as: <?php echo htmlspecialchars($_SESSION['name']); ?></p>
        </div>
        <form action="logout.php" method="POST" class="logout-form">
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </header>

    <div class="action-bar">
        <form action="college.entry.php" method="POST">
            <button type="submit" class="btn add-btn">Add New College</button>
        </form>
        <a href="dashboard.php" class="btn back-btn">Back to Dashboard</a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="message success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="message error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <h2>College Entries</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Short Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            try {
                $pdo = new PDO("mysql:host=localhost:3306;dbname=usjr", "root", "root");
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $stmt = $pdo->query("SELECT collid, collfullname, collshortname FROM colleges");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>
                        <td>{$row['collid']}</td>
                        <td>{$row['collfullname']}</td>
                        <td>{$row['collshortname']}</td>
                        <td>
                            <button class='edit-btn' onclick='editCollege({$row['collid']})'>Edit</button>
                            <button class='delete-btn' onclick='deleteCollege({$row['collid']})'>Delete</button>
                        </td>
                    </tr>";
                }
            } catch (PDOException $e) {
                error_log("Database error: " . $e->getMessage());
                echo "<tr><td colspan='4'>An error occurred while retrieving college data.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script src="axios.min.js"></script>
</body>
</html>
