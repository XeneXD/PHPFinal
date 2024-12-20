<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <style>
        body {
            height: 100vh; 
            margin: 0;
            display: flex;
            justify-content: center; 
            align-items: center;
            background-color: #f0f0f0;
            font-family: "Poppins", sans-serif;
            font-weight: 400;
        }        
        .registration-container {
            width: 35vw;
            padding: 30px;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }
        .registration-container h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        .input-group {
            margin-bottom: 15px;
        }
        .input-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        .input-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1em;
        }
        .action-buttons {
            display: flex;
            justify-content: space-between;
        }
        .action-buttons button {
            padding: 12px 18px;
            border: none;
            border-radius: 6px;
            font-size: 1em;
            cursor: pointer;
            width: 48%;
        }
        .submit-btn {
            background-color: #4CAF50;
            color: white;
        }
        .reset-btn {
            background-color: #f44336;
            color: white;
        }
        .submit-btn:hover {
            background-color: #45a049;
        }
        .reset-btn:hover {
            background-color: #e53935;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .login-link a {
            color: #0275d8;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 400px;
            text-align: center;
            border-radius: 12px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body>
    <div class="registration-container">
        <h1>Register User</h1>
        <form id="registerForm" action="save.user.php" method="post">
            <div class="input-group">
                <label for="user">Username</label>
                <input type="text" name="user" id="user" value="<?php if(isset($_POST['user'])) echo htmlspecialchars($_POST['user']); ?>">
            </div>
            <div class="input-group">
                <label for="pass">Password</label>
                <input type="password" name="pass" id="pass">
            </div>
            <div class="input-group">
                <label for="verify">Verify Password</label>
                <input type="password" name="verify" id="verify">
            </div>
            <div class="action-buttons">
                <button type="submit" name="reg" class="submit-btn">Register</button>
                <button type="reset" class="reset-btn">Clear</button>
            </div>
        </form>
        <div class="login-link">
            Already have an account? <a href="login.php">Sign in here</a>
        </div>
    </div>

   
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <p id="modal-message"></p>
        </div>
    </div>

    <script>
       
        var modal = document.getElementById("myModal");
        var modalMessage = document.getElementById("modal-message");

        var span = document.getElementsByClassName("close")[0];

    
        span.onclick = function() {
            modal.style.display = "none";
        }

        
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        
        function showModal(message) {
            modalMessage.innerText = message;
            modal.style.display = "flex";
        }

        document.getElementById('registerForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const user = document.getElementById('user').value;
            const pass = document.getElementById('pass').value;
            const verify = document.getElementById('verify').value;
            let errorMessage = '';

            if (!user) {
                errorMessage += 'Username is required.\n';
            }

            if (!pass) {
                errorMessage += 'Password is required.\n';
            }

            if (!verify) {
                errorMessage += 'Verify Password is required.\n';
            }

            if (pass !== verify) {
                errorMessage += 'Passwords do not match.\n';
                document.getElementById('pass').value = '';
                document.getElementById('verify').value = '';
            }

            if (errorMessage) {
                showModal(errorMessage);
                return;
            }

            try {
                const response = await axios.post('save.user.php', formData);
                if (response.data.success) {
                    showModal(response.data.message);
                    document.getElementById('registerForm').reset();
                } else {
                    showModal(response.data.error);
                }
            } catch (error) {
                showModal('An error occurred while registering. Please try again.');
                console.error(error);
            }
        });
    </script>
</body>
</html>