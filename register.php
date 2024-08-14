<?php
session_start();

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

$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize form data
    $nom = $conn->real_escape_string($_POST['nom']);
    $prenom = $conn->real_escape_string($_POST['prenom']);
    $email = $conn->real_escape_string($_POST['email']);
    $mot_de_passe = $conn->real_escape_string($_POST['mot_de_passe']);
    $confirm_password = $conn->real_escape_string($_POST['confirm_password']);
    $country_code = $conn->real_escape_string($_POST['country_code']);
    $numero_de_telephone = $conn->real_escape_string($_POST['numero_de_telephone']);
    $adresse = $conn->real_escape_string($_POST['adresse']);

    // Check if passwords match
    if ($mot_de_passe !== $confirm_password) {
        $error_message = "Les mots de passe ne correspondent pas.";
    } else {
        // Hash the password for security
        $hashed_password = password_hash($mot_de_passe, PASSWORD_DEFAULT);

        // Handle file upload
        $photo = "";
        if (!empty($_FILES['photo']['name'])) {
            $uploads_dir = 'uploads/';
            if (!is_dir($uploads_dir)) {
                mkdir($uploads_dir, 0777, true); // Create the directory if it doesn't exist
            }
            $photo = $uploads_dir . basename($_FILES['photo']['name']);
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $photo)) {
                // File moved successfully
            } else {
                $error_message = "Failed to upload file.";
            }
        }

        // Insert data into the database
        $sql = "INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, photo, numero_de_telephone, adresse) 
                VALUES ('$nom', '$prenom', '$email', '$hashed_password', '$photo', '$country_code $numero_de_telephone', '$adresse')";

        if ($conn->query($sql) === TRUE) {
            $success_message = "Compte créé avec succès.";
        } else {
            $error_message = "Erreur lors de la création du compte : " . $conn->error;
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
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f7f7f7;
            padding-top: 20px;
            margin: 0;
        }
        .register-container {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }
        .register-container h2 {
            margin-bottom: 1.5rem;
            font-weight: bold;
            text-align: center;
            color: #333;
        }
        .login-options {
            margin-top: 1rem;
            text-align: center;
        }
        .login-options a {
            display: block;
            margin-top: 0.5rem;
            font-size: 0.9rem;
            text-decoration: none;
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Create an Account</h2>
        <?php if(!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if(!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form method="post" action="" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="nom" class="form-label">Nom</label>
                <input type="text" class="form-control" id="nom" name="nom" required>
            </div>
            <div class="mb-3">
                <label for="prenom" class="form-label">Prénom</label>
                <input type="text" class="form-control" id="prenom" name="prenom" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="mot_de_passe" class="form-label">Mot de passe</label>
                <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <div class="mb-3">
                <label for="numero_de_telephone" class="form-label">Numéro de téléphone</label>
                <div class="input-group">
                    <select class="form-select" id="country_code" name="country_code" required>
                        <option value="+1">+1 (USA)</option>
                        <option value="+44">+44 (UK)</option>
                        <option value="+33">+33 (France)</option>
                        <option value="+49">+49 (Germany)</option>
                        <option value="+91">+91 (India)</option>
                        <option value="+243">+243 (DR Congo)</option>
                        <!-- Add more countries as needed -->
                    </select>
                    <input type="text" class="form-control" id="numero_de_telephone" name="numero_de_telephone" placeholder="Numéro de téléphone" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="photo" class="form-label">Photo de profil</label>
                <input type="file" class="form-control" id="photo" name="photo">
            </div>
            <div class="mb-3">
                <label for="adresse" class="form-label">Adresse</label>
                <textarea class="form-control" id="adresse" name="adresse"></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100">Enregistrer</button>
        </form>
        <div class="login-options">
            <a href="login.php" class="btn btn-link">Avez-vous déjà un compte? Se connecter</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
