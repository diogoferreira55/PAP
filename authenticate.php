<?php

$aux = true;

include "db.config.php";

// Verificar se os campos 'username' e 'password' estão presentes no POST
if (!isset($_POST['username'], $_POST['password'])) {
    header('Location: index.php?error=Por favor, preencha os campos de nome de utilizador e password!');
    exit;
}

// Sanitarizar as entradas
$username = trim($_POST['username']);
$password = trim($_POST['password']);

// Verificar se o banco de dados tem o utilizador
if ($stmt = $con->prepare('SELECT id, password FROM user WHERE email = ?')) {
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->store_result();

    // Se o utilizador for encontrado
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();

        // Verificar a senha fornecida
        if (password_verify($password, $hashed_password)) {
            session_regenerate_id();  // Regenera o ID da sessão para prevenir ataque de fixação de sessão

            $_SESSION['loggedin'] = true;
            $_SESSION['name'] = $username;
            $_SESSION['id'] = $id;

            // Redirecionar para a página principal
            header('Location: adduserlog.php');
            exit;
        } else {
            // Senha inválida
            header('Location: index.php?error=Palavra passe inválida!');
            exit;
        }
    } else {
        // Utilizador não encontrado
        header('Location: index.php?error=Utilizador não encontrado!');
        exit;
    }

    $stmt->close();
} else {
    // Erro ao preparar a consulta
    header('Location: index.php?error=Erro ao preparar a consulta SQL!');
    exit;
}

$con->close();
?>