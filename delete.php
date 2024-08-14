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

// Get table from URL
$table = filter_input(INPUT_GET, 'table', FILTER_SANITIZE_STRING);

if (!$table) {
    die("Table not specified.");
}

// Define fields based on table
$fields = [];
switch ($table) {
    case 'competences':
        $fields = ['id', 'competence', 'niveau'];
        break;
    case 'experiences':
        $fields = ['id', 'entreprise', 'poste', 'date_debut', 'date_fin', 'description'];
        break;
    case 'formations':
        $fields = ['id', 'etablissement', 'diplome', 'date_debut', 'date_fin', 'description'];
        break;
    case 'langue_parle':
        $fields = ['id', 'nom_de_la_langue', 'niveau'];
        break;
    case 'utilisateur':
        $fields = ['id', 'nom', 'prenom', 'email', 'mot_de_passe', 'photo', 'Numero_de_telephone', 'adresse'];
        break;
    default:
        die("Invalid table.");
}

// Fetch all records from the specified table
$sql = "SELECT * FROM $table";
$result = $conn->query($sql);

// Handle form submission for delete
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = $_POST['id'];
    $sql = "DELETE FROM $table WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Record deleted successfully.</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }

    $stmt->close();
    header("Location: delete.php?table=" . urlencode($table));
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Records</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-container {
            margin-top: 20px;
        }
        .table-container {
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1>Delete Records from <?php echo ucfirst($table); ?></h1>
        
        <!-- Display Records Table -->
        <div class="table-container">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <?php foreach ($fields as $field): ?>
                            <th><?php echo ucfirst(str_replace('_', ' ', $field)); ?></th>
                        <?php endforeach; ?>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <?php foreach ($fields as $field): ?>
                                    <td><?php echo htmlspecialchars($row[$field]); ?></td>
                                <?php endforeach; ?>
                                <td>
                                    <button class="btn btn-danger btn-sm" onclick="showDeleteForm(<?php echo htmlspecialchars($row['id']); ?>)">Delete</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="<?php echo count($fields) + 1; ?>">No records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Delete Form -->
        <div id="form-container" class="form-container" style="display: none;">
            <form id="delete-form" method="post" action="">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="delete-id">
                <p>Are you sure you want to delete this record?</p>
                <button type="submit" class="btn btn-danger">Delete</button>
                <button type="button" class="btn btn-secondary" onclick="hideDeleteForm()">Cancel</button>
            </form>
        </div>

        <script>
            function showDeleteForm(id) {
                document.getElementById('delete-id').value = id;
                document.getElementById('form-container').style.display = 'block';
            }
            
            function hideDeleteForm() {
                document.getElementById('form-container').style.display = 'none';
            }
        </script>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
