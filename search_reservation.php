<?php
include "db.config.php";

$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : "";

$sqlReservation = "SELECT r.*, rps.paymentStatus, rs.status, c.companyName FROM 
                reservation r
                INNER JOIN 
                reservation_paymentstatus rps ON r.idPaymentStatus = rps.id
                INNER JOIN
                reservation_status rs ON r.idStatus = rs.id
                INNER JOIN 
                client c ON r.idClient = c.id
                WHERE companyName LIKE ? OR paymentStatus LIKE ? OR status LIKE ? OR  orderDateStart LIKE ? OR  orderDateEnd LIKE ? OR totalValue LIKE ? 
                LIMIT 20";
$stmt = $con->prepare($sqlReservation);
$searchWildcard = "%" . $searchTerm . "%";
$stmt->bind_param("ssssss", $searchWildcard, $searchWildcard, $searchWildcard, $searchWildcard, $searchWildcard, $searchWildcard);
$stmt->execute();
$result = $stmt->get_result();

$reservation = [];
while ($row = $result->fetch_assoc()) {
    // Formatando as datas
    $orderDateStart = date("d/m/Y H:i:s", strtotime($row['orderDateStart']));
    $orderDateEnd = date("d/m/Y H:i:s", strtotime($row['orderDateEnd']));

    // Adicionando os dados formatados ao array
    $reservation[] = [
        'reservation_id' => $row['id'],
        'companyName' => $row['companyName'],
        'status' => $row['status'],
        'orderDateStart' => $orderDateStart,
        'orderDateEnd' => $orderDateEnd,
        'paymentStatus' => $row['paymentStatus'],
        'totalValue' => $row['totalValue'],
    ];
}

header('Content-Type: application/json');
echo json_encode($reservation);
