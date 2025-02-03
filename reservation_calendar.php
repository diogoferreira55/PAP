<?php
include "db.config.php"; // Conexão com o banco de dados
include "permission.php"; // Permissões do usuário

// Obtém o ID do usuário e define as permissões
$idUser    = $_SESSION['id'];
$canDelete = hasPermission($idUser, 4, 'delete', $con);
$canEdit   = hasPermission($idUser, 4, 'update', $con);
$canView   = hasPermission($idUser, 4, 'view', $con);

$canAction = $canEdit || $canDelete || $canView;

$estouEm = 6;
// Se for uma requisição AJAX para buscar eventos do calendário
if (isset($_GET['action']) && $_GET['action'] == "getEvents") {
    header('Content-Type: application/json');

    $sql = "SELECT id, orderDateStart, orderDateEnd, companyName FROM reservation";
    $result = $con->query($sql);

    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = [
            'id'    => $row['id'],
            'title' => $row['companyName'],
            'start' => $row['orderDateStart'],
            'end'   => $row['orderDateEnd']
        ];
    }
    echo json_encode($events);
    exit;
}

// Se for uma requisição AJAX para buscar reservas de um dia específico
if (isset($_GET['action']) && $_GET['action'] == "getReservations") {
    $dateFilter = $_GET['date'] ?? null;

    if (!$dateFilter) {
        echo "<p>Data não informada.</p>";
        exit;
    }

    $stmt = $con->prepare("SELECT r.id, c.companyName, r.orderDateStart, r.orderDateEnd, r.totalValue
                           FROM reservation r
                           INNER JOIN client c ON r.idClient = c.id
                           WHERE DATE(r.orderDateStart) = ?");
    $stmt->bind_param("s", $dateFilter);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '<table class="table table-striped">';
        echo '<thead><tr><th>Cliente</th><th>Início</th><th>Fim</th><th>Valor</th></tr></thead><tbody>';
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . $row['companyName'] . '</td>';
            echo '<td>' . $row['orderDateStart'] . '</td>';
            echo '<td>' . $row['orderDateEnd'] . '</td>';
            echo '<td>' . number_format($row['totalValue'], 2, ',', '.') . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo "<p>Nenhuma reserva encontrada.</p>";
    }

    $stmt->close();
    exit;
}

// Se for uma requisição AJAX para buscar produtos relacionados a uma data
if (isset($_GET['action']) && $_GET['action'] == "getProducts") {
    $dateFilter = $_GET['date'] ?? null;

    if (!$dateFilter) {
        echo "<p>Data não informada.</p>";
        exit;
    }

    $stmt = $con->prepare("SELECT p.item, p.brand,p.model,p.value
                           FROM product p
                           INNER JOIN reservation_product rp ON rp.idProduct = p.id
                           INNER JOIN reservation r ON r.id = rp.idReservation
                           WHERE r.orderDateStart <= ? AND r.orderDateEnd >= ?");
    $stmt->bind_param("ss", $dateFilter, $dateFilter);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '<table class="table table-striped">';
        echo '<thead><tr><th>Produto</th><th>Quantidade</th></tr></thead><tbody>';
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . $row['name'] . '</td>';
            echo '<td>' . $row['quantity'] . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo "<p>Nenhum produto encontrado.</p>";
    }

    $stmt->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <?php include "./rel.header.php"; ?>
    <style>
        #calendar {
            max-width: 900px;
            margin: 40px auto;
        }
    </style>
</head>

<body>
    <div id="global-loader">
        <div class="whirly-loader"> </div>
    </div>
    <div class="page-wrapper">

        <div class="main-wrapper">
            <?php include "./menu.header.php"; ?>
            <?php include "./menu.lateral.php"; ?>
            <div class="content">
                <div class="page-header">
                    <div class="page-title">
                        <h2 class="text-center">Calendário de Reservas</h2>
                    </div>
                </div>
                <div id="calendar"></div>

                <!-- Modal para exibir reservas -->
                <div class="modal fade" id="reservationModal" tabindex="-1" aria-labelledby="reservationModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Reservas para <span id="modalDate"></span></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body" id="modalContent">
                                <p>Carregando reservas...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="/assets/js/feather.min.js"></script>
    <script src="/assets/js/jquery.slimscroll.min.js"></script>
    <script src="/assets/js/jquery.dataTables.min.js"></script>
    <script src="/assets/js/dataTables.bootstrap4.min.js"></script>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/plugins/select2/js/select2.min.js"></script>
    <script src="/assets/plugins/sweetalert/sweetalert2.all.min.js"></script>
    <script src="/assets/plugins/sweetalert/sweetalerts.min.js"></script>
    <script src="/assets/js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                selectable: true,

                // Quando clicar em uma data, abre o modal e carrega as reservas
                dateClick: function(info) {
                    $("#modalDate").text(info.dateStr);
                    $("#modalContent").html("<p>Carregando reservas...</p>");
                    $("#reservationModal").modal("show");

                    $.ajax({
                        url: "<?php echo $_SERVER['PHP_SELF']; ?>",
                        type: "GET",
                        data: {
                            action: "getReservations",
                            date: info.dateStr
                        },
                        success: function(data) {
                            $("#modalContent").html(data);
                        },
                        error: function() {
                            $("#modalContent").html("<p>Erro ao carregar reservas.</p>");
                        }
                    });

                },

            });

            calendar.render();
        });
    </script>
</body>

</html>