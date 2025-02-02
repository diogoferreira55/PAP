<?php
include "db.config.php";

include "permission.php";

$idReservation = intval($_GET['id'] ?? 0);
$idUser = $_SESSION['id'];
$canEdit = hasPermission($idUser, 4, 'update', $con);

if ($canEdit == 0) {
    header("Location: no_permission.php");
    exit;
}



$estouEm = 4;
$table_name = "reservation";

$msgOp = "";

if (!$idReservation) {
    die("ID de reserva inválido!");
}

// Consultas
$sqlClients = "SELECT id, companyName FROM client";
$resultClients = $con->query($sqlClients);

$sqlStatus = "SELECT id, status FROM reservation_status";
$resultStatus = $con->query($sqlStatus);

$sqlPaymentStatus = "SELECT id, paymentStatus FROM reservation_paymentStatus";
$resultPaymentStatus = $con->query($sqlPaymentStatus);

$sqlReservation = "SELECT r.id, c.companyName, r.orderDateStart, r.orderDateEnd, r.idClient, r.idStatus, r.idPaymentStatus,r.discountMultDays, r.discountTotalValue
                   FROM reservation r
                   INNER JOIN client c ON r.idClient = c.id
                   WHERE r.id = $idReservation";
$result = $con->query($sqlReservation);
$reservation = $result && $result->num_rows > 0 ? $result->fetch_assoc() : null;

if (!$reservation) {
    die("Reserva não encontrada!");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idClient = intval($_POST['idClient']);
    $idPaymentStatus = intval($_POST['idPaymentStatus']);
    $idStatus = intval($_POST['idStatus']);
    $orderDateStart = $con->real_escape_string($_POST['orderDateStart']);
    $orderDateEnd = $con->real_escape_string($_POST['orderDateEnd']);
    $discountTotalValue = $_POST['discountTotalValue'];
    $discountMultDays = $_POST['discountMultDays'];

    $sql = "UPDATE $table_name 
            SET idClient = '$idClient', 
                idPaymentStatus = '$idPaymentStatus', 
                idStatus = '$idStatus', 
                orderDateStart = '$orderDateStart', 
                orderDateEnd = '$orderDateEnd',
                discountTotalValue = '$discountTotalValue',
                discountMultDays = '$discountMultDays'
            WHERE id = $idReservation";

    if ($con->query($sql) === TRUE) {
        header("Location: editreservation_product.php?idReservation=$idReservation");
        exit();
    } else {
        echo "Erro ao atualizar a reserva: " . $con->error;
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <?php include "./rel.header.php"; ?>
    <style>
        .button-container {
            display: flex;
            justify-content: flex-start;
            gap: 10px;
        }

        .btn-submit,
        .btn-cancel {
            width: 150px;
            height: 50px;
            text-align: center;
            font-size: 16px;
            border-radius: 5px;
            border: none;
        }

        .product-row {
            display: flex;
            justify-content: space-between;
            padding: 10px;
        }

        .product-row input {
            width: 100px;
        }

        .product-row .product-name {
            flex-grow: 1;
        }
    </style>
</head>

<body>
    <div id="global-loader">
        <div class="whirly-loader"> </div>
    </div>

    <div class="main-wrapper">

        <?php include "./menu.header.php"; ?>

        <?php include "./menu.lateral.php"; ?>

        <div class="page-wrapper">
            <div class="content">
                <div class="page-header">
                    <div class="page-title">
                        <h4>Editar Reserva</h4>
                    </div>
                </div>
                <form action="" method="POST">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Nome Empresa Cliente</label>
                                        <select name="idClient" class="select" required>
                                            <option value="">Selecione um Cliente</option>
                                            <?php while ($row = $resultClients->fetch_assoc()) { ?>
                                                <option value="<?= $row['id'] ?>" <?= ($row['id'] == $reservation['idClient']) ? 'selected' : '' ?>>
                                                    <?= $row['companyName'] ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Data Inicial</label>
                                        <input type="datetime-local" name="orderDateStart" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($reservation['orderDateStart'])) ?>">
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Data Final</label>
                                        <input type="datetime-local" name="orderDateEnd" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($reservation['orderDateEnd'])) ?>">
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Desconto por Múltiplos Dias</label>
                                        <input type="number" name="discountMultDays" class="form-control" value="<?= $reservation['discountMultDays'] ?>">
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Valor Total do Desconto</label>
                                        <input type="number" name="discountTotalValue" class="form-control" value="<?= $reservation['discountTotalValue'] ?>">
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Estado</label>
                                        <select name="idStatus" class="select" required>
                                            <?php while ($status = $resultStatus->fetch_assoc()) { ?>
                                                <option value="<?= $status['id'] ?>" <?= ($status['id'] == $reservation['idStatus']) ? 'selected' : '' ?>>
                                                    <?= $status['status'] ?>
                                                </option>
                                            <?php } ?>
                                        </select>

                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Estado do pagamento</label>
                                        <select name="idPaymentStatus" class="select" required>
                                            <?php while ($paymentStatus = $resultPaymentStatus->fetch_assoc()) { ?>
                                                <option value="<?= $paymentStatus['id'] ?>" <?= ($paymentStatus['id'] == $reservation['idPaymentStatus']) ? 'selected' : '' ?>>
                                                    <?= $paymentStatus['paymentStatus'] ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="button-container">
                            <a href="reservationlist.php" class="btn btn-cancel">Cancelar</a>
                            <button class="btn btn-submit" type="submit">Editar Produtos</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="assets/js/jquery-3.6.0.min.js"></script>
    <script src="assets/js/feather.min.js"></script>
    <script src="assets/js/jquery.slimscroll.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/plugins/select2/js/select2.min.js"></script>
    <script src="assets/plugins/sweetalert/sweetalert2.all.min.js"></script>
    <script src="assets/plugins/sweetalert/sweetalerts.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>

</html>