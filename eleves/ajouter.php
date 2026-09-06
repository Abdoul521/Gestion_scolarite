<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

require_once "../../config/database.php";

$message = "";
$erreur = "";

$classes = $pdo->query(
    "SELECT * FROM classes ORDER BY niveau, libelle"
)->fetchAll();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nis = trim($_POST["nis"]);
    $nom = trim($_POST["nom"]);
    $prenom = trim($_POST["prenom"]);
    $date_naissance = $_POST["date_naissance"];
    $sexe = $_POST["sexe"];
    $telephone_parent = trim($_POST["telephone_parent"]);
    $classe_id = intval($_POST["classe_id"]);
    $statut = $_POST["statut"];

    if (
        empty($nis) ||
        empty($nom) ||
        empty($prenom) ||
        empty($date_naissance) ||
        empty($sexe) ||
        empty($classe_id)
    ) {

        $erreur = "Veuillez remplir tous les champs obligatoires.";

    } else {

        // Vérifier si le NIS existe déjà
        $check = $pdo->prepare(
            "SELECT id FROM eleves WHERE nis = ?"
        );

        $check->execute([$nis]);

        if ($check->fetch()) {

            $erreur = "Ce NIS existe déjà.";

        } else {

            // Gestion de la photo
            $nom_photo = null;

            if (
                isset($_FILES["photo"]) &&
                $_FILES["photo"]["error"] === UPLOAD_ERR_OK
            ) {

                $extension = strtolower(
                    pathinfo(
                        $_FILES["photo"]["name"],
                        PATHINFO_EXTENSION
                    )
                );

                $extensions_autorisees = [
                    "jpg",
                    "jpeg",
                    "png",
                    "webp"
                ];

                if (!in_array($extension, $extensions_autorisees)) {

                    $erreur = "Format de photo non autorisé.";

                } else {

                    $nom_photo =
                        uniqid("eleve_", true) .
                        "." .
                        $extension;

                    $destination =
                        "uploads/" . $nom_photo;

                    move_uploaded_file(
                        $_FILES["photo"]["tmp_name"],
                        $destination
                    );
                }
            }

            if (empty($erreur)) {

                $sql = "INSERT INTO eleves
                        (
                            nis,
                            nom,
                            prenom,
                            date_naissance,
                            sexe,
                            telephone_parent,
                            classe_id,
                            photo,
                            statut
                        )
                        VALUES
                        (
                            :nis,
                            :nom,
                            :prenom,
                            :date_naissance,
                            :sexe,
                            :telephone_parent,
                            :classe_id,
                            :photo,
                            :statut
                        )";

                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    "nis" => $nis,
                    "nom" => $nom,
                    "prenom" => $prenom,
                    "date_naissance" => $date_naissance,
                    "sexe" => $sexe,
                    "telephone_parent" => $telephone_parent,
                    "classe_id" => $classe_id,
                    "photo" => $nom_photo,
                    "statut" => $statut
                ]);

                header("Location: index.php");
                exit;
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <title>Ajouter un élève</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body>

<div class="container mt-4">

    <h2>Ajouter un élève</h2>

    <?php if ($erreur): ?>

        <div class="alert alert-danger">
            <?= htmlspecialchars($erreur) ?>
        </div>

    <?php endif; ?>

    <div class="card shadow">

        <div class="card-body">

            <form method="POST" enctype="multipart/form-data">

                <div class="row">

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            NIS *
                        </label>

                        <input
                            type="text"
                            name="nis"
                            class="form-control"
                            required
                        >

                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Nom *
                        </label>

                        <input
                            type="text"
                            name="nom"
                            class="form-control"
                            required
                        >

                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Prénom *
                        </label>

                        <input
                            type="text"
                            name="prenom"
                            class="form-control"
                            required
                        >

                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Date de naissance *
                        </label>

                        <input
                            type="date"
                            name="date_naissance"
                            class="form-control"
                            required
                        >

                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Sexe *
                        </label>

                        <select name="sexe" class="form-select" required>

                            <option value="">
                                Sélectionner
                            </option>

                            <option value="M">
                                Masculin
                            </option>

                            <option value="F">
                                Féminin
                            </option>

                        </select>

                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Téléphone du parent
                        </label>

                        <input
                            type="text"
                            name="telephone_parent"
                            class="form-control"
                        >

                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Classe *
                        </label>

                        <select
                            name="classe_id"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Sélectionner une classe
                            </option>

                            <?php foreach ($classes as $classe): ?>

                                <option value="<?= $classe["id"] ?>">

                                    <?= htmlspecialchars($classe["libelle"]) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Statut
                        </label>

                        <select name="statut" class="form-select">

                            <option value="actif">
                                Actif
                            </option>

                            <option value="suspendu">
                                Suspendu
                            </option>

                            <option value="transfere">
                                Transféré
                            </option>

                        </select>

                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Photo
                        </label>

                        <input
                            type="file"
                            name="photo"
                            class="form-control"
                            accept=".jpg,.jpeg,.png,.webp"
                        >

                    </div>

                </div>

                <button
                    type="submit"
                    class="btn btn-success"
                >
                    Enregistrer
                </button>

                <a
                    href="index.php"
                    class="btn btn-secondary"
                >
                    Annuler
                </a>

            </form>

        </div>

    </div>

</div>

</body>
</html>