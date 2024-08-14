<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cv";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user details (assuming you're displaying information for a single user with id=1)
$user_id = 4;

$sql_user = "SELECT nom, prenom, email, photo, Numero_de_telephone, adresse FROM utilisateur WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_result = $stmt_user->get_result()->fetch_assoc();

// Fetch competences
$sql_competences = "SELECT competence, niveau FROM competences WHERE utilisateur_id = ?";
$stmt_competences = $conn->prepare($sql_competences);
$stmt_competences->bind_param("i", $user_id);
$stmt_competences->execute();
$competences_result = $stmt_competences->get_result();

// Fetch experiences
$sql_experiences = "SELECT entreprise, poste, date_debut, date_fin, description FROM experiences WHERE utilisateur_id = ?";
$stmt_experiences = $conn->prepare($sql_experiences);
$stmt_experiences->bind_param("i", $user_id);
$stmt_experiences->execute();
$experiences_result = $stmt_experiences->get_result();

// Fetch formations
$sql_formations = "SELECT etablissement, diplome, date_debut, date_fin, description FROM formations WHERE utilisateur_id = ?";
$stmt_formations = $conn->prepare($sql_formations);
$stmt_formations->bind_param("i", $user_id);
$stmt_formations->execute();
$formations_result = $stmt_formations->get_result();

// Fetch languages
$sql_langues = "SELECT nom_de_la_langue, niveau FROM langue_parle WHERE utilisateur_id = ?";
$stmt_langues = $conn->prepare($sql_langues);
$stmt_langues->bind_param("i", $user_id);
$stmt_langues->execute();
$langues_result = $stmt_langues->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon CV</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1><?php echo $user_result['prenom'] . " " . $user_result['nom']; ?></h1>
    <img src="<?php echo $user_result['photo']; ?>" alt="Profile Picture" class="img-thumbnail" style="width: 150px;">
    <p>Email: <?php echo $user_result['email']; ?></p>
    <p>Téléphone: <?php echo $user_result['Numero_de_telephone']; ?></p>
    <p>Adresse: <?php echo $user_result['adresse']; ?></p>

    <hr>
    <h2>Compétences</h2>
    <ul>
        <?php while ($row = $competences_result->fetch_assoc()): ?>
            <li><?php echo $row['competence'] . " - Niveau: " . $row['niveau']; ?></li>
        <?php endwhile; ?>
    </ul>

    <hr>
    <h2>Expériences</h2>
    <ul>
        <?php while ($row = $experiences_result->fetch_assoc()): ?>
            <li><?php echo $row['poste'] . " à " . $row['entreprise']; ?> (<?php echo $row['date_debut'] . " - " . $row['date_fin']; ?>)</li>
            <p><?php echo $row['description']; ?></p>
        <?php endwhile; ?>
    </ul>

    <hr>
    <h2>Formations</h2>
    <ul>
        <?php while ($row = $formations_result->fetch_assoc()): ?>
            <li><?php echo $row['diplome'] . " à " . $row['etablissement']; ?> (<?php echo $row['date_debut'] . " - " . $row['date_fin']; ?>)</li>
            <p><?php echo $row['description']; ?></p>
        <?php endwhile; ?>
    </ul>

    <hr>
    <h2>Langues Parlées</h2>
    <ul>
        <?php while ($row = $langues_result->fetch_assoc()): ?>
            <li><?php echo $row['nom_de_la_langue'] . " - Niveau: " . $row['niveau']; ?></li>
        <?php endwhile; ?>
    </ul>
</div>
</body>
</html>
