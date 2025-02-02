<?php
$estouEm = 9;

include "db.config.php";

$idUser = $_SESSION['id'];

include "permission.php";

$canDelete = hasPermission($idUser, 1, 'delete', $con);
$canEdit = hasPermission($idUser, 1, 'update', $con);
$canView = hasPermission($idUser, 1, 'view', $con);

if ($canView == 0) {
    header("Location: no_permission.php");
    exit;
}
$canAction = $canEdit || $canDelete || $canView;

$sql = "SELECT 
            c.id, 
            c.responsableName, 
            c.companyName, 
            c.contact, 
            a.address, 
            a.locality, 
            a.city,
            t.category 
        FROM client AS c 
        LEFT JOIN client_address AS a 
        ON c.id = a.idClient 
        LEFT JOIN client_type AS t 
        ON c.idClientType = t.id 
        ORDER BY c.responsableName";

$stmt = $con->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include "./rel.header.php"; ?>
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
                        <h4>Client List</h4>
                        <h6>Manage your Clients</h6>
                    </div>
                    <div class="page-btn">
                        <a href="addclient.php" class="btn btn-added"><img src="assets/img/icons/plus.svg" alt="img">Adicionar Cliente</a>
                    </div>
                </div>
                <div class="form-group">
                    <input type="text" id="search" placeholder="Pesquisar cliente...">
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table ">
                                <thead>
                                    <tr>
                                        <th>Nome do Responsavel</th>
                                        <th>Nome da Empresa</th>
                                        <th>Contacto</th>
                                        <?php if ($canAction) { ?>
                                            <th>Ações</th>
                                        <?php } ?>
                                    </tr>
                                </thead>
                                <tbody id="client-list">
                                    <!-- Client rows will be dynamically inserted here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            var canDelete = <?php echo $canDelete ? 'true' : 'false'; ?>;
            var canEdit = <?php echo $canEdit ? 'true' : 'false'; ?>;
            var canAction = <?php echo $canAction ? 'true' : 'false'; ?>;


            function fetchClient(query) {
                $.ajax({
                    url: "search_client.php",
                    method: "GET",
                    data: {
                        search: query
                    },
                    dataType: "json",
                    success: function(data) {
                        let clientList = $("#client-list");
                        clientList.empty(); // Limpa os usuários existentes

                        if (data.length > 0) {
                            // Loop pelos usuários e exibe-os
                            data.forEach(function(client) {
                                let id = client.id; // Ajuste conforme o ID real do usuário
                                clientList.append(` 
                                    <tr>
                                        <td>${client.responsableName}</td>
                                        <td>${client.companyName}</td>
                                        <td>${client.contact}</td>
                                        ${canAction ? `
                                            <td>
                                                <a title="Ver Reservas do cliente" href="client_reservations.php?id=${client.id}" class="btn btn-filters ms-auto">
                                                    <img src="/assets/img/icons/log.svg" alt="Ver">
                                                </a>
                                                ${canEdit ? `
                                                    <a title="Editar" href="editclient.php?id=${client.id}" class="btn btn-filters ms-auto">
                                                        <img src="/assets/img/icons/edit.svg" alt="Editar">
                                                    </a>
                                                ` : ''}
                                                ${canDelete ? `
                                                    <a title="Excluir" href="javascript:void(0);" class="btn btn-filters ms-auto" onclick="confirmarExclusao(${client.id})">
                                                        <img src="/assets/img/icons/delete.svg" alt="Excluir">
                                                    </a>
                                                ` : ''}
                                            </td>
                                        ` : ''}
                                    </tr>
                                `);
                            });
                        } else {
                            // Exibir mensagem caso não existam usuários
                            clientList.append(`
                                <tr>
                                    <td colspan="5">Nenhum utilizador encontrado.</td>
                                </tr>
                            `);
                        }
                    },
                    error: function() {
                        alert("Erro ao buscar utilizadores.");
                    }
                });
            }

            // Trigger da busca de usuários quando digitar algo
            $("#search").on("input", function() {
                const query = $(this).val();
                fetchClient(query); // Passa a consulta para a função
            });

            // Carrega todos os usuários quando a página for carregada
            fetchClient("");
        });
    </script>
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

    <script>
        function confirmarExclusao(id) {
            Swal.fire({
                title: 'Tem certeza?',
                text: "Esta ação não pode ser desfeita!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Não, cancelar!',
                reverseButtons: true,
                customClass: {
                    icon: 'swal-icon-custom'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'deleteclient.php?id=' + id;
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