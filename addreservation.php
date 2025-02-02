<?php

include "db.config.php";

include "permission.php";

$idUser = $_SESSION['id'];

$canAdd = hasPermission($idUser, 4, 'insert', $con);

if ($canAdd == 0) {
    header("Location: no_permission.php");
    exit;
}

$estouEm = 4;

$table_name = "reservation";
$defaultStatusId = 1;
$defaultPaymentStatusId = 1;
$reservation = [];

$sqlClients = "SELECT id, companyName FROM client";
$resultClients = $con->query($sqlClients);

$sqlStatus = "SELECT id, status FROM reservation_status";
$resultStatus = $con->query($sqlStatus);

$sqlPaymentStatus = "SELECT id, paymentStatus FROM reservation_paymentStatus";
$resultPaymentStatus = $con->query($sqlPaymentStatus);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idClient = $_POST['idClient'];
    $idPaymentStatus = $_POST['idPaymentStatus'];
    $idStatus = $_POST['idStatus'];
    $orderDateStart = $_POST['orderDateStart'];
    $orderDateEnd = $_POST['orderDateEnd'];
    $discountTotalValue = $_POST['discountTotalValue'];
    $discountMultDays = $_POST['discountMultDays'];

    $startDateTime = new DateTime($orderDateStart);
    $endDateTime = new DateTime($orderDateEnd);

    $startHour = (int)$startDateTime->format('H');
    $endHour = (int)$endDateTime->format('H');

    if ($startHour < 8 || $startHour >= 18 || $endHour < 8 || $endHour >= 18) {
        die("Erro: As reservas só podem ser feitas entre 08:00 e 18:00.");
    }
    
    $sql = "INSERT INTO $table_name (idClient, idPaymentStatus,idStatus, orderDateStart, orderDateEnd, discountTotalValue, discountMultDays)
            VALUES ('$idClient','$idPaymentStatus','$idStatus', '$orderDateStart','$orderDateEnd', '$discountTotalValue','$discountMultDays')";

    if ($con->query($sql) === TRUE) {
        $idReservation = $con->insert_id;
        header("Location: selectproduct.php?idReservation=$idReservation");
        exit();

        // if ($con->query($sqlProduct) === TRUE) {
        //     echo "Reserva e produto inseridos com sucesso!";
        // } else {
        //     echo "Erro ao inserir produto: " . $con->error;
        // }
    } else {
        echo "Erro ao inserir reserva: " . $con->error;
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

        .modal-content {
            padding: 20px;
        }

        #productModal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60%;
            max-width: 800px;
            height: auto;
            max-height: 90%;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            overflow-y: auto;
        }

        .modal-content h4 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.5rem;
            color: #333;
        }

        .modal-content form {
            margin-top: 10px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        .table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .modal-content label {
            flex: 1 1 calc(50% - 10px);
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .modal-content label:hover {
            background-color: #e9ecef;
        }

        .modal-content input[type="checkbox"] {
            margin: 0;
            width: 20px;
            height: 20px;
        }

        .close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 25px;
            color: #333;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
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
                        <h4>Adicionar Reserva</h4>
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
                                                <option value="<?= $row['id'] ?>"><?= $row['companyName'] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Data e Hora Inicial</label>
                                        <input type="datetime-local" id="orderDateStart" name="orderDateStart" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Data e Hora Final</label>
                                        <input type="datetime-local" id="orderDateEnd" name="orderDateEnd" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12" id="discountMultDays" style="display: none;">
                                    <div class="form-group">
                                        <label>Desconto Reserva</label>
                                        <input type="number" name="discountMultDays" class="form-control">
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12" id="discountTotalValue">
                                    <div class="form-group">
                                        <label>Desconto Valor Total</label>
                                        <input type="number" name="discountTotalValue" class="form-control">
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Estado</label>
                                        <select name="idStatus" class="select" required>
                                            <?php while ($status = $resultStatus->fetch_assoc()) { ?>
                                                <option value="<?= $status['id'] ?>" <?= $status['id'] == $defaultStatusId ? 'selected' : '' ?>>
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
                                                <option value="<?= $paymentStatus['id'] ?>" <?= $paymentStatus['id'] == $defaultPaymentStatusId ? 'selected' : '' ?>>
                                                    <?= $paymentStatus['paymentStatus'] ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="button-container">
                                        <button type="submit" class="btn btn-submit me-2">Avançar</button>
                                        <a href="reservationlist.php" class="btn btn-cancel">Cancelar</a>
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
    <script src="assets/js/jquery.dataTables.min.js"></script>
    <script src="assets/js/dataTables.bootstrap4.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/plugins/select2/js/select2.min.js"></script>
    <script src="assets/plugins/sweetalert/sweetalert2.all.min.js"></script>
    <script src="assets/plugins/sweetalert/sweetalerts.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script>
        // Obter a data e hora atual no formato necessário para datetime-local
        const now = new Date();
        const formattedNow = now.toISOString().slice(0, 16); // Formato YYYY-MM-DDTHH:mm

        // Definir o valor padrão para os campos "Data e Hora Inicial" e "Data e Hora Final"
        document.getElementById('orderDateStart').value = formattedNow;
        document.getElementById('orderDateEnd').value = formattedNow;
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const orderDateStart = document.getElementById("orderDateStart");
            const orderDateEnd = document.getElementById("orderDateEnd");
            const discountMultDays = document.getElementById("discountMultDays");

            function toggleDiscountMultDays() {
                const startDate = new Date(orderDateStart.value);
                const endDate = new Date(orderDateEnd.value);

                // Calcular a diferença em milissegundos e converter para dias
                const diffInTime = endDate - startDate;
                const diffInDays = diffInTime / (1000 * 60 * 60 * 24);

                // Mostrar ou ocultar o campo de desconto
                if (diffInDays > 1) {
                    discountMultDays.style.display = "block";
                } else {
                    discountMultDays.style.display = "none";
                }
            }

            // Adiciona evento de escuta para mudanças nas datas
            orderDateStart.addEventListener("change", toggleDiscountMultDays);
            orderDateEnd.addEventListener("change", toggleDiscountMultDays);
        });
    </script>
</body>
</html>