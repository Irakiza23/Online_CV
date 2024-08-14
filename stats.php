<?php
session_start();

if (!isset($_SESSION["loggedin"]) || !$_SESSION["loggedin"]) {
    header("Location: login.php");
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cv";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user profile information
$user_id = $_SESSION['user_id'];
$sql = "SELECT nom, prenom, photo FROM utilisateur WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_info = $result->fetch_assoc();

$user_name = $user_info['prenom'] . ' ' . $user_info['nom'];
$user_profile_pic = $user_info['photo'] ? $user_info['photo'] : 'default-profile.png';

// Logout logic
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: login.php");
    exit;
}

// Fetch statistics
$tables = ['competences', 'experiences', 'formations', 'langue_parle'];
$stats = [];

foreach ($tables as $table) {
    $sql = "SELECT COUNT(*) AS count FROM $table";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $stats[$table] = $row['count'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            padding-top: 70px;
        }
        .header-info {
            position: fixed;
            top: 0;
            right: 0;
            padding: 10px 20px;
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            width: calc(100% - 200px);
            border-bottom: 1px solid #dee2e6;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header-info img {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .header-info .btn {
            margin-left: 10px;
        }
        .container {
            margin-right: 220px;
        }
        .stats-card {
            margin-bottom: 20px;
        }
        .stats-card .card-body {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .stats-card .card-body .number {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .stats-card .card-body .icon {
            font-size: 2rem;
            color: #007bff;
        }
        .card {
            border: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="header-info">
        <img src="<?php echo htmlspecialchars($user_profile_pic); ?>" alt="Profile Picture">
        <span><?php echo htmlspecialchars($user_name); ?></span>
        <a href="admin.php" class="btn btn-secondary">
            <i class="fas fa-cogs"></i>
        </a>
        <a href="?action=logout" class="btn btn-danger">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </div>

    <div class="container">
        <h1 class="text-center mb-4">Statistics</h1>
        <div class="row">
            <div class="col-md-6 stats-card">
                <div class="card">
                    <div class="card-body">
                        <i class="fas fa-briefcase icon"></i>
                        <div class="info">
                            <h5 class="card-title">Competences</h5>
                            <div class="number"><?php echo $stats['competences']; ?></div>
                        </div>
                        <canvas id="competencesChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6 stats-card">
                <div class="card">
                    <div class="card-body">
                        <i class="fas fa-briefcase icon"></i>
                        <div class="info">
                            <h5 class="card-title">Experiences</h5>
                            <div class="number"><?php echo $stats['experiences']; ?></div>
                        </div>
                        <canvas id="experiencesChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6 stats-card">
                <div class="card">
                    <div class="card-body">
                        <i class="fas fa-graduation-cap icon"></i>
                        <div class="info">
                            <h5 class="card-title">Formations</h5>
                            <div class="number"><?php echo $stats['formations']; ?></div>
                        </div>
                        <canvas id="formationsChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6 stats-card">
                <div class="card">
                    <div class="card-body">
                        <i class="fas fa-language icon"></i>
                        <div class="info">
                            <h5 class="card-title">Languages</h5>
                            <div class="number"><?php echo $stats['langue_parle']; ?></div>
                        </div>
                        <canvas id="languagesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const stats = <?php echo json_encode($stats); ?>;
        
        const competencesChart = new Chart(document.getElementById('competencesChart'), {
            type: 'bar',
            data: {
                labels: ['Competences'],
                datasets: [{
                    label: 'Number of Competences',
                    data: [stats.competences],
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        const experiencesChart = new Chart(document.getElementById('experiencesChart'), {
            type: 'bar',
            data: {
                labels: ['Experiences'],
                datasets: [{
                    label: 'Number of Experiences',
                    data: [stats.experiences],
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        const formationsChart = new Chart(document.getElementById('formationsChart'), {
            type: 'bar',
            data: {
                labels: ['Formations'],
                datasets: [{
                    label: 'Number of Formations',
                    data: [stats.formations],
                    backgroundColor: 'rgba(255, 159, 64, 0.2)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        const languagesChart = new Chart(document.getElementById('languagesChart'), {
            type: 'bar',
            data: {
                labels: ['Languages'],
                datasets: [{
                    label: 'Number of Languages',
                    data: [stats.langue_parle],
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
