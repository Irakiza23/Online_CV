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

// Handle form submission for update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = $_POST['id'];
    $update_fields = array_diff($fields, ['id']);
    $set_clause = implode(", ", array_map(fn($field) => "$field = ?", $update_fields));
    $sql = "UPDATE $table SET $set_clause WHERE id = ?";
    $stmt = $conn->prepare($sql);

    $values = array_map(fn($field) => $_POST[$field], $update_fields);
    $values[] = $id;

    $types = str_repeat("s", count($values) - 1) . "i";
    $stmt->bind_param($types, ...$values);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Record updated successfully.</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }

    $stmt->close();
    header("Location: update.php?table=" . urlencode($table));
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Records</title>
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
        <h1>Update Records for <?php echo ucfirst($table); ?></h1>
        
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
                                    <button class="btn btn-warning btn-sm" onclick="showUpdateForm(<?php echo htmlspecialchars($row['id']); ?>)">Update</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="<?php echo count($fields) + 1; ?>">No records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Update Form -->
        <div id="form-container" class="form-container" style="display: none;">
            <form id="update-form" method="post" action="">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="update-id">
                <?php foreach ($fields as $field): ?>
                    <?php if ($field !== 'id'): ?>
                        <div class="mb-3">
                            <label for="<?php echo $field; ?>" class="form-label"><?php echo ucfirst(str_replace('_', ' ', $field)); ?></label>
                            <input type="<?php echo ($field == 'date_debut' || $field == 'date_fin') ? 'date' : 'text'; ?>" 
                                   id="<?php echo $field; ?>" 
                                   name="<?php echo $field; ?>" 
                                   class="form-control" 
                                   required>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>

        <script>
            function showUpdateForm(id) {
                document.getElementById('update-id').value = id;

                // Fetch record details for update
                fetch(`get_record.php?table=<?php echo urlencode($table); ?>&id=${id}`)
                    .then(response => response.json())
                    .then(data => {
                        <?php foreach ($fields as $field): ?>
                            if (field !== 'id') {
                                document.getElementById('<?php echo $field; ?>').value = data['<?php echo $field; ?>'];
                            }
                        <?php endforeach; ?>
                        document.getElementById('form-container').style.display = 'block';
                    });
            }
        </script>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
