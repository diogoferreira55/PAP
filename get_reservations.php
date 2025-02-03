<?php
header('Content-Type: application/json');
include "db.config.php";

$sql = "SELECT 
            r.id AS reservation_id, 
            c.companyName AS cliente, 
            r.orderDateStart AS data_inicio, 
            r.orderDateEnd AS data_fim
        FROM reservation r
        INNER JOIN client c ON r.idClient = c.id";

$result = $con->query($sql);
$events = [];

function gerarCorAleatoria() {
    return sprintf("#%06X", mt_rand(0, 0xFFFFFF));
}

while ($row = $result->fetch_assoc()) {
    $events[] = [
        'id' => $row['reservation_id'],
        'title' => "Reserva: " . $row['cliente'],
        'start' => $row['data_inicio'],
        'end' => $row['data_fim'],
        'color' => gerarCorAleatoria()
    ];
}

$con->close();
echo json_encode($events);
?>
