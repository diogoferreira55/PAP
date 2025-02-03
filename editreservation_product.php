<?php

include "db.config.php";


include "permission.php";

$id = intval($_GET['idReservation']);
$idUser = $_SESSION['id'];
$canEdit = hasPermission($idUser, 4, 'update', $con);

if ($canEdit == 0) {
    header("Location: no_permission.php");
    exit;
}


$estouEm = 4;


// Carregar os produtos da reserva
$sqlProducts = "SELECT rp.idReservation, rp.idProduct, p.item, p.model, p.brand, p.discounted_value
                FROM reservation_product rp
                INNER JOIN product p ON rp.idProduct = p.id
                WHERE rp.idReservation = ?";
$stmt = $con->prepare($sqlProducts);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultProducts = $stmt->get_result();

$reservedProducts = [];
while ($row = $resultProducts->fetch_assoc()) {
    $reservedProducts[] = $row['idProduct'];
}

// Carregar os dados da reserva (sem editar nada além dos produtos)
$sqlReservation = "SELECT 
            r.id AS reservation_id,
            r.idClient,
            r.idPaymentStatus,
            r.idStatus,
            r.orderDateStart,
            r.orderDateEnd,
            r.discountTotalValue,
            r.discountMultDays,
            r.totalValue
        FROM reservation r
        WHERE r.id = ?";
$stmtReservation = $con->prepare($sqlReservation);
$stmtReservation->bind_param("i", $id);
$stmtReservation->execute();
$resultReservation = $stmtReservation->get_result();

$reservation = null;
if ($resultReservation && $resultReservation->num_rows > 0) {
    $reservation = $resultReservation->fetch_assoc();
} else {
    $reservation = null;
    $msgOp = "ERROR";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obter os produtos selecionados
    $idProduct = $_POST['idProduct'] ?? [];
    $totalValue = 0;
    $discountMultDays = 0;
    $discountTotalValue = 0;

    $discountMultDays = floatval($reservation['discountMultDays']);
    $discountTotalValue = floatval($reservation['discountTotalValue']);

    // Limpar produtos existentes da reserva
    $sqlDelete = "DELETE FROM reservation_product WHERE idReservation = ?";
    $stmtDelete = $con->prepare($sqlDelete);
    $stmtDelete->bind_param("i", $id);
    $stmtDelete->execute();

    // Inserir os novos produtos na reserva
    foreach ($idProduct as $productId) {
        $sqlProduct = "INSERT INTO reservation_product (idReservation, idProduct) 
                       VALUES ('$id', '$productId')";
        if ($con->query($sqlProduct) !== TRUE) {
            $msgOp = "ERROR";
            break;
        }

        // Calcular o valor total da reserva
        $sqlValue = "SELECT discounted_value FROM product WHERE id = '$productId'";
        $resultValue = $con->query($sqlValue);
        if ($resultValue && $resultValue->num_rows > 0) {
            $product = $resultValue->fetch_assoc();
            $totalValue += $product['discounted_value'];
        }
    }


    if ($discountMultDays > 0) {
        $totalValue -= ($totalValue * ($discountMultDays / 100));
    }

    if ($discountTotalValue > 0) {
        $totalValue -= ($totalValue * ($discountTotalValue / 100));
    }

    if ($msgOp != "ERROR") {
        $sqlUpdate = "UPDATE reservation SET totalValue = '$totalValue' WHERE id = '$id'";
        if ($con->query($sqlUpdate) === TRUE) {
            header("Location: reservationlist.php");
            exit;
        } else {
            $msgOp = "ERROR";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include "./rel.header.php"; ?>
    <style>
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .reservation-details {
            margin-bottom: 20px;
        }

        .reservation-details p {
            margin: 5px 0;
            font-size: 16px;
        }

        .btn-submit,
        .btn-cancel {
            margin-top: 20px;
            padding: 12px 24px;
            font-size: 16px;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-submit:hover {
            background-color: #0056b3;
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
                        <h4>Editar Produtos Reservados</h4>
                    </div>
                </div>
                <form action="" method="POST">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Selecionar</th>
                                            <th>Item</th>
                                            <th>Marca</th>
                                            <th>Modelo</th>
                                            <th>Valor</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sqlAllProducts = "SELECT id, item, model, brand, discounted_value FROM product";
                                        $resultAllProducts = $con->query($sqlAllProducts);

                                        while ($product = $resultAllProducts->fetch_assoc()) {
                                            $isChecked = in_array($product['id'], $reservedProducts) ? 'checked' : '';
                                        ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="idProduct[]" value="<?= $product['id'] ?>" <?= $isChecked ?>>
                                                </td>
                                                <td><?= $product['item'] ?></td>
                                                <td><?= $product['brand'] ?></td>
                                                <td><?= $product['model'] ?></td>
                                                <td><?= number_format($product['discounted_value'], 2, ',', '.') ?>€</td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                                <div class="col-lg-12">
                                    <div class="button-container">
                                        <a href="reservationlist.php" class="btn btn-cancel">Cancelar</a>
                                        <button class="btn btn-submit" type="submit">Confirmar</button>
                                    </div>
                                </div>
                            </div>
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