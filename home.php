<?php

include "db.config.php";

$estouEm = 1;

$months = range(1, 12);
$monthly_reservations = array_fill(0, 12, 0);

$sql = "SELECT MONTH(orderDateStart) AS month, COUNT(id) AS total_reservations_month
        FROM reservation 
        GROUP BY MONTH(orderDateStart)";
$stmt = $con->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $month_index = $row['month'] - 1;
    $monthly_reservations[$month_index] = $row['total_reservations_month'];
}

// Converta os arrays para strings JSON para uso no JavaScript
$months_js = json_encode($months);
$monthly_reservations_js = json_encode($monthly_reservations);


$sql = "SELECT COUNT(id) AS total_clients FROM client";
$stmt = $con->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_clients = $row['total_clients'];

$sql = "SELECT COUNT(id) AS total_products FROM product";
$stmt = $con->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_products = $row['total_products'];

$sql = "SELECT COUNT(id) AS total_reservations FROM reservation";
$stmt = $con->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_reservations = $row['total_reservations'];

$sql = "SELECT COUNT(id) AS total_users FROM user";
$stmt = $con->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_users = $row['total_users'];

$sql = "SELECT COUNT(id) AS total_reservations FROM reservation";
$stmt = $con->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_reservations = $row['total_reservations'];

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <?php include "./rel.header.php"; ?>

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
                <div class="row">
                    <div class="col-lg-3 col-sm-6 col-12 d-flex">
                        <div class="dash-count">
                            <div class="dash-counts">
                                <h4><?php echo $total_clients; ?></h4>
                                <h5>Clientes</h5>
                            </div>
                            <div class="dash-imgs">
                                <i data-feather="user"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-12 d-flex">
                        <div class="dash-count">
                            <div class="dash-counts">
                                <h4><?php echo $total_products; ?></h4>
                                <h5>Produtos</h5>
                            </div>
                            <div class="dash-imgs">
                                <i data-feather="box"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-12 d-flex">
                        <div class="dash-count">
                            <div class="dash-counts">
                                <h4><?php echo $total_reservations; ?></h4>
                                <h5>Reservas</h5>
                            </div>
                            <div class="dash-imgs">
                                <i data-feather="tag"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-12 d-flex">
                        <div class="dash-count">
                            <div class="dash-counts">
                                <h4><?php echo $total_users; ?></h4>
                                <h5>Utilizadores</h5>
                            </div>
                            <div class="dash-imgs">
                                <i data-feather="users"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-7 col-sm-12 col-12 d-flex">
                        <div class="card flex-fill">
                            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Total de Reservas por Mês</h5>
                            </div>
                            <div class="card-body">
                                <div id="sales_charts"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5 col-sm-12 col-12 d-flex">
                        <div class="card flex-fill">
                            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                                <h4 class="card-title mb-0">Produtos recentemente adicionados</h4>
                                <div class="dropdown">
                                    <a href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false" class="dropset">
                                        <i class="fa fa-ellipsis-v"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <li>
                                            <a href="productlist.php" class="dropdown-item">Product List</a>
                                        </li>
                                        <li>
                                            <a href="addproduct.php" class="dropdown-item">Product Add</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive dataview">
                                    <table class="table datatable">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Produto</th>
                                                <th>Preço</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql = "SELECT id, item, discounted_value FROM product ORDER BY created ASC LIMIT 4";
                                            $stmt = $con->prepare($sql);
                                            $stmt->execute();
                                            $result = $stmt->get_result();
                                            while ($row = $result->fetch_assoc()) {
                                                $product_id = $row['id'];
                                                $product_name = $row['item'];
                                                $product_price = $row['discounted_value'];
                                                echo "<tr>
                                                    <td>$product_id</td>
                                                    <td>$product_name</td>
                                                    <td>$product_price €</td>
                                                    </tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



                <div class="card mb-0">
                    <div class="card-body">
                        <h4 class="card-title">Reservas Recentes</h4>
                        <div class="table-responsive dataview">
                            <table class="table datatable ">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Cliente</th>
                                        <th>Data Inicial</th>
                                        <th>Data Final</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT r.id, c.companyName AS client_name, r.orderDateStart, r.orderDateEnd 
                            FROM reservation r
                            JOIN client c ON r.idClient = c.id
                            ORDER BY r.orderDateStart DESC 
                            LIMIT 5";
                                    $stmt = $con->prepare($sql);
                                    $stmt->execute();
                                    $result = $stmt->get_result();

                                    $sno = 1; // Número de série
                                    while ($row = $result->fetch_assoc()) {
                                        $client_name = $row['client_name'];
                                        $start_date = $row['orderDateStart'];
                                        $end_date = $row['orderDateEnd'];

                                        echo "<tr>
                                            <td>$sno</td>
                                            <td>$client_name</td>
                                            <td>$start_date</td>
                                            <td>$end_date</td>
                                        </tr>";
                                        $sno++;
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>


    <script src="/assets/js/jquery-3.6.0.min.js"></script>


    <script src="/assets/js/feather.min.js"></script>

    <script src="/assets/js/jquery.slimscroll.min.js"></script>

    <script src="/assets/js/jquery.dataTables.min.js"></script>
    <script src="/assets/js/dataTables.bootstrap4.min.js"></script>

    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="/assets/plugins/apexchart/apexcharts.min.js"></script>
    <script>
        // Dados passados do PHP
        var months = <?php echo $months_js; ?>;
        var monthlyReservations = <?php echo $monthly_reservations_js; ?>;

        // Configuração do gráfico
        var options = {
            chart: {
                height: 350,
                type: 'bar',
            },
            plotOptions: {
                bar: {
                    columnWidth: '50%',
                    colors: {
                        ranges: [{
                            from: 0,
                            to: 1000, // Faixa genérica
                            color: '#007bff' // Cor azul
                        }]
                    },
                },
            },
            dataLabels: {
                enabled: false
            },
            series: [{
                name: 'Total de Reservas',
                data: monthlyReservations // Dados mensais de reservas
            }],
            xaxis: {
                categories: months.map(month => {
                    const monthNames = ["Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"];
                    return monthNames[month - 1]; // Convertendo número do mês para o nome
                }),
            },
            yaxis: {
                labels: {
                    formatter: function(value) {
                        return Math.round(value); // Garantir que o número seja inteiro
                    }
                },
            },
        };
        // Renderizando o gráfico
        var chart = new ApexCharts(document.querySelector("#sales_charts"), options);
        chart.render();
    </script>
    <script src="/assets/js/script.js"></script>
</body>
</html>