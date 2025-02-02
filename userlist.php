<?php
$estouEm = 11; // ID do módulo (Exemplo: Módulo de "Usuários")
include "db.config.php";

$idUser = $_SESSION['id'];

include "permission.php";

$canDelete = hasPermission($idUser, 2, 'delete', $con);
$canEdit = hasPermission($idUser, 2, 'update', $con);
$canView = hasPermission($idUser, 2, 'view', $con);

if ($canView == 0) {
    header("Location: no_permission.php");
    exit;
}
$canAction = $canEdit || $canDelete;

$sql = "SELECT id, name, phone, email FROM user";
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
                        <h4>Lista de Utizadores</h4>
                    </div>
                    <div class="page-btn">
                        <a href="adduser.php" class="btn btn-added"><img src="\assets/img/icons/plus.svg" alt="img">Adicionar Utilizador</a>
                    </div>
                </div>
                <div class="form-group">
                    <input type="text" id="search" placeholder="Pesquisar utilizador...">
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Telémovel</th>
                                        <th>Email</th>
                                        <?php if ($canAction) { ?>
                                            <th>Ações</th>
                                        <?php } ?>
                                    </tr>
                                </thead>
                                <tbody id="user-list">
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


            function fetchUser(query) {
                $.ajax({
                    url: "search_user.php",
                    method: "GET",
                    data: {
                        search: query
                    },
                    dataType: "json",
                    success: function(data) {
                        let userList = $("#user-list");
                        userList.empty(); // Limpa os usuários existentes

                        if (data.length > 0) {
                            // Loop pelos usuários e exibe-os
                            data.forEach(function(user) {
                                let id = user.id; // Ajuste conforme o ID real do usuário
                                userList.append(`
                                    <tr>
                                        <td>${user.name}</td>
                                        <td>${user.phone}</td>
                                        <td>${user.email}</td>
                                        ${canAction ? `
                                            <td>
                                                <a title="Logs" href="userlogs.php?id=${user.id}" class="btn btn-filters ms-auto">
                                                    <img src="/assets/img/icons/log.svg" alt="Editar">
                                                </a>
                                                ${canEdit ? `
                                                    <a title="Editar" href="edituser.php?id=${user.id}" class="btn btn-filters ms-auto">
                                                        <img src="/assets/img/icons/edit.svg" alt="Editar">
                                                    </a>
                                                ` : ''}
                                                ${canDelete ? `
                                                    <a title="Excluir" href="javascript:void(0);" class="btn btn-filters ms-auto" onclick="confirmarExclusao(${user.id})">
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
                            userList.append(`
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
                fetchUser(query); // Passa a consulta para a função
            });

            // Carrega todos os usuários quando a página for carregada
            fetchUser("");
        });
    </script>
    <script src="assets/js/script.js"></script>
    <script src="assets/js/feather.min.js"></script>

    <script src="assets/js/jquery.slimscroll.min.js"></script>

    <script src="assets/js/jquery.dataTables.min.js"></script>
    <script src="assets/js/dataTables.bootstrap4.min.js"></script>

    <script src="assets/js/bootstrap.bundle.min.js"></script>

    <script src="assets/plugins/select2/js/select2.min.js"></script>

    <script src="assets/plugins/sweetalert/sweetalert2.all.min.js"></script>
    <script src="assets/plugins/sweetalert/sweetalerts.min.js"></script>
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
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'deleteuser.php?id=' + id;
                }
            });
        }
    </script>
</body>

</html>