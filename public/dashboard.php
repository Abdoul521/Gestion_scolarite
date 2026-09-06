<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$nom = $_SESSION["nom"];
$prenom = $_SESSION["prenom"];
$role = $_SESSION["role"];

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <title>Tableau de bord</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body>

<nav class="navbar navbar-dark bg-primary">

    <div class="container-fluid">

        <span class="navbar-brand">
            Gestion Scolarité
        </span>

        <span class="text-white">
            <?= htmlspecialchars($prenom . " " . $nom) ?>
            -
            <?= htmlspecialchars($role) ?>

            <a
                href="logout.php"
                class="btn btn-light btn-sm ms-3"
            >
                Déconnexion
            </a>
        </span>

    </div>

</nav>

<div class="container mt-4">

    <h2>
        Tableau de bord
    </h2>

    <p>
        Bienvenue
        <strong>
            <?= htmlspecialchars($prenom . " " . $nom) ?>
        </strong>
    </p>

    <div class="row mt-4">

        <div class="col-md-4 mb-3">
            <div class="card shadow">
                <div class="card-body">
                    <h5>Élèves</h5>
                    <a href="#" class="btn btn-primary">
                        Gérer les élèves
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card shadow">
                <div class="card-body">
                    <h5>Paiements</h5>
                    <a href="#" class="btn btn-success">
                        Gérer les paiements
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card shadow">
                <div class="card-body">
                    <h5>Contrôle NIS</h5>
                    <a href="#" class="btn btn-warning">
                        Contrôler une carte
                    </a>
                </div>
            </div>
        </div>

    </div>

</div>

</body>
</html>