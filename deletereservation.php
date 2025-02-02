<?php

include "db.config.php";
include "permission.php";

$idUser = $_SESSION['id'];

$canDelete = hasPermission($idUser, 4, 'delete', $con);

if ($canDelete == 0) {
    header("Location: no_permission.php");
    exit;
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    // Iniciar transação para garantir que ambas as exclusões sejam feitas corretamente
    $con->begin_transaction();

    try {
        // Deletar produtos relacionados à reserva
        $sqlDeleteProducts = "DELETE FROM reservation_product WHERE idReservation = ?";
        $stmtProducts = $con->prepare($sqlDeleteProducts);
        if ($stmtProducts === false) {
            throw new Exception('Erro na preparação da consulta de produtos: ' . $con->error);
        }
        $stmtProducts->bind_param("i", $id);
        if (!$stmtProducts->execute()) {
            throw new Exception("Erro ao excluir os produtos: " . $stmtProducts->error);
        }
        $stmtProducts->close();

        // Deletar a reserva
        $sqlDeleteReservation = "DELETE FROM reservation WHERE id = ?";
        $stmtReservation = $con->prepare($sqlDeleteReservation);
        if ($stmtReservation === false) {
            throw new Exception('Erro na preparação da consulta de reserva: ' . $con->error);
        }
        $stmtReservation->bind_param("i", $id);
        if (!$stmtReservation->execute()) {
            throw new Exception("Erro ao excluir a reserva: " . $stmtReservation->error);
        }
        $stmtReservation->close();

        // Commit da transação
        $con->commit();

        header('Location: reservationlist.php');
        exit;
    } catch (Exception $e) {
        $con->rollback();
        echo "Erro: " . $e->getMessage();
    }
} else {
    header('Location: reservationlist.php');
    exit;
}
