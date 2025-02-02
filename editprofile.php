<?php
include "db.config.php";

$estouEm = 5;

// Verifica se o ID do usuário está presente
if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    // Consulta para pegar os dados do usuário
    $sql = "SELECT * FROM user WHERE id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Se o formulário for enviado
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $password = $_POST['password'];
        $conpassword = $_POST['conpassword'];

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
            exit; // Interrompe a execução
        }

        if (!empty($password) && $password !== $conpassword) {
            echo "<script>alert('As palavras-passe não coincidem. Por favor, tente novamente.'); window.history.back();</script>";
            exit;
        }


        // Se a senha for fornecida, atualiza a senha
        if (!empty($password)) {
            $password = password_hash($password, PASSWORD_DEFAULT); // Criptografar a senha
            $update_sql = "UPDATE user SET name = ?, email = ?, phone = ?, password = ? WHERE id = ?";
            $stmt = $con->prepare($update_sql);
            $stmt->bind_param("ssssi", $name, $email, $phone, $password, $userId);
        } else {
            $update_sql = "UPDATE user SET name = ?, email = ?, phone = ? WHERE id = ?";
            $stmt = $con->prepare($update_sql);
            $stmt->bind_param("sssi", $name, $email, $phone, $userId);
        }

        // Se o usuário enviou uma nova foto
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $photoName = $_FILES['photo']['name'];
            $photoTmp = $_FILES['photo']['tmp_name'];
            $photoPath = "uploads/" . basename($photoName);
            move_uploaded_file($photoTmp, $photoPath);

            // Atualiza a foto no banco
            $update_sql = "UPDATE user SET photo = ? WHERE id = ?";
            $stmt = $con->prepare($update_sql);
            $stmt->bind_param("si", $photoPath, $userId);
            $stmt->execute();
        }

        // Atualiza as informações no banco
        $stmt->execute();

        // Redireciona para o perfil após a atualização
        header("Location: profile.php");
        exit();
    }
} else {
    // Caso o ID não esteja presente, redireciona para a página de perfil
    header("Location: profile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include "./rel.header.php"; ?>
    <style>
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-header h2 {
            margin-top: 15px;
            font-size: 24px;
            font-weight: bold;
        }

        .profile-card img {
            border-radius: 50%;
            width: 120px;
            height: 120px;
            object-fit: cover;
        }

        .profile-card {
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            background: #fff;
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-group label {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        .form-group input,
        .form-group textarea {
            font-size: 16px;
            padding: 10px;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .form-group input[type="file"] {
            padding: 0;
        }

        .btn-save {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .btn-save .btn {
            font-size: 16px;
            padding: 8px 20px;
            width: auto;
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
                        <h4>Editar Perfil</h4>
                    </div>
                </div>
                <div class="card profile-card">
                    <!-- Cabeçalho do perfil -->
                    <div class="profile-header">
                        <img src="assets/img/logoreduzida.png" alt="Zoom Out Logo">
                        <h2><?php echo htmlspecialchars($user['name']); ?></h2>
                    </div>
                    <div class="card profile-card">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label><strong>Nome:</strong></label>
                                <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label><strong>Email:</strong></label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label><strong>Telefone:</strong></label>
                                <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Confirmar Password</label>
                                <input type="password" name="conpassword" class="form-control">
                                <!-- </div>
                            <div class="form-group">
                                <label><strong>Foto de Perfil:</strong></label>
                                <input type="file" name="photo" accept="image/*">
                                <p><strong>Foto atual:</strong> <img src="assets/img/logoreduzida.png" alt="Foto de Perfil" width="100"></p>
                            </div> -->
                                <div class="btn-save">
                                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                                </div>
                        </form>
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