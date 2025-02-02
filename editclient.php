<?php

include "db.config.php";

include "permission.php";

$id = intval($_GET['id']);
$idUser = $_SESSION['id'];

$canEdit = hasPermission($idUser, 1, 'update', $con);

if ($canEdit == 0) {
    header("Location: no_permission.php");
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Cliente não especificado.";
    exit;
}

$estouEm = 9;


$client = [];

$sqlCategories = "SELECT id, category FROM client_type";
$resultCategories = $con->query($sqlCategories);

$sql = "SELECT c.*, a.address, a.postalCode, a.city, a.locality, category
        FROM client c 
        LEFT JOIN client_address a ON c.id = a.idClient
        LEFT JOIN client_type ct ON c.idClientType = ct.id 
        WHERE c.id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $client = $result->fetch_assoc();
} else {
    echo "Cliente não encontrado.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $conpassword = $_POST['conpassword'];

    if ($password !== $conpassword) {
        echo "<script>alert('As palavras-passe não coincidem. Por favor, tente novamente.'); window.history.back();</script>";
        exit;
    }
    $responsableName = $_POST['responsableName'];
    $companyName = $_POST['companyName'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $conpassword = $_POST['conpassword'];

    if (!empty($password) && $password !== $conpassword) {
        echo "<script>alert('As palavras-passe não coincidem. Por favor, tente novamente.'); window.history.back();</script>";
        exit;
    }
    $nif = $_POST['nif'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];
    $postalCode = $_POST['postalCode'];
    $city = $_POST['city'];
    $locality = $_POST['locality'];

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

    if ($nif !== $client['nif']) {
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
    } else {
        $nif = $client['nif']; // Manter o NIF atual
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

    $sql_update = "UPDATE client SET responsableName = ?,companyName = ?, email = ? ,nif = ?, contact = ? " . (!empty($password) ? ", password = ?" : "") . "WHERE id = ?";
    $stmt_update = $con->prepare($sql_update);
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt_update->bind_param("ssssisi", $responsableName, $companyName, $email, $hashedPassword, $nif, $contact, $id);
    } else {
        $stmt_update->bind_param("sssssi", $responsableName, $companyName, $email, $nif, $contact, $id);
    }
    $stmt_update->execute();

    $sql_address = "UPDATE client_address SET address = ?, postalCode = ?, city = ?, locality = ? WHERE idClient = ?";
    $stmt_address = $con->prepare($sql_address);
    $stmt_address->bind_param("ssssi", $address, $city, $locality, $postalCode, $id);
    $stmt_address->execute();

    header("Location: clientlist.php");
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
                        <h4>Edit Client Management</h4>
                        <h6>Edit/Update Client</h6>
                    </div>
                </div>

                <form method="POST">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Nome da Empresa</label>
                                        <input type="text" name="companyName" class="form-control" value="<?= $client['companyName'] ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Morada</label>
                                        <input type="text" name="address" class="form-control" value="<?= $client['address'] ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Código Postal</label>
                                        <input type="text" name="postalCode" class="form-control" value="<?= $client['postalCode'] ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Cidade</label>
                                        <input type="text" name="city" class="form-control" value="<?= $client['city'] ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Localidade</label>
                                        <input type="text" name="locality" class="form-control" value="<?= $client['locality'] ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Nome do Responsável</label>
                                        <input type="text" name="responsableName" class="form-control" value="<?= $client['responsableName'] ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Contacto</label>
                                        <input type="text" name="contact" class="form-control" value="<?= $client['contact'] ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>NIF</label>
                                        <input type="text" name="nif" class="form-control" value="<?= $client['nif'] ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" class="form-control" value="<?= $client['email'] ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Tipo de Cliente</label>
                                        <select name="idClientType" class="select" required>
                                            <option value="">Selecione o tipo do cliente</option>
                                            <?php while ($row = $resultCategories->fetch_assoc()) { ?>
                                                <option value="<?= $row['id'] ?>" <?= ($row['id'] == $client['idClientType']) ? 'selected' : '' ?>>
                                                    <?= $row['category'] ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Password</label>
                                        <input type="password" name="password" class="form-control">
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Confirmar Password</label>
                                        <input type="password" name="conpassword" class="form-control">
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="button-container">
                                        <button class="btn btn-submit" type="submit">Atualizar</button>
                                        <a href="clientlist.php" class="btn btn-cancel">Cancelar</a>
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