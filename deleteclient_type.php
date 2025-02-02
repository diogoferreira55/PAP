<?php

include "db.config.php";

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "DELETE FROM client_type WHERE id = ?";
    $stmt = $con->prepare($sql);
    
    if ($stmt === false) {
        die('Erro na preparação da consulta: ' . $con->error);
    }

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header('Location: client_typelist.php');
        exit;
    } else {
        echo "Erro ao excluir o tipo de cliente: " . $stmt->error;
    }

    $stmt->close();
} else {
    header('Location: client_typelist.php');
    exit;
}
?>
