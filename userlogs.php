<?php

include "db.config.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "utilizador não especificado ou ID inválido.";
    exit;
}

$estouEm = 11;
$id = intval($_GET['id']);

$sql = "SELECT ul.idUser, ul.datalog, u.name, u.id 
        FROM user_logs ul 
        INNER JOIN user u ON ul.idUser = u.id 
        WHERE ul.idUser = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $id);
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
                        <h4>Logs dos utilizadores</h4>
                    </div>
                    <div class="page-btn">
                        <a href="userlist.php" class="btn btn-added">Lista de utilizadores</a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table ">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Logs</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['name']) ?></td>
                                                <td><?= htmlspecialchars($row['datalog']) ?></td>
                                                <td>
                                                    <a title="Edit" href="edituser.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn btn-filters ms-auto">
                                                        <img src="/assets/img/icons/edit.svg" alt="Edit">
                                                    </a>
                                                    <a title="Delete" href="deleteuser.php?id=<?= htmlspecialchars($row['id']) ?>" class="btn btn-filters ms-auto">
                                                        <img src="/assets/img/icons/delete.svg" alt="Delete">
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4">Nenhum log encontrado</td>
                                        </tr>
                                    <?php endif; ?>
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

</body>

</html>