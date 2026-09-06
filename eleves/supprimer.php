<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

require_once "../../config/database.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if ($id) {

    try {

        $stmt = $pdo->prepare(
            "DELETE FROM eleves WHERE id = ?"
        );

        $stmt->execute([$id]);

    } catch (PDOException $e) {

        // Si l'élève possède déjà des paiements,
        // la suppression sera refusée par la base.
        $_SESSION["erreur"] =
            "Impossible de supprimer cet élève car il possède des données associées.";

    }
}

header("Location: index.php");
exit;