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
    <title>Dashboard</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: url('assets/USJR.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            height: 100vh;
        }

        .banner {
            width: 100%;
            background-color: #4caf50;
            color: white;
            text-align: center;
            padding: 20px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            transition: height 0.6s ease-in-out, justify-content 0.6s ease-in-out;
            height: 100px; 
            z-index: 1000;
        }

        .banner:hover {
            height: 10vh; 
            justify-content: space-between;
        }

        .banner img {
            width: 50px;
            height: auto;
        }

        .banner .title {
            flex-grow: 1;
            font-size: 2.5em;
            text-align: center;
            transition: transform 0.6s ease-in-out;
            margin: 0;
        }

        .banner:hover .title {
            transform: translateX(0);
        }

        .user-info {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            margin-right: 20px;
            font-size: 1.2em;
            font-weight: bold;
            opacity: 0;
            transition: opacity 0.6s ease-in-out;
        }

        .banner:hover .user-info {
            opacity: 1;
        }

        .user-info span {
            margin-bottom: 5px;
        }

        .user-info button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 1.2em;
            cursor: pointer;
            text-decoration: none;
            color: white;
            background-color: #d9534f;
            transition: background-color 0.3s ease;
        }

        .user-info button:hover {
            background-color: #c9302c;
        }

        .content {
            text-align: center;
            margin-top: 150px; 
            padding-top: 150px; 
            transition: opacity 0.6s ease-in-out;
        }

        .banner:hover ~ .content {
            opacity: 0;
        }

        .button-group {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .button-container {
            padding: 50px;
            border-radius: 10px;
            animation: float 3s ease-in-out infinite;
            transition: animation 0.3s ease;
            width: 200px;
            height: 200px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .button-container:hover {
            animation: none;
        }

        .button-container.students {
            background: url('assets/students-background.jpg') no-repeat center center;
            background-size: cover;
        }

        .button-container.departments {
            background: url('assets/departments-background.jpg') no-repeat center center;
            background-size: cover;
        }

        .button-container.programs {
            background: url('assets/programs-background.jpg') no-repeat center center;
            background-size: cover;
        }

        .button-container.colleges {
            background: url('assets/colleges-background.jpg') no-repeat center center;
            background-size: cover;
        }

        .button-group a {
            padding: 20px 40px;
            border: none;
            border-radius: 5px;
            font-size: 1.5em;
            cursor: pointer;
            text-decoration: none;
            color: white;
            background-color: #0275d8;
            transition: background-color 0.3s ease;
        }

        .button-group a:hover {
            background-color: #025aa5;
        }

        .chart-container {
            width: 50%;
            margin: 50px auto;
            background-color: rgba(255, 255, 255, 0.8); 
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        @keyframes float {
            0% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
            100% {
                transform: translateY(0);
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body>
    <div class="banner">
        <img src="assets/Icon.png">
        <div class="title">Welcome Admin</div>
        <div class="user-info">
            <span>Logged in as: <?php echo htmlspecialchars($_SESSION['name']); ?></span>
            <button onclick="location.href='logout.php'">Logout</button>
        </div>
    </div>
    <div class="content">
        <div class="button-group">
            <div class="button-container students">
                <a href="students.php">Students</a>
            </div>
            <div class="button-container departments">
                <a href="departments.php">Departments</a>
            </div>
            <div class="button-container programs">
                <a href="programs.php">Programs</a>
            </div>
            <div class="button-container colleges">
                <a href="colleges.php">Colleges</a>
            </div>
        </div>
        <div class="chart-container">
            <canvas id="dashboardChart"></canvas>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('dashboardChart').getContext('2d');
            let dashboardChart;

            function fetchChartData() {
                axios.get('fetch_students.php')
                    .then(response => {
                        const data = response.data.students;
                        const chartData = {
                            labels: data.map(item => item.collfullname),
                            datasets: [{
                                data: data.map(item => item.student_count),
                                backgroundColor: ['#0275d8', '#5bc0de', '#5cb85c', '#f0ad4e', '#d9534f'],
                                hoverBackgroundColor: ['#025aa5', '#31b0d5', '#4cae4c', '#ec971f', '#c9302c']
                            }]
                        };
                        const options = {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                },
                                title: {
                                    display: true,
                                    text: 'Number of Students in Each College'
                                }
                            }
                        };

                        if (dashboardChart) {
                            dashboardChart.destroy();
                        }

                        dashboardChart = new Chart(ctx, {
                            type: 'pie',
                            data: chartData,
                            options: options
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching data:', error);
                    });
            }

            fetchChartData();

            window.addEventListener('focus', fetchChartData);
        });
    </script>
</body>
</html>
