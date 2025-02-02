<?php

include "db.config.php";

include "permission.php";

$idUser = $_SESSION['id'];

$canEdit = hasPermission($idUser, 2, 'update', $con);

if ($canEdit == 0) {
    header("Location: no_permission.php");
    exit;
}

$estouEm = 11;
$id = intval($_GET['id']);
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Utilizador não especificado.";
    exit;
}

$user = [];

// Consultar dados do utilizador
$sql = "SELECT * FROM user WHERE id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "Utilizador não encontrado.";
    exit;
}

// Consultar permissões dos módulos
$sqlModules = "
    SELECT m.idModule, m.module, 
           IFNULL(um.view, 0) AS view, 
           IFNULL(um.`insert`, 0) AS `insert`, 
           IFNULL(um.`update`, 0) AS `update`, 
           IFNULL(um.delete, 0) AS `delete`
    FROM modules m 
    LEFT JOIN user_modules um ON m.idModule = um.idModule AND um.idUser = ?";
$stmtModules = $con->prepare($sqlModules);
$stmtModules->bind_param("i", $id);
$stmtModules->execute();
$resultModules = $stmtModules->get_result();

// Verificar se o usuário tem permissão de editar (campo 'update')
$canEditPermissions = false;

$sqlPermissionCheck = "SELECT `update` FROM user_modules WHERE idUser = ? AND idModule IN (SELECT idModule FROM modules)";
$stmtPermissionCheck = $con->prepare($sqlPermissionCheck);
$stmtPermissionCheck->bind_param("i", $id);
$stmtPermissionCheck->execute();
$resultPermissionCheck = $stmtPermissionCheck->get_result();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $conpassword = $_POST['conpassword'];


    if (!empty($password) && $password !== $conpassword) {
        echo "<script>alert('As palavras-passe não coincidem. Por favor, tente novamente.'); window.history.back();</script>";
        exit;
    }

    if ($email !== $user['email']) {
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


    if (!preg_match("/^[0-9\-\+]{9,15}$/", $phone)) {
        echo "<script>alert('Por favor, insira um contacto válido.'); window.history.back();</script>";
        exit;
    }

    // Atualizar dados do utilizador
    $sqlUpdateUser = "UPDATE user SET name = ?, email = ?,phone=?" . (!empty($password) ? ", password = ?" : "") . " WHERE id = ?";
    $stmtUpdateUser = $con->prepare($sqlUpdateUser);
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmtUpdateUser->bind_param("ssssi", $name, $email,$phone, $hashedPassword, $id);
    } else {
        $stmtUpdateUser->bind_param("sssi", $name, $email,$phone, $id);
    }
    $stmtUpdateUser->execute();

    // Atualizar permissões
    if (!empty($_POST['permissions'])) {
        foreach ($_POST['permissions'] as $idModule => $actions) {
            foreach (['view', 'insert', 'update', 'delete'] as $action) {
                $enabled = isset($actions[$action]) ? intval($actions[$action]) : 0;

                // Verifica se a permissão já existe
                $sqlCheckPermission = "SELECT idUser FROM user_modules WHERE idUser = ? AND idModule = ?";
                $stmtCheckPermission = $con->prepare($sqlCheckPermission);
                $stmtCheckPermission->bind_param("ii", $id, $idModule);
                $stmtCheckPermission->execute();
                $resultCheckPermission = $stmtCheckPermission->get_result();

                if ($resultCheckPermission->num_rows > 0) {
                    // Atualiza a permissão existente
                    $sqlUpdatePermission = "UPDATE user_modules SET `$action` = ? WHERE idUser = ? AND idModule = ?";
                    $stmtUpdatePermission = $con->prepare($sqlUpdatePermission);
                    $stmtUpdatePermission->bind_param("iii", $enabled, $id, $idModule);
                    echo $enabled;
                    $stmtUpdatePermission->execute();
                } else {
                    // Insere uma nova permissão
                    $sqlInsertPermission = "INSERT INTO user_modules (idUser, idModule, `$action`) VALUES (?, ?, ?)";
                    $stmtInsertPermission = $con->prepare($sqlInsertPermission);
                    $stmtInsertPermission->bind_param("iii", $id, $idModule, $enabled);
                    $stmtInsertPermission->execute();
                }
            }
        }
    }


    header("Location: userlist.php");
    exit;
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
        <div class="whirly-loader"></div>
    </div>

    <div class="main-wrapper">
        <?php include "./menu.header.php"; ?>
        <?php include "./menu.lateral.php"; ?>

        <div class="page-wrapper">
            <div class="content">
                <div class="page-header">
                    <div class="page-title">
                        <h4>Editar Utilizador</h4>
                    </div>
                </div>
                <form method="POST">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Nome</label>
                                        <input type="text" name="name" class="form-control" value="<?= $user['name'] ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Telefone</label>
                                        <input type="text" name="phone" class="form-control" value="<?= $user['phone'] ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" class="form-control" value="<?= $user['email'] ?>" required>
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

                                <div class="col-lg-12 col-sm-12 col-12 permissions-section">
                                    <div class="form-group">
                                        <label>Permissões</label>
                                        <div class="permissions-list">
                                            <table class="permissions-table">
                                                <thead>
                                                    <tr>
                                                        <th>Módulo</th>
                                                        <th>Visualizar</th>
                                                        <th>Adicionar</th>
                                                        <th>Editar</th>
                                                        <th>Excluir</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while ($row = $resultModules->fetch_assoc()) { ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($row['module']) ?></td>
                                                            <?php
                                                            $permissions = ['view', 'insert', 'update', 'delete'];
                                                            foreach ($permissions as $action) {
                                                                // Remova a verificação que força pelo menos uma permissão a ser verdadeira
                                                                $checked = (isset($row[$action]) && $row[$action] == 1) ? 'checked' : '';
                                                            ?>
                                                                <td>
                                                                    <label class="switch">
                                                                        <input type="hidden" name="permissions[<?= $row['idModule'] ?>][<?= $action ?>]" value="0">
                                                                        <input type="checkbox" name="permissions[<?= $row['idModule'] ?>][<?= $action ?>]" value="1" <?= $checked ?>>
                                                                        <span class="slider"></span>
                                                                    </label>
                                                                </td>
                                                            <?php } ?>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>


                                <div class="col-lg-12">
                                    <div class="button-container">
                                        <button class="btn btn-submit" type="submit">Atualizar</button>
                                        <a href="userlist.php" class="btn btn-cancel">Cancelar</a>
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