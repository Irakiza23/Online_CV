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

// Handle search query
$search = '';
$results = [];
if (isset($_POST['search'])) {
    $search = $_POST['search'];
    
    // Define a function to execute a search query
    function executeSearchQuery($conn, $query, $params, $types) {
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result();
    }

    // Define search queries for each table
    $queries = [
        "competences" => [
            "query" => "SELECT * FROM competences WHERE competence LIKE ?",
            "params" => ["%$search%"],
            "types" => "s"
        ],
        "experiences" => [
            "query" => "SELECT * FROM experiences WHERE entreprise LIKE ? OR poste LIKE ?",
            "params" => ["%$search%", "%$search%"],
            "types" => "ss"
        ],
        "formations" => [
            "query" => "SELECT * FROM formations WHERE etablissement LIKE ? OR diplome LIKE ?",
            "params" => ["%$search%", "%$search%"],
            "types" => "ss"
        ],
        "langue_parle" => [
            "query" => "SELECT * FROM langue_parle WHERE nom_de_la_langue LIKE ?",
            "params" => ["%$search%"],
            "types" => "s"
        ]
    ];

    // Execute queries and collect results
    foreach ($queries as $table => $queryData) {
        $result = executeSearchQuery($conn, $queryData['query'], $queryData['params'], $queryData['types']);
        while ($row = $result->fetch_assoc()) {
            $row['table'] = $table; // Add table name to the row
            $results[] = $row;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
        .card {
            margin-bottom: 20px;
        }
        .result-item {
            border: 1px solid #dee2e6;
            border-radius: .25rem;
            padding: 10px;
            margin-bottom: 10px;
            background-color: #ffffff;
        }
        .result-item h5 {
            margin-top: 0;
            font-size: 1.2rem;
            font-weight: bold;
        }
        .result-item p {
            margin-bottom: 0;
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

    <div class="container mt-5">
        <h1 class="mb-4">Search CV Information</h1>
        <form method="POST" action="search.php">
            <div class="mb-3">
                <input type="text" name="search" class="form-control" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>

        <?php if (!empty($results)): ?>
            <h2 class="mt-4">Search Results</h2>
            <div class="row">
                <?php
                $groupedResults = [];
                foreach ($results as $row) {
                    $table = $row['table'];
                    if (!isset($groupedResults[$table])) {
                        $groupedResults[$table] = [];
                    }
                    unset($row['table']); // Remove the table name from the result
                    $groupedResults[$table][] = $row;
                }
                ?>
                <?php foreach ($groupedResults as $table => $rows): ?>
                    <div class="col-md-12">
                        <h3 class="mt-4"><?php echo ucfirst($table); ?></h3>
                        <?php foreach ($rows as $row): ?>
                            <div class="result-item">
                                <h5><?php echo ucfirst($table); ?> Entry</h5>
                                <?php foreach ($row as $key => $value): ?>
                                    <p><strong><?php echo htmlspecialchars($key); ?>:</strong> <?php echo htmlspecialchars($value); ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif (isset($_POST['search'])): ?>
            <p class="mt-3">No results found for "<?php echo htmlspecialchars($search); ?>".</p>
        <?php endif; ?>
    </div>
</body>
</html>
