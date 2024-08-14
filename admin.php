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

$user_id = $_SESSION['user_id'];

// Fetch user profile information
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

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .container {
            max-width: 600px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            flex-direction: column;
            padding-top: 20px; /* Space between heading and first button */
        }
        .btn-group-vertical {
            width: 100%;
        }
        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn i {
            margin-right: 8px;
        }
        .dropdown-menu {
            width: 100%;
        }
        .btn-group {
            margin-bottom: 20px; /* Space between each button */
        }
        .profile-info {
            position: absolute;
            top: 15px;
            right: 15px;
            display: flex;
            align-items: center;
        }
        .profile-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .profile-info .logout-button {
            margin-left: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-info">
            <img src="<?php echo htmlspecialchars($user_profile_pic); ?>" alt="Profile Picture">
            <span><?php echo htmlspecialchars($user_name); ?></span>
            <a href="?action=logout" class="btn btn-danger logout-button">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
        <h1 class="text-center mb-4">Admin Panel</h1>

        <div class="btn-group-vertical">
            <!-- Manage Competences -->
            <div class="btn-group">
                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-briefcase"></i> Manage Competences
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="create.php?table=competences"><i class="fas fa-plus"></i> Create Competence</a></li>
                    <li><a class="dropdown-item" href="read.php?table=competences"><i class="fas fa-eye"></i> Read Competences</a></li>
                    <li><a class="dropdown-item" href="update.php?table=competences"><i class="fas fa-pencil-alt"></i> Update Competence</a></li>
                    <li><a class="dropdown-item" href="delete.php?table=competences"><i class="fas fa-trash"></i> Delete Competence</a></li>
                </ul>
            </div>

            <!-- Manage Experiences -->
            <div class="btn-group">
                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-briefcase"></i> Manage Experiences
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="create.php?table=experiences"><i class="fas fa-plus"></i> Create Experience</a></li>
                    <li><a class="dropdown-item" href="read.php?table=experiences"><i class="fas fa-eye"></i> Read Experiences</a></li>
                    <li><a class="dropdown-item" href="update.php?table=experiences"><i class="fas fa-pencil-alt"></i> Update Experience</a></li>
                    <li><a class="dropdown-item" href="delete.php?table=experiences"><i class="fas fa-trash"></i> Delete Experience</a></li>
                </ul>
            </div>

            <!-- Manage Formations -->
            <div class="btn-group">
                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-graduation-cap"></i> Manage Formations
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="create.php?table=formations"><i class="fas fa-plus"></i> Create Formation</a></li>
                    <li><a class="dropdown-item" href="read.php?table=formations"><i class="fas fa-eye"></i> Read Formations</a></li>
                    <li><a class="dropdown-item" href="update.php?table=formations"><i class="fas fa-pencil-alt"></i> Update Formation</a></li>
                    <li><a class="dropdown-item" href="delete.php?table=formations"><i class="fas fa-trash"></i> Delete Formation</a></li>
                </ul>
            </div>

            <!-- Manage Languages -->
            <div class="btn-group">
                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-language"></i> Manage Languages
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="create.php?table=langue_parle"><i class="fas fa-plus"></i> Create Language</a></li>
                    <li><a class="dropdown-item" href="read.php?table=langue_parle"><i class="fas fa-eye"></i> Read Languages</a></li>
                    <li><a class="dropdown-item" href="update.php?table=langue_parle"><i class="fas fa-pencil-alt"></i> Update Language</a></li>
                    <li><a class="dropdown-item" href="delete.php?table=langue_parle"><i class="fas fa-trash"></i> Delete Language</a></li>
                </ul>
            </div>

            <!-- Manage Users -->
            <div class="btn-group">
                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-users"></i> Manage Users
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="register.php"><i class="fas fa-user-plus"></i> Create User</a></li>
                    <li><a class="dropdown-item" href="read.php?table=utilisateur"><i class="fas fa-eye"></i> Read Users</a></li>
                    <li><a class="dropdown-item" href="update.php?table=utilisateur"><i class="fas fa-pencil-alt"></i> Update User</a></li>
                    <li><a class="dropdown-item" href="delete.php?table=utilisateur"><i class="fas fa-trash"></i> Delete User</a></li>
                </ul>
            </div>

            <!-- Search Button -->
            <div class="btn-group">
                <a class="btn btn-success" href="search.php">
                    <i class="fas fa-search"></i> Search
                </a>
            </div>

            <!-- Statistics Button -->
            <div class="btn-group">
                <a class="btn btn-info" href="stats.php">
                    <i class="fas fa-chart-bar"></i> Statistics
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
