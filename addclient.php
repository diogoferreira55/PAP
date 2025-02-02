<?php
include "db.config.php";

$estouEm = 9;

include "permission.php";

$idUser = $_SESSION['id'];

$canAdd = hasPermission($idUser, 1, 'insert', $con);

if ($canAdd == 0) {
    header("Location: no_permission.php");
    exit;
}


$sqlCategories = "SELECT id, category FROM client_type";
$resultCategories = $con->query($sqlCategories);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'];
    $conpassword = $_POST['conpassword'];

    if ($password !== $conpassword) {
        echo "<script>alert('As palavras-passe não coincidem. Por favor, tente novamente.'); window.history.back();</script>";
        exit;
    }

    $idClientType = $_POST['idClientType'];
    $responsableName = $_POST['responsableName'];
    $companyName = $_POST['companyName'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $nif = $_POST['nif'];
    $contact = $_POST['contact'];

    function validarNIF($nif)
    {
        if (!preg_match('/^[0-9]{9}$/', $nif)) {
            return false;
        }
        $soma = 0;
        for ($i = 0; $i < 8; $i++) {
            $soma += $nif[$i] * (9 - $i);
        }
        $digitoControle = 11 - ($soma % 11);
        if ($digitoControle >= 10) {
            $digitoControle = 0;
        }
        return $digitoControle == $nif[8];
    }

    if (!validarNIF($nif)) {
        echo "<script>alert('O NIF inserido não é válido. Por favor, tente novamente.'); window.history.back();</script>";
        exit;
    }

    $checkNif = $con->prepare("SELECT id FROM client WHERE nif = ?");
    $checkNif->bind_param("s", $nif);
    $checkNif->execute();
    $checkNif->store_result();

    if ($checkNif->num_rows > 0) {
        echo "<script>alert('O NIF já está cadastrado. Por favor, utilize outro NIF.'); window.history.back();</script>";
        exit;
    }

    if ($email !== $client['email']) {
        $checkEmail = $con->prepare("SELECT id FROM client WHERE email = ?");
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

    $sql = "INSERT INTO client (idClientType, responsableName, companyName, email, password, nif, contact)
    VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("issssis", $idClientType, $responsableName, $companyName, $email, $password, $nif, $contact);

    if ($stmt->execute()) {
        $idClient = $stmt->insert_id;

        $address = $_POST['address'];
        $postalCode = $_POST['postalCode'];
        $locality = $_POST['locality'];
        $city = $_POST['city'];

        $sqlAddress = "INSERT INTO client_address (idClient, address, postalCode, locality, city)
        VALUES (?, ?, ?, ?, ?)";
        $stmtAddress = $con->prepare($sqlAddress);
        $stmtAddress->bind_param("issss", $idClient, $address, $postalCode, $locality, $city);

        if ($stmtAddress->execute()) {
            $msgOp = "OK";
        } else {
            $msgOp = "ERROR";
        }
    } else {
        echo "Erro ao inserir dados do cliente: " . $stmt->error;
        exit;
    }
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
                        <h4>Adicionar Client</h4>
                    </div>
                </div>
                <form action="addclient.php" method="POST">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Nome da Empresa</label>
                                        <input type="text" name="companyName" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Nome do Responsavel</label>
                                        <input type="text" name="responsableName" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Morada</label>
                                        <input type="text" name="address" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Codigo Postal</label>
                                        <input type="text" name="postalCode" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Cidade</label>
                                        <input type="text" name="city" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Localidade</label>
                                        <input type="text" name="locality" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Tipo de Cliente</label>
                                        <select name="idClientType" class="select" required>
                                            <option value="">Selecione o tipo do cliente</option>
                                            <?php while ($row = $resultCategories->fetch_assoc()) { ?>
                                                <option value="<?= $row['id'] ?>"><?= $row['category'] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Contacto</label>
                                        <input type="text" name="contact" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Nif</label>
                                        <input type="text" name="nif" class="form-control" required>
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
                                        <label>Password</label>
                                        <input type="password" name="password" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Confirmar Password</label>
                                        <input type="password" name="conpassword" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="button-container">
                                        <button class="btn btn-submit" type="submit">Criar</button>
                                        <a href="clientlist.php" class="btn btn-cancel">Cancel</a>
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
            title: 'Cliente adicionado com sucesso!',
            text: 'Deseja adicionar outro cliente ou ir para a lista?',
            icon: 'success',
            showCancelButton: true,
            confirmButtonText: 'Adicionar Outro',
            cancelButtonText: 'Ir para a Lista'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'addclient.php';
            } else {
                window.location.href = 'clientlist.php';
            }
        });";
        } elseif ($msgOp == "ERROR") {
            error_log("Erro ao adicionar produto: " . $stmt->error);
            echo "alert('Erro ao adicionar produto. Verifique os logs.');";
        }
        ?>
    </script>
</body>

</html>
<?php
$con->close();
?>