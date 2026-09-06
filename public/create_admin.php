 <?php

require_once "./config/database.php";

$nom = "Admin";
$prenom = "Principal";
$email = "admin@ecole.com";
$mot_de_passe = password_hash("Admin123@", PASSWORD_DEFAULT);
$role = "Administrateur";

$sql = "INSERT INTO users
        (nom, prenom, email, mot_de_passe, role)
        VALUES
        (:nom, :prenom, :email, :mot_de_passe, :role)";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    "nom" => $nom,
    "prenom" => $prenom,
    "email" => $email,
    "mot_de_passe" => $mot_de_passe,
    "role" => $role
]);

echo "Administrateur créé avec succès.";