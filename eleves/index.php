<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

require_once "../../config/database.php";

$sql = "SELECT 
            e.*,
            c.libelle AS classe
        FROM eleves e
        INNER JOIN classes c ON c.id = e.classe_id
        ORDER BY e.id DESC";

$stmt = $pdo->query($sql);
$eleves = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <title>Gestion des élèves</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body>

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2>Gestion des élèves</h2>

        <div>
            <a href="../dashboard.php" class="btn btn-secondary">
                Tableau de bord
            </a>

            <a href="ajouter.php" class="btn btn-primary">
                + Ajouter un élève
            </a>
        </div>

    </div>

    <div class="card shadow">

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-dark">

                        <tr>
                            <th>Photo</th>
                            <th>NIS</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Sexe</th>
                            <th>Classe</th>
                            <th>Téléphone parent</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php foreach ($eleves as $eleve): ?>

                        <tr>

                            <td>
                                <?php if (!empty($eleve["photo"])): ?>

                                    <img
                                        src="uploads/<?= htmlspecialchars($eleve["photo"]) ?>"
                                        width="50"
                                        height="50"
                                        style="object-fit: cover;"
                                        class="rounded-circle"
                                    >

                                <?php else: ?>

                                    Aucune

                                <?php endif; ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($eleve["nis"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($eleve["nom"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($eleve["prenom"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($eleve["sexe"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($eleve["classe"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($eleve["telephone_parent"]) ?>
                            </td>

                            <td>

                                <?php if ($eleve["statut"] === "actif"): ?>

                                    <span class="badge bg-success">
                                        Actif
                                    </span>

                                <?php elseif ($eleve["statut"] === "suspendu"): ?>

                                    <span class="badge bg-danger">
                                        Suspendu
                                    </span>

                                <?php else: ?>

                                    <span class="badge bg-secondary">
                                        Transféré
                                    </span>

                                <?php endif; ?>

                            </td>

                            <td>

                                <a
                                    href="modifier.php?id=<?= $eleve["id"] ?>"
                                    class="btn btn-warning btn-sm"
                                >
                                    Modifier
                                </a>

                                <a
                                    href="supprimer.php?id=<?= $eleve["id"] ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Voulez-vous vraiment supprimer cet élève ?');"
                                >
                                    Supprimer
                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

</body>
</html>