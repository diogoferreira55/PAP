<!DOCTYPE html>
<html lang="en">

<head>
    <?php include "./rel.header.php"; ?>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body,
        html {
            height: 100%;
            width: 100%;
            font-family: Arial, sans-serif;
            overflow: hidden;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f7f7f7;
        }

        .container2 {
            display: flex;
            width: 100%;
            height: 100vh;
        }

        .login-section {
            flex: 1;
            padding: 50px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: #ffffff;
        }

        .login-section h2 {
            font-size: 28px;
            margin-bottom: 10px;
            color: #333;
        }

        .login-section p {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
        }

        .login-section label {
            font-size: 14px;
            margin-bottom: 5px;
            color: #333;
        }

        .form-d {
            width: 50%;
        }

        .form-control {
            border-radius: 5px;
            margin-bottom: 20px;
            height: 45px;
            font-size: 14px;
        }

        .btn-login {
            width: 100%;
            background-color: #0066ff;
            color: #fff;
            border: none;
            height: 45px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-login:hover {
            background-color: #004ccc;
        }

        .right-section {
            flex: 1;
            background: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .right-section img {
            justify-content: center;
            align-items: center;
            max-width: 80%;
            height: auto;
        }

        /* Responsividade */
        @media (max-width: 1200px) {
            .form-d {
                width: 60%;
            }
        }

        @media (max-width: 992px) {

            .form-d {
                width: 70%;
            }
        }

        @media (max-width: 768px) {
            body {
                background-color: #ffffff;
            }

            .container2 {
                flex-direction: column;
                height: auto;
            }

            .login-section {
                flex: none;
                width: 100%;
                padding: 20px;
            }

            .right-section {
                display: none;
            }

            .right-section img {
                max-width: 100%;
                height: auto;
            }

            .form-d {
                width: 90%;
            }
        }

        @media (max-width: 576px) {
            .login-section h2 {
                font-size: 24px;
            }

            .login-section p {
                font-size: 14px;
                margin-bottom: 20px;
            }

            .form-d {
                width: 100%;
            }

            .form-control {
                font-size: 13px;
                height: 40px;
            }

            .btn-login {
                font-size: 14px;
                height: 40px;
            }
        }

        .error-message {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            font-size: 14px;
            padding: 10px;
            margin-bottom: 20px;
            text-align: center;
            width: 100%;
        }
    </style>
</head>

<body>
    <div class="container2">
        <div class="login-section">
            <img src="/assets/img/logoreduzida.png" alt="Zoom Out Logo" style="width: 50px; margin-bottom: 20px;">
            <h2>Faça login na sua conta</h2>
            <p>Bem vindo de volta! Por favor, insira seus dados.</p>

            <form class="form-d" action="./authenticate.php" method="post">
                <label for="email">Email</label>
                <input type="text" id="email" name="username" class="form-control" placeholder="Insira o seu email" required>

                <label for="password">Palavra passe</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Insira a sua palavra passe" required>
                <?php if (isset($_GET['error'])): ?>
                    <div class="error-message"><?= htmlspecialchars($_GET['error']) ?></div>
                <?php endif; ?>

                <button type="submit" class="btn-login">Login</button>
            </form>
        </div>

        <div class="right-section">
            <img src="/assets/img/logo.png" alt="Zoom Out Logo">
        </div>
    </div>

    <script src="/assets/js/jquery-3.6.0.min.js"></script>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>