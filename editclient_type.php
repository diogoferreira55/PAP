<?php

include "db.config.php";

$estouEm = 10;

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Tipo de Cliente não especificado.";
    exit;
}

$id = intval($_GET['id']);
$client_type = [];

$sql = "SELECT category
        FROM client_type
        WHERE id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $client_type = $result->fetch_assoc();
} else {
    echo "Tipo de Cliente não encontrado.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = $_POST['category'];
    $sql_update = "UPDATE client_type SET category = ? WHERE id = ?";
    $stmt_update = $con->prepare($sql_update);
    $stmt_update->bind_param("si", $category, $id);
    $stmt_update->execute();

    header("Location: client_typelist.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include "./rel.header.php"; ?>
</head>

<body>
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
                        <h4>Editar Tipo de cliente</h4>
                    </div>
                </div>

                <form method="POST">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Tipo de Cliente</label>
                                        <input type="text" name="category" class="form-control" value="<?= $client_type['category'] ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="button-container">
                                        <button class="btn btn-submit" type="submit">Atualizar</button>
                                        <a href="client_typelist.php" class="btn btn-cancel">Cancelar</a>
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