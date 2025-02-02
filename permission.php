<?php
// permission.php

// Função para verificar a permissão de um usuário em um módulo específico
function hasPermission($user_id, $module_id, $action, $con)
{
    $sql = "SELECT `$action` FROM user_modules WHERE idUser = ? AND idModule = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ii", $user_id, $module_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row[$action] == 1;
    }
    return false;
}

