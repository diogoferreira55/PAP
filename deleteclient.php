<?php

include "db.config.php";

include "permission.php";

$idUser = $_SESSION['id'];

$canDelete = hasPermission($idUser, 1, 'delete', $con);

if ($canDelete == 0) {
    header("Location: no_permission.php");
    exit;
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    // Primeiramente, eliminar o endereço associado ao cliente
    $sqlAddress = "DELETE FROM client_address WHERE idClient = ?";
    $stmtAddress = $con->prepare($sqlAddress);

    if ($stmtAddress === false) {
        die('Erro na preparação da consulta de endereço: ' . $con->error);
    }

    $stmtAddress->bind_param("i", $id);

    // Execute a remoção do endereço
    if (!$stmtAddress->execute()) {
        echo "Erro ao excluir o endereço: " . $stmtAddress->error;
        $stmtAddress->close();
        exit;
    }

    $stmtAddress->close();

    // Agora, excluir o cliente
    $sql = "DELETE FROM client WHERE id = ?";
    $stmt = $con->prepare($sql);

    if ($stmt === false) {
        die('Erro na preparação da consulta de cliente: ' . $con->error);
    }

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header('Location: clientlist.php');
        exit;
    } else {
        echo "Erro ao excluir o cliente: " . $stmt->error;
    }

    $stmt->close();
} else {
    header('Location: clientlist.php');
    exit;
}
