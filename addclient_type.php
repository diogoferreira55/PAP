<?php


include "db.config.php";

$estouEm = 10;

$table_name = "client_type";



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category = $_POST['category'];
    $sql = "INSERT INTO $table_name (category)
    VALUES ('$category')";

    if ($con->query($sql) === TRUE) {
        $idclient = $con->insert_id;
        $msgOp = "OK";
    } else {
        $msgOp = "ERROR";
    }
}

$con->close();
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
                        <h4>Adicionar Tipo de Cliente</h4>
                    </div>
                </div>
                <form action="addclient_type.php" method="POST">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Tipo de Cliente</label>
                                        <input type="text" name="category" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="button-container">
                                        <button class="btn btn-submit" type="submit">Criar</button>
                                        <a href="client_typelist.php" class="btn btn-cancel">Cancel</a>
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
            title: 'Tipo de cliente adicionado com sucesso!',
            text: 'Deseja adicionar outro tipo de cliente ou ir para a lista?',
            icon: 'success',
            showCancelButton: true,
            confirmButtonText: 'Adicionar Outro',
            cancelButtonText: 'Ir para a Lista'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'addclient_type.php';
            } else {
                window.location.href = 'client_typelist.php';
            }
        });";
        } elseif ($msgOp == "ERROR") {
            error_log("Erro ao adicionar tipo de cliente: " . $stmt->error);
            echo "alert('Erro ao adicionar tipo de cliente. Verifique os logs.');";
        }
        ?>
    </script>
</body>

</html>