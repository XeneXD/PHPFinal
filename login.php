<?php
session_start();

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $pdo = new PDO("mysql:host=localhost:3306;dbname=usjr", "root", "root");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT password FROM appusers WHERE username = :username");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $row['password'])) {
                $_SESSION['name'] = $username;
                echo json_encode(['success' => true]);
                exit();
            } else {
                echo json_encode(['success' => false, 'error' => 'Invalid username or password.']);
                exit();
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid username or password.']);
            exit();
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Connection failed: ' . $e->getMessage()]);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: 'Poppins', Arial, Helvetica, sans-serif;
            background-color: #f0f0f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-wrapper {
            max-width: 400px;
            width: 100%;
            padding: 25px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border: 2px solid #ccc;
        }

        .login-wrapper h2 {
            text-align: center;
            background-color: #333;
            color: #ffffff;
            padding: 10px;
            border-radius: 8px;
            font-size: 1.5rem;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        .button-group {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .button-group button {
            width: 100%;
            padding: 12px;
            border: none;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
        }

        .login-btn {
            background-color: #007bff;
            color: #ffffff;
        }

        .login-btn:hover {
            background-color: #0056b3;
        }

        .clear-btn {
            background-color: #e63946;
            color: #ffffff;
        }

        .clear-btn:hover {
            background-color: #b00020;
        }

        .error-message {
            color: #e63946;
            background-color: #ffe5e5;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 15px;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
        }

        .register-link a {
            color: #007bff;
            text-decoration: none;
        }

        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body>
    <div class="login-wrapper">
        <h2>Login</h2>

        <div id="error-message" class="error-message" style="display: none;"></div>

        <form id="loginForm">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" autocomplete="current-password">
            </div>
            <div class="button-group">
                <button type="submit" class="login-btn">Log-in</button>
                <button type="reset" class="clear-btn">Clear</button>
            </div>
        </form>
        <div class="register-link">
            <p>If you don't have an account, <a href="register.php">Register here</a>.</p>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;

            axios.post('fetch_user.php')
                .then(response => {
                    if (response.data.success) {
                        const users = response.data.users;
                        const userExists = users.some(user => user.username === username);

                        if (userExists) {
                            axios.post('login.php', new URLSearchParams({
                                username: username,
                                password: password,
                                login: true
                            }))
                            .then(response => {
                                if (response.data.success) {
                                    window.location.href = 'Dashboard.php';
                                } else {
                                    document.getElementById('error-message').innerText = response.data.error;
                                    document.getElementById('error-message').style.display = 'block';
                                }
                            })
                            .catch(error => {
                                console.error('There was an error!', error);
                                document.getElementById('error-message').innerText = 'An error occurred while logging in.';
                                document.getElementById('error-message').style.display = 'block';
                            });
                        } else {
                            document.getElementById('error-message').innerText = 'User not registered.';
                            document.getElementById('error-message').style.display = 'block';
                        }
                    } else {
                        document.getElementById('error-message').innerText = 'Failed to fetch users.';
                        document.getElementById('error-message').style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('There was an error!', error);
                    document.getElementById('error-message').innerText = 'An error occurred while fetching users.';
                    document.getElementById('error-message').style.display = 'block';
                });
        });
    </script>
</body>
</html>