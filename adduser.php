<?php

include "db.config.php";

include "permission.php";

$idUser = $_SESSION['id'];

$canAdd = hasPermission($idUser, 2, 'insert', $con);

if ($canAdd == 0) {
    header("Location: no_permission.php");
    exit;
}
$estouEm = 11;
$sqlCategories = "SELECT id, category FROM user_type";
$resultCategories = $con->query($sqlCategories);

$sqlModules = "SELECT idModule, module FROM modules";
$resultModules = $con->query($sqlModules);

$table_name = "user";
$msgOp = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'];
    $conpassword = $_POST['conpassword'];

    if ($password !== $conpassword) {
        echo "<script>alert('As palavras-passe não coincidem. Por favor, tente novamente.'); window.history.back();</script>";
        exit;
    }

    $idUserType = $_POST['idUserType'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $phone = $_POST['phone'];

    if ($email !== $client['email']) {
        $checkEmail = $con->prepare("SELECT id FROM user WHERE email = ?");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $checkEmail->store_result();

        if ($checkEmail->num_rows > 0) {
            echo "<script>alert('O e-mail já está cadastrado. Por favor, utilize outro e-mail.'); window.history.back();</script>";
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "<script>alert('O e-mail inserido não é válido.'); window.history.back();</script>";
            exit;
        }
    }

    $sql = "INSERT INTO $table_name (idUserType, name, email, password, phone)
            VALUES ('$idUserType', '$name', '$email', '$password', '$phone')";




    if ($con->query($sql) === TRUE) {
        $idUser = $con->insert_id;
        if (!empty($_POST['permissions'])) {
            foreach ($_POST['permissions'] as $idModule => $actions) {
                $view = isset($actions['view']) ? 1 : 0;
                $insert = isset($actions['insert']) ? 1 : 0;
                $edit = isset($actions['edit']) ? 1 : 0;
                $delete = isset($actions['delete']) ? 1 : 0;

                $sqlPermission = "INSERT INTO user_modules (idUser, idModule, `view`, `insert`, `update`, `delete`)
                VALUES ('$idUser', '$idModule', '$view', '$insert', '$edit', '$delete')";


                if ($con->query($sqlPermission) !== TRUE) {
                    echo "Erro ao inserir permissões: " . $con->error;
                }
            }
        }

        $msgOp = "OK";
    } else {
        $msgOp = "ERROR";
        echo "Erro ao inserir utilizador: " . $con->error;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include "./rel.header.php"; ?>
    <style>
        .permissions-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .permissions-table th,
        .permissions-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        .permissions-table th {
            background-color: #f4f4f4;
            font-weight: bold;
        }

        .permissions-table td:first-child {
            text-align: left;
        }

        .button-container {
            display: flex;
            justify-content: flex-start;
            gap: 20px;
            margin-top: 30px;
        }

        .btn-submit,
        .btn-cancel {
            width: 180px;
            height: 60px;
            text-align: center;
            font-size: 18px;
            border-radius: 8px;
            border: none;
        }
    </style>
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
                        <h4>Adicionar user</h4>
                    </div>
                </div>
                <form action="adduser.php" method="POST">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Nome</label>
                                        <input type="text" name="name" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Contacto</label>
                                        <input type="text" name="phone" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Tipo de Cliente</label>
                                        <select name="idUserType" class="select" required>
                                            <option value="">Selecione um Tipo</option>
                                            <?php while ($row = $resultCategories->fetch_assoc()) { ?>
                                                <option value="<?= $row['id'] ?>"><?= $row['category'] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Palavra Passe</label>
                                        <input type="password" name="password" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Confirmar Palavra Passe</label>
                                        <input type="password" name="conpassword" class="form-control" required>
                                    </div>
                                </div>
                                <div class="permissions-section">
                                    <label>Permissões:</label>
                                    <table class="permissions-table">
                                        <thead>
                                            <tr>
                                                <th>Módulo</th>
                                                <th>Ver</th>
                                                <th>Inserir</th>
                                                <th>Editar</th>
                                                <th>Excluir</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = $resultModules->fetch_assoc()) { ?>
                                                <tr>
                                                    <td><?= $row['module'] ?></td>
                                                    <td><input type="checkbox" name="permissions[<?= $row['idModule'] ?>][view]" value="1"></td>
                                                    <td><input type="checkbox" name="permissions[<?= $row['idModule'] ?>][insert]" value="1"></td>
                                                    <td><input type="checkbox" name="permissions[<?= $row['idModule'] ?>][edit]" value="1"></td>
                                                    <td><input type="checkbox" name="permissions[<?= $row['idModule'] ?>][delete]" value="1"></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>



                                <div class="col-lg-12">
                                    <div class="button-container">
                                        <button class="btn btn-submit" type="submit">Criar</button>
                                        <a href="userlist.php" class="btn btn-cancel">Cancel</a>
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
            title: 'Utilizador adicionado com sucesso!',
            text: 'Deseja adicionar outro utilizador ou ir para a lista?',
            icon: 'success',
            showCancelButton: true,
            confirmButtonText: 'Adicionar Outro',
            cancelButtonText: 'Ir para a Lista'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'adduser.php';
            } else {
                window.location.href = 'userlist.php';
            }
        });";
        } elseif ($msgOp == "ERROR") {
            error_log("Erro ao adicionar utilizador: " . $stmt->error);
            echo "alert('Erro ao adicionar utilizador. Verifique os logs.');";
        }
        ?>
    </script>
    <script>
        function validarEmail(email) {
            // Expressão regular para validar e-mails
            const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return regexEmail.test(email);
        }

        function verificarFormulario(event) {
            const emailInput = document.querySelector('input[name="email"]');
            const email = emailInput.value;

            if (!validarEmail(email)) {
                alert("Por favor, insira um endereço de e-mail válido.");
                emailInput.focus();
                event.preventDefault(); // Impede o envio do formulário
            }
        }

        // Adicionar evento de validação ao formulário
        document.querySelector('form').addEventListener('submit', verificarFormulario);
    </script>

</body>

</html>