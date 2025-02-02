<?php
$estouEm = 10;

include "db.config.php";

$sql = "SELECT 
            id, 
            category
        FROM client_type";
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
                        <h4>Lista Tipos de Clientes</h4>
                    </div>
                    <div class="page-btn">
                        <a href="addclient_type.php" class="btn btn-added"><img src="\assets/img/icons/plus.svg" alt="img">Adicionar Tipo de Cliente</a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive ">
                            <table class="table ">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tipo de Cliente</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                    ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['id']) ?></td>
                                                <td><?= htmlspecialchars($row['category']) ?></td>
                                                <td>
                                                    <a title="Edit" href="editclient_type.php?id=<?= $row['id'] ?>" class="btn btn-filters ms-auto">
                                                        <img src="/assets/img/icons/edit.svg" alt="Edit">
                                                    </a>
                                                    <a title="Delete" href="javascript:void(0);" class="btn btn-filters ms-auto" onclick="confirmarExclusao(<?= $row['id'] ?>)">
                                                        <img src="/assets/img/icons/delete.svg" alt="Delete">
                                                    </a>
                                                </td>
                                            </tr>
                                    <?php
                                        }
                                    } else {
                                        echo '<tr><td colspan="8">No clients found</td></tr>';
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
                    window.location.href = 'deleteclient_type.php?id=' + id;
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