<?php
include "db.config.php";

$categoryId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
/// truque a nao esquecer :-)()
$sqlCategories = "SELECT id, category FROM product_category WHERE idmaincategory=0 AND id<>" . $categoryId . ";";
$resultCategories = $con->query($sqlCategories);



$estouEm = 3;
if ($categoryId > 0) {
    $stmt = $con->prepare("SELECT id, idmaincategory, category FROM product_category WHERE id = ?");
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    $categoryData = $result->fetch_assoc();
    $stmt->close();

    if (!$categoryData) {
        echo "Categoria não encontrada.";
        exit;
    }
} else {
    echo "ID inválido.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $categoryName = trim($_POST['category_name']);
    $idCategory = (int)trim($_POST['idCategory']);

    if (!empty($categoryName)) {
        $stmt = $con->prepare("UPDATE product_category SET idmaincategory=?, category = ? WHERE id = ?");
        $stmt->bind_param("isi", $idCategory, $categoryName, $categoryId);

        if ($stmt->execute()) {
            $successMsg = "Categoria atualizada com sucesso!";
            header('Location: categorylist.php');
            exit;
        } else {
            $errorMsg = "Erro ao atualizar a categoria: " . $con->error;
        }
        $stmt->close();
    } else {
        $errorMsg = "O nome da categoria não pode estar vazio.";
    }
}
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
                        <h4>Editar Subcategoria</h4>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <?php if (isset($successMsg)): ?>
                            <div class="alert alert-success"><?= $successMsg ?></div>
                        <?php endif; ?>
                        <?php if (isset($errorMsg)): ?>
                            <div class="alert alert-danger"><?= $errorMsg ?></div>
                        <?php endif; ?>

                        <?php
                        //print_r($categoryData);
                        ?>

                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Categoria</label>
                                        <select name="idCategory" class="select" required>
                                            <option value=0>- categoria principal -</option>
                                            <?php while ($row = $resultCategories->fetch_assoc()) { ?>
                                                <option <?php if ($categoryData['idmaincategory'] == $row['id']) echo "selected='selected'"; ?> value="<?= $row['id'] ?>"><?= $row['category'] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-6 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Category Name</label>
                                        <input type="text" name="category_name" class="form-control" value="<?= $categoryData['category'] ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <button type="submit" class="btn btn-submit me-2">Guardar</button>
                                    <a href="categorylist.php" class="btn btn-cancel">Cancelar</a>
                                </div>
                            </div>
                        </form>
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