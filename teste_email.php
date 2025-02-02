<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
include "db.config.php";

if (!isset($_GET['idReservation']) || !is_numeric($_GET['idReservation'])) {
    die("ID da reserva inválido.");
}

$idReservation = intval($_GET['idReservation']);

// Buscar os detalhes da reserva de forma segura
$sqlReservation = "SELECT r.id, c.companyName, c.email, r.orderDateStart, r.totalValue
                   FROM reservation r
                   INNER JOIN client c ON r.idclient = c.id
                   WHERE r.id = ?";
$stmt = $con->prepare($sqlReservation);
$stmt->bind_param("i", $idReservation);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $reservation = $result->fetch_assoc();
} else {
    die("Erro ao buscar os detalhes da reserva.");
}

// Configuração do PHPMailer
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = getenv('diogohenriqueferreira5@gmail.com');
    $mail->Password = getenv('Diogo_ferreira5?');
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Configuração do e-mail
    $mail->setFrom('diogohenriqueferreira5@gmail.com', 'Zoom Out');
    $mail->addAddress($reservation['email'], $reservation['companyName']);

    $mail->isHTML(true);
    $mail->Subject = 'Confirmação da Reserva #' . $reservation['id'];

    // Corpo do e-mail
    $mail->Body = "<h2>Detalhes da sua Reserva</h2>
                   <p><strong>ID da Reserva:</strong> " . htmlspecialchars($reservation['id']) . "</p>
                   <p><strong>Cliente:</strong> " . htmlspecialchars($reservation['companyName']) . "</p>
                   <p><strong>Data da Reserva:</strong> " . date("d/m/Y", strtotime($reservation['orderDateStart'])) . "</p>
                   <p><strong>Valor Total:</strong> €" . number_format($reservation['totalValue'], 2, ',', '.') . "</p>";

    $mail->send();
    echo 'E-mail enviado com sucesso!';
    header('location: reservationlist.php');
} catch (Exception $e) {
    echo "Erro ao enviar e-mail: {$mail->ErrorInfo}";
}