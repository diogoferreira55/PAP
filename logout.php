<?php
session_start();
if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    session_unset(); // Remove todas as variáveis de sessão
    session_destroy(); // Destroi a sessão
    header("Location: index.php"); // Redireciona para a página de login
    exit();
}
?>
