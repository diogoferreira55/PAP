<?php
include "db.config.php";

$estouEm = 5;

$userId = $_SESSION['id'];
$sql = "SELECT * FROM user WHERE id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include "./rel.header.php"; ?>
    <style>
        .profile-card {
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            background: #fff;
        }

        .profile-card img {
            border-radius: 50%;
            width: 120px;
            height: 120px;
            object-fit: cover;
        }

        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-header h2 {
            margin-top: 15px;
            font-size: 24px;
            font-weight: bold;
        }

        .profile-details .form-group {
            margin-bottom: 22px;
        }

        .profile-details .form-group label {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        .profile-details .form-group p {
            font-size: 16px;
            color: #555;
            line-height: 1.5;
            margin-top: 5px;
        }



        .edit-btn {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .edit-btn .btn {
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
                        <h4>Perfil</h4>
                    </div>
                </div>

                <div class="card profile-card">
                    <!-- Cabeçalho do perfil -->
                    <div class="profile-header">
                        <img src="assets/img/logoreduzida.png" alt="Zoom Out Logo">
                        <h2><?php echo htmlspecialchars($user['name']); ?></h2>
                        <p class="text-muted">Perfil do Utilizador</p>
                    </div>

                    <div class="profile-details">
                        <div class="form-group">
                            <label><strong>Nome:</strong></label>
                            <p><?php echo htmlspecialchars($user['name']); ?></p>
                        </div>
                        <div class="form-group">
                            <label><strong>Email:</strong></label>
                            <p><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                        <div class="form-group">
                            <label><strong>Telefone:</strong></label>
                            <p><?php echo htmlspecialchars($user['phone']); ?></p>
                        </div>
                    </div>

                    <!-- Botão de edição -->
                    <div class="edit-btn">
                        <a href="editprofile.php?id=<?php echo $user['id']; ?>" class="btn btn-primary">Editar Perfil</a>
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