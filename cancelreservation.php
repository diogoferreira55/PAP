<?php
include "db.config.php";

if (isset($_GET['id'])) {
    $reservationId = intval($_GET['id']);

    $sql = "UPDATE reservation SET idStatus = 3 WHERE id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $reservationId);

    if ($stmt->execute()) {
        header('Location: reservationlist.php');
        exit;
    } else {
        echo "Erro ao excluir o tipo de cliente: " . $stmt->error;
    }

    $stmt->close();
} else {
    header('Location: reservationlist.php');
    exit;
}
?>