<?php

include "db.config.php";

include "permission.php";

$idUser = $_SESSION['id'];

$canDelete = hasPermission($idUser, 3, 'delete', $con);

if ($canDelete == 0) {
    header("Location: no_permission.php");
    exit;
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "DELETE FROM product WHERE id = ?";
    $stmt = $con->prepare($sql);

    if ($stmt === false) {
        die('Erro na preparação da consulta: ' . $con->error);
    }

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header('Location: productlist.php');
        exit;
    } else {
        echo "Erro ao excluir o produto: " . $stmt->error;
    }

    $stmt->close();
} else {
    header('Location: productlist.php');
    exit;
}
