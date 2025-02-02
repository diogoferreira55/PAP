<?php
include "db.config.php";

$estouEm = 3;

$sqlCategories = "SELECT id, category FROM product_category WHERE idmaincategory=0;";
$resultCategories = $con->query($sqlCategories);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $categoryName = trim($_POST['category_name']);
    $idCategory = (int)trim($_POST['idCategory']);

    if (!empty($categoryName)) {
        $stmt = $con->prepare("INSERT INTO product_category (idmaincategory, category) VALUES (?, ?)");
        $stmt->bind_param("is", $idCategory, $categoryName);

        if ($stmt->execute()) {
            $msgOp = "OK";
        } else {
            $msgOp = "ERROR";
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                        <h4>Adicionar Categoria</h4>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">

                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Categoria</label>
                                        <select name="idCategory" class="select" required>
                                            <option value=0>- categoria principal -</option>
                                            <?php while ($row = $resultCategories->fetch_assoc()) { ?>
                                                <option value="<?= $row['id'] ?>"><?= $row['category'] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-6 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Category Name</label>
                                        <input type="text" name="category_name" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <button type="submit" class="btn btn-submit me-2">Criar</button>
                                    <a href="categorylist.php" class="btn btn-cancel">Cancel</a>
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
    <script>
        $(document).ready(function() {
            /*$('#categorySelect').change(function() {
                var categoryId = $(this).val();

                if (categoryId) {
                    $.ajax({
                        type: 'POST',
                        url: 'addproduct.php', // O mesmo arquivo
                        data: {
                            idCategory: categoryId
                        },
                        success: function(response) {
                            $('#subCategorySelect').html(response);
                        }
                    });
                }
            });*/
        });

        <?php
        if ($msgOp == "OK") {
            echo "
        Swal.fire({
            title: 'Categoria de produtos adicionado com sucesso!',
            text: 'Deseja adicionar outro categoria ou ir para a lista?',
            icon: 'success',
            showCancelButton: true,
            confirmButtonText: 'Adicionar Outro',
            cancelButtonText: 'Ir para a Lista'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'addcategory.php';
            } else {
                window.location.href = 'categorylist.php';
            }
        });";
        } elseif ($msgOp == "ERROR") {
            error_log("Erro ao adicionar categoria: " . $stmt->error);
            echo "alert('Erro ao adicionar categoria. Verifique os logs.');";
        }
        ?>
    </script>
</body>

</html>