<?php
include "db.config.php";

$estouEm = 4;

$idUser = $_SESSION['id'];

include "permission.php";

$canDelete = hasPermission($idUser, 4, 'delete', $con);
$canEdit = hasPermission($idUser, 4, 'update', $con);
$canView = hasPermission($idUser, 4, 'view', $con);

if ($canView == 0) {
    header("Location: no_permission.php");
    exit;
}
$canAction = $canEdit || $canDelete || $canView;

// // $sql = "SELECT 
// //     r.id AS reservation_id,
// //     r.idClient,
// //     rps.paymentStatus,
// //     rs.status,
// //     r.orderDateStart,
// //     r.orderDateEnd,
// //     r.totalValue,
// //     c.companyName
// // FROM 
// //     reservation r
// // INNER JOIN 
// //     reservation_paymentstatus rps ON r.idPaymentStatus = rps.id
// // INNER JOIN
// //     reservation_status rs ON r.idStatus = rs.id
// // INNER JOIN 
// //     client c ON r.idClient = c.id";

// // $result = $con->query($sql);

// $reservations = [];
// if ($result->num_rows > 0) {
//     while ($row = $result->fetch_assoc()) {
//         $reservations[] = $row;
//     }
// } else {
//     echo "Nenhuma reserva encontrada.";
// }
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
                <div class="page-header">
                    <div class="page-title">
                        <h4>Lista de Reservas</h4>
                    </div>
                    <div class="page-btn">
                        <a href="addreservation.php" class="btn btn-added">
                            <img src="/assets/img/icons/plus.svg" alt="img" class="me-1">Adicionar Reserva
                        </a>
                    </div>
                </div>
                <div class="form-group">
                    <input type="text" id="search" placeholder="Pesquisar reservas...">
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Estado</th>
                                        <th>Data Início</th>
                                        <th>Data Fim</th>
                                        <th>Estado de Pagamento</th>
                                        <th>Valor</th>
                                        <?php if ($canAction) { ?>
                                            <th>Ações</th>
                                        <?php } ?>
                                    </tr>
                                </thead>
                                <tbody id="reservation-list">
                                    <!-- reservas carregados via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/assets/js/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Passando os dados das reservas para o JavaScript usando JSON
            var canDelete = <?php echo $canDelete ? 'true' : 'false'; ?>;
            var canEdit = <?php echo $canEdit ? 'true' : 'false'; ?>;
            var canView = <?php echo $canView ? 'true' : 'false'; ?>;
            var canAction = <?php echo $canAction ? 'true' : 'false'; ?>;

            // Função para exibir as reservas na tabela
            function fetchReservation(query) {
                $.ajax({
                    url: "search_reservation.php",
                    method: "GET",
                    data: {
                        search: query
                    },
                    dataType: "json",
                    success: function(data) {
                        let reservationList = $("#reservation-list");
                        reservationList.empty(); // Clear the existing products

                        if (data.length > 0) {
                            // Loop through the fetched products and display them
                            data.forEach(function(reservation) {
                                let id = reservation.id; // Adjust this to match the actual product ID
                                let actions = '';

                                // Adicionando botão de "Visualizar"
                                if (canView) {
                                    actions += `
                                        <a title="Visualizar" href="reservation-details.php?id=${reservation.reservation_id}" class="btn btn-filters ms-auto">
                                            <img src="/assets/img/icons/eye.svg" alt="Visualizar">
                                        </a>
                                    `;
                                }

                                // Adicionando botão de "Editar"
                                if (canEdit) {
                                    actions += `
                                        <a title="Editar" href="editreservation.php?id=${reservation.reservation_id}" class="btn btn-filters ms-auto">
                                            <img src="/assets/img/icons/edit.svg" alt="Editar">
                                        </a>
                                    `;
                                }

                                // Adicionando botão de "Excluir"
                                if (canDelete && reservation.status === 'Cancelado') {
                                    actions += `
                                        <a title="Excluir" href="javascript:void(0);" class="btn btn-filters ms-auto" onclick="confirmarExclusao(${reservation.reservation_id})">
                                            <img src="/assets/img/icons/delete.svg" alt="Excluir">
                                        </a>
                                    `;
                                }

                                // Adicionando botão de "Cancelar"
                                if (canDelete && reservation.status === 'Em Espera') {
                                    actions += `
                                        <a title="Cancelar" href="javascript:void(0);" class="btn btn-filters ms-auto" onclick="confirmarCancelamento(${reservation.reservation_id})">
                                            <img src="/assets/img/icons/cancel.svg" alt="Cancelar">
                                        </a>
                                    `;
                                }

                                reservationList.append(`
                                <tr>
                                    <td>${reservation.companyName}</td>
                                    <td>${reservation.status}</td>
                                    <td>${reservation.orderDateStart}</td>
                                    <td>${reservation.orderDateEnd}</td>
                                    <td>${reservation.paymentStatus}</td>
                                    <td>${parseFloat(reservation.totalValue).toFixed(2).replace('.', ',')}</td>
                                    <td>${actions}</td>
                                </tr>
                            `);
                            });
                        } else {
                            // Display a message if no products are found
                            reservationList.append(`
                        <tr>
                            <td colspan="8">Nenhum reserva encontrada.</td>
                        </tr>
                    `);
                        }
                    },
                    error: function() { 
                        alert("Erro ao buscar reservas.");
                    }
                });
            }


            // Função para buscar reservas ao digitar no campo de pesquisa (se for necessário)
            $("#search").on("input", function() {
                const query = $(this).val();
                fetchReservation(query); // Pass the search query
            });
            fetchReservation("");
        });
    </script>
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
        // Função de confirmação de cancelamento
        function confirmarCancelamento(id) {
            Swal.fire({
                title: 'Tem certeza?',
                text: "Esta ação não pode ser desfeita!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sim, cancelar!',
                cancelButtonText: 'Não!',
                reverseButtons: true,
                customClass: {
                    icon: 'swal-icon-custom'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'cancelreservation.php?id=' + id;
                }
            });
        }

        function confirmarExclusao(id) {
            Swal.fire({
                title: 'Tem certeza?',
                text: "Esta ação não pode ser desfeita!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sim, cancelar!',
                cancelButtonText: 'Não !',
                reverseButtons: true,
                customClass: {
                    icon: 'swal-icon-custom'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'deletereservation.php?id=' + id;
                }
            });
        }
    </script>

    <style>
        .swal-icon-custom {
            color: #0d6efd !important;
        }

        .swal2-icon.swal2-warning {
            border-color: #0d6efd !important;
            color: #0d6efd !important;
        }

        .swal2-icon.swal2-warning .swal2-icon-content {
            color: #0d6efd !important;
        }
    </style>
</body>

</html>