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

$table = $_GET['table'] ?? null;
$fields = [];

if ($table) {
    switch ($table) {
        case 'competences':
            $fields = ['competence', 'niveau'];
            break;
        case 'experiences':
            $fields = ['entreprise', 'poste', 'date_debut', 'date_fin', 'description'];
            break;
        case 'formations':
            $fields = ['etablissement', 'diplome', 'date_debut', 'date_fin', 'description'];
            break;
        case 'langue_parle':
            $fields = ['nom_de_la_langue', 'niveau'];
            break;
        case 'utilisateur':
            // This is handled by register.php
            header("Location: register.php");
            exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $utilisateur_id = $_SESSION['user_id'];
    $values = [];

    foreach ($fields as $field) {
        if (strpos($field, 'date') !== false) {
            // Verify and format the date fields
            $date = DateTime::createFromFormat('Y-m-d', $_POST[$field]);
            if (!$date) {
                echo "<div class='alert alert-danger'>Invalid date format for $field. Please use YYYY-MM-DD.</div>";
                $conn->close();
                exit;
            }
            $values[] = $date->format('Y-m-d');
        } else {
            $values[] = $_POST[$field];
        }
    }

    array_unshift($values, $utilisateur_id);

    $placeholders = implode(", ", array_fill(0, count($fields), "?"));
    $sql = "INSERT INTO $table (utilisateur_id, " . implode(", ", $fields) . ") VALUES (?, $placeholders)";
    $stmt = $conn->prepare($sql);

    $types = str_repeat("s", count($values));
    $stmt->bind_param($types, ...$values);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Record added successfully to $table.</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Record</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Create Record in <?php echo ucfirst($table); ?></h1>
        <form method="post" action="">
            <?php foreach ($fields as $field): ?>
                <div class="mb-3">
                    <label for="<?php echo $field; ?>" class="form-label"><?php echo ucfirst(str_replace('_', ' ', $field)); ?></label>
                    <?php if (strpos($field, 'date') !== false): ?>
                        <input type="date" id="<?php echo $field; ?>" name="<?php echo $field; ?>" class="form-control" required>
                    <?php else: ?>
                        <input type="text" id="<?php echo $field; ?>" name="<?php echo $field; ?>" class="form-control" required>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
