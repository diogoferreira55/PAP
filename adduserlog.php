<?php

include "db.config.php";


$user_id = $_SESSION['id'];
$username = $_SESSION['name'];

// Registrar o log de acesso
if ($stmt = $con->prepare('INSERT INTO user_logs (idUser) VALUES (?)')) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->close();
}

header('Location: home.php');
exit;
