<?php

include "db.config.php";

$estouEm = 4;

include "permission.php";

$idUser = $_SESSION['id'];

$canView = hasPermission($idUser, 4, 'view', $con);

if ($canView == 0) {
    header("Location: no_permission.php");
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Reserva não especificada.";
    exit;
}

$id = intval($_GET['id']);

$sqlClients = "SELECT id, companyName FROM client";
$resultClients = $con->query($sqlClients);

$sqlStatus = "SELECT id, status FROM reservation_status";
$resultStatus = $con->query($sqlStatus);

$sqlPaymentStatus = "SELECT id, paymentStatus FROM reservation_paymentStatus";
$resultPaymentStatus = $con->query($sqlPaymentStatus);

$sqlProducts = "SELECT 
                    p.*, 
                    rp.idProduct 
                FROM 
                    reservation_product rp
                INNER JOIN 
                    product p ON rp.idProduct = p.id
                WHERE 
                    rp.idReservation = ?";
$stmtProducts = $con->prepare($sqlProducts);
$stmtProducts->bind_param("i", $id);
$stmtProducts->execute();
$resultProducts = $stmtProducts->get_result();

$products = [];
if ($resultProducts->num_rows > 0) {
    while ($row = $resultProducts->fetch_assoc()) {
        $products[] = $row;
    }
}

$sql = "SELECT 
            r.id AS reservation_id,
            r.idClient,
            r.idPaymentStatus,
            r.idStatus,
            rps.paymentStatus,
            rs.status,
            r.orderDateStart,
            r.orderDateEnd,
            r.discountTotalValue,
            r.totalValue,
            c.companyName
        FROM 
            reservation r
        INNER JOIN 
            reservation_paymentstatus rps on r.idPaymentStatus = rps.id
        INNER JOIN
            reservation_status rs on r.idStatus = rs.id
        INNER JOIN 
            client c on r.idClient=c.id

        WHERE 
            r.id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $reservation = $result->fetch_assoc();
} else {
    echo "Reserva não encontrada.";
    exit;
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
    </style>
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
                        <h4>Detalhes da Reserva</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12 col-sm-12">
                        <div class="card">
                            <div class="card-body">
                                <!-- <div class="bar-code-view">
                                    <img src="assets/img/barcode1.png" alt="barcode">
                                    <a class="printimg">
                                        <img src="assets/img/icons/printer.svg" alt="print">
                                    </a>
                                </div> -->
                                <div class="productdetails">
                                    <ul class="product-bar">
                                        <li>
                                            <h4>Client</h4>
                                            <h6><?php echo htmlspecialchars($reservation['companyName']); ?></h6>
                                        </li>
                                        <li>
                                            <h4>Data Inicial</h4>
                                            <h6><?php echo htmlspecialchars($reservation['orderDateStart']); ?></h6>
                                        </li>
                                        <li>
                                            <h4>Data Final</h4>
                                            <h6><?php echo htmlspecialchars($reservation['orderDateEnd']); ?></h6>
                                        </li>
                                        <li>
                                            <h4>Estado</h4>
                                            <h6><?php echo htmlspecialchars($reservation['status']); ?></h6>
                                        </li>
                                        <li>
                                            <h4>Estado de Pagamento</h4>
                                            <h6><?php echo htmlspecialchars($reservation['paymentStatus']); ?></h6>
                                        </li>
                                        <li>
                                            <h4>Desconto</h4>
                                            <h6><?php echo htmlspecialchars($reservation['discountTotalValue']); ?></h6>
                                        </li>
                                        <li>
                                            <h4>Valor Total</h4>
                                            <h6><?php echo htmlspecialchars($reservation['totalValue']); ?></h6>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="page-header">
                    <div class="page-title">
                        <h4>Produtos da Reserva</h4>
                    </div>
                </div>
                <div class="col-lg-12 col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <?php if (!empty($products)) { ?>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Produto</th>
                                                <th>Marca</th>
                                                <th>Modelo</th>
                                                <th>Desconto</th>
                                                <th>Valor Base</th>
                                                <th>Valor com desconto</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($products as $product) { ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($product['idProduct']); ?></td>
                                                    <td><?php echo htmlspecialchars($product['item']); ?></td>
                                                    <td><?php echo htmlspecialchars($product['brand']); ?></td>
                                                    <td><?php echo htmlspecialchars($product['model']); ?></td>
                                                    <td><?php echo number_format($product['discount'], 2, ',', '.'); ?></td>
                                                    <td><?php echo number_format($product['value'], 2, ',', '.'); ?></td>
                                                    <td><?php echo number_format($product['discounted_value'], 2, ',', '.'); ?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                <?php } else { ?>
                                    <p>Não há produtos associados a esta reserva.</p>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="button-container">
                            <a href="reservationlist.php" class="btn btn-cancel">Voltar</a>
                            <a href="editreservation.php?id=<?php echo $id; ?>" class="btn btn-submit">Editar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="assets/js/jquery-3.6.0.min.js"></script>
        <script src="assets/js/feather.min.js"></script>
        <script src="assets/js/jquery.slimscroll.min.js"></script>
        <script src="assets/js/bootstrap.bundle.min.js"></script>
        <script src="assets/plugins/owlcarousel/owl.carousel.min.js"></script>
        <script src="assets/plugins/select2/js/select2.min.js"></script>
        <script src="assets/js/script.js"></script>

</body>

</html>