<?php
include "db.config.php";
$estouEm = 9;

$idUser = $_SESSION['id'];

include "permission.php";

// Verifica permissão de visualização
$canView = hasPermission($idUser, 1, 'view', $con);

if ($canView == 0) {
    header("Location: no_permission.php");
    exit;
}

// Verifica se o ID do cliente foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: clientlist.php");
    exit;
}

$idClient = intval($_GET['id']);

// Busca informações das reservas do cliente
$sql = "SELECT 
            r.id AS reservation_id,
            r.idClient,
            r.idPaymentStatus,
            r.idStatus,
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
        WHERE 
            r.idClient = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $idClient);
$stmt->execute();
$result = $stmt->get_result();

$reservations = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $reservations[] = $row;
    }
} else {
    echo "Nenhuma reserva encontrada para este cliente.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include "./rel.header.php"; ?>
    <title>Reservas do Cliente</title>
</head>

<body>
    <div id="global-loader">
        <div class="whirly-loader"></div>
    </div>

    <div class="main-wrapper">
        <?php include "./menu.header.php"; ?>
        <?php include "./menu.lateral.php"; ?>

        <div class="page-wrapper">
            <div class="content">
                <div class="page-header">
                    <div class="page-title">
                        <h4>Reservas do Cliente</h4>
                        <h6>Histórico de reservas</h6>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID da Reserva</th>
                                        <th>Cliente</th>
                                        <th>Status</th>
                                        <th>Status de Pagamento</th>
                                        <th>Data Inicio</th>
                                        <th>Data Final</th>
                                        <th>Valor Total</th>
                                        <th>Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reservations as $reservation): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($reservation['reservation_id']) ?></td>
                                            <td><?= htmlspecialchars($reservation['companyName']) ?></td>
                                            <td><?= htmlspecialchars($reservation['status']) ?></td>
                                            <td><?= htmlspecialchars($reservation['paymentStatus']) ?></td>
                                            <td><?= htmlspecialchars($reservation['orderDateStart']) ?></td>
                                            <td><?= htmlspecialchars($reservation['orderDateEnd']) ?></td>
                                            <td><?= htmlspecialchars($reservation['totalValue']) ?></td>
                                            <td>
                                                <a title="Ver Reservas do cliente" href="reservation-details.php?id=<?= htmlspecialchars($reservation['reservation_id']) ?>" class="btn btn-filters ms-auto">
                                                    <img src="/assets/img/icons/eye.svg" alt="Ver">
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/jquery-3.6.0.min.js"></script>
    <script src="assets/js/feather.min.js"></script>
    <script src="assets/js/jquery.slimscroll.min.js"></script>
    <script src="assets/js/jquery.dataTables.min.js"></script>
    <script src="assets/js/dataTables.bootstrap4.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/plugins/select2/js/select2.min.js"></script>
    <script src="assets/js/moment.min.js"></script>
    <script src="assets/js/bootstrap-datetimepicker.min.js"></script>
    <script src="assets/plugins/sweetalert/sweetalert2.all.min.js"></script>
    <script src="assets/plugins/sweetalert/sweetalerts.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>

</html>