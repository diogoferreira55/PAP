<?php
// Inicia a sessão e inclui a configuração do banco de dados
include "db.config.php";

// Verifica as permissões se necessário (opcional, se desejar reutilizar as variáveis de permissão)
// Se já possuir as variáveis de permissão no arquivo principal, pode ser necessário recalculá-las aqui ou removê-las.
include "permission.php";
$idUser = $_SESSION['id'];
$canDelete = hasPermission($idUser, 4, 'delete', $con);
$canEdit   = hasPermission($idUser, 4, 'update', $con);
$canView   = hasPermission($idUser, 4, 'view', $con);
$canAction = $canEdit || $canDelete || $canView;

if (!isset($_GET['date'])) {
    echo "<p>Data não informada.</p>";
    exit;
}

$dateFilter = $_GET['date'];

// Consulta as reservas para a data informada
$sql = "SELECT 
            r.id AS reservation_id,
            r.idClient,
            rps.paymentStatus,
            rs.status,
            r.orderDateStart,
            r.orderDateEnd,
            r.totalValue,
            c.companyName
        FROM 
            reservation r
        INNER JOIN 
            reservation_paymentstatus rps ON r.idPaymentStatus = rps.id
        INNER JOIN
            reservation_status rs ON r.idStatus = rs.id
        INNER JOIN 
            client c ON r.idClient = c.id
        WHERE DATE(r.orderDateStart) = ?";

$stmt = $con->prepare($sql);
$stmt->bind_param("s", $dateFilter);
$stmt->execute();
$result = $stmt->get_result();

$reservations = [];
while ($row = $result->fetch_assoc()) {
    $reservations[] = $row;
}
$stmt->close();

// Monta o HTML para exibir as reservas
if (count($reservations) > 0) {
    echo '<div class="table-responsive">';
    echo '<table class="table table-striped">';
    echo '<thead>
            <tr>
                <th>Cliente</th>
                <th>Estado</th>
                <th>Data Início</th>
                <th>Data Fim</th>
                <th>Pagamento</th>
                <th>Valor</th>';
    if ($canAction) {
        echo '<th>Ações</th>';
    }
    echo '  </tr>
          </thead>
          <tbody>';
    foreach ($reservations as $reservation) {
        echo '<tr>';
        echo '<td>' . $reservation['companyName'] . '</td>';
        echo '<td>' . $reservation['status'] . '</td>';
        echo '<td>' . $reservation['orderDateStart'] . '</td>';
        echo '<td>' . $reservation['orderDateEnd'] . '</td>';
        echo '<td>' . $reservation['paymentStatus'] . '</td>';
        echo '<td>' . number_format($reservation['totalValue'], 2, ',', '.') . '</td>';
        echo '<td>';
        if ($canView) {
            echo '<a title="Visualizar" href="reservation-details.php?id=' . $reservation['reservation_id'] . '" class="btn btn-sm btn-info mr-1">
                    <img src="/assets/img/icons/eye.svg" alt="Visualizar">
                  </a>';
        }
        if ($canEdit) {
            echo '<a title="Editar" href="editreservation.php?id=' . $reservation['reservation_id'] . '" class="btn btn-sm btn-warning mr-1">
                    <img src="/assets/img/icons/edit.svg" alt="Editar">
                  </a>';
        }
        if ($canDelete && $reservation['status'] === 'Cancelado') {
            echo '<a title="Excluir" href="javascript:void(0);" class="btn btn-sm btn-danger" onclick="confirmarExclusao(' . $reservation['reservation_id'] . ')">
                    <img src="/assets/img/icons/delete.svg" alt="Excluir">
                  </a>';
        }
        echo '</td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';
} else {
    echo "<p>Nenhuma reserva encontrada para esta data.</p>";
}
