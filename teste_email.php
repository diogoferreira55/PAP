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

// Buscar os produtos da reserva
$sqlProducts = "SELECT p.item, p.brand, p.model, p.value
                FROM reservation_product rp
                INNER JOIN product p ON rp.idProduct = p.id
                WHERE rp.idReservation = ?";
$stmtProducts = $con->prepare($sqlProducts);
$stmtProducts->bind_param("i", $idReservation);
$stmtProducts->execute();
$productsResult = $stmtProducts->get_result();

$productsHtml = "";
while ($product = $productsResult->fetch_assoc()) {
    $productsHtml .= "<p><strong>Produto:</strong> " . htmlspecialchars($product['item'], ENT_QUOTES, 'UTF-8') . "<br>
                      <strong>Marca:</strong> " . htmlspecialchars($product['brand'], ENT_QUOTES, 'UTF-8') . "<br>
                      <strong>Modelo:</strong> " . htmlspecialchars($product['model'], ENT_QUOTES, 'UTF-8') . "<br>
                      <strong>Valor:</strong> €" . number_format($product['value'], 2, ',', '.') . "</p>";
}

// Configuração do PHPMailer
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->SMTPAuth   = true;
    $mail->Port       = 587;
    $mail->Host       = "mail.kuattrodesign.com";
    $mail->Username   = "noreply@kuattrodesign.com";
    $mail->Password   = "ockPJzX1WRC8";
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

    $mail->setFrom('noreply@kuattrodesign.com', 'estuda.pt');
    $mail->addAddress($reservation['email'], $reservation['companyName']);

    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';  // Definir o charset para UTF-8
    $mail->Subject = 'Confirmação da Reserva #' . $reservation['id'];

    // Corpo do e-mail com detalhes da reserva e produtos
    $mail->Body = "<h2>Detalhes da sua Reserva</h2>
                   <p><strong>ID da Reserva:</strong> " . htmlspecialchars($reservation['id'], ENT_QUOTES, 'UTF-8') . "</p>
                   <p><strong>Cliente:</strong> " . htmlspecialchars($reservation['companyName'], ENT_QUOTES, 'UTF-8') . "</p>
                   <p><strong>Data da Reserva:</strong> " . date("d/m/Y", strtotime($reservation['orderDateStart'])) . "</p>
                   <p><strong>Valor Total:</strong> €" . number_format($reservation['totalValue'], 2, ',', '.') . "</p>
                   <h3>Produtos Reservados</h3>
                   $productsHtml";

    // Envia o e-mail
    $mail->send();
    echo 'E-mail enviado com sucesso!';
    header('location: reservationlist.php');
} catch (Exception $e) {
    echo "Erro ao enviar e-mail: {$mail->ErrorInfo}";
}
