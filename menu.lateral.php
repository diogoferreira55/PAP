<?php
$idUser = $_SESSION['id'];

function has_permission($idUser, $module_id, $action)
{
    global $con;
    $valid_actions = ['view', 'insert', 'edit', 'delete'];

    if (!in_array($action, $valid_actions)) {
        return false;
    }

    $stmt = $con->prepare("SELECT $action FROM user_modules WHERE idUser = ? AND idModule = ?");
    $stmt->bind_param("ii", $idUser, $module_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return $row && $row[$action] == 1;
}
?>

<div class="sidebar" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                <li class="<?php if ($estouEm == 1) echo 'active'; ?>">
                    <a href="home.php"><img src="assets/img/icons/dashboard.svg" alt="img"><span> Dashboard</span></a>
                </li>

                <?php if (has_permission($idUser, 4, 'view')) { ?>
                    <li class="submenu">
                        <a href="javascript:void(0);"><img src="/assets/img/icons/product.svg" alt="img"><span> Reservas</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <?php if (has_permission($idUser, 4, 'view')) { ?>
                                <li><a href="reservationlist.php" class="<?php if ($estouEm == 4) echo 'active'; ?>"> Reservas</a></li>
                            <?php } ?>
                            <?php if (has_permission($idUser, 4, 'view')) { ?>
                                <li><a href="reservation_calendar.php" class="<?php if ($estouEm == 6) echo 'active'; ?>"> Calendário</a></li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>
                
                <?php if (has_permission($idUser, 3, 'view')) { ?>
                    <li class="submenu">
                        <a href="javascript:void(0);"><img src="/assets/img/icons/product.svg" alt="img"><span> Produtos</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <?php if (has_permission($idUser, 3, 'view')) { ?>
                                <li><a href="productlist.php" class="<?php if ($estouEm == 2) echo 'active'; ?>"> Produtos</a></li>
                            <?php } ?>
                            <?php if (has_permission($idUser, 3, 'view')) { ?>
                                <li><a href="categorylist.php" class="<?php if ($estouEm == 3) echo 'active'; ?>"> Categorias</a></li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>

                <?php if (has_permission($idUser, 1, 'view')) { ?>
                    <li class="submenu">
                        <a href="javascript:void(0);"><img src="/assets/img/icons/users1.svg" alt="img"><span> Clientes</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <?php if (has_permission($idUser, 1, 'view')) { ?>
                                <li><a href="clientlist.php" class="<?php if ($estouEm == 9) echo 'active'; ?>"> Clientes</a></li>
                            <?php } ?>
                            <?php if (has_permission($idUser, 1, 'view')) { ?>
                                <li><a href="client_typelist.php" class="<?php if ($estouEm == 10) echo 'active'; ?>"> Tipos Clientes</a></li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>

                <?php if (has_permission($idUser, 2, 'view')) { ?>
                    <li class="submenu">
                        <a href="javascript:void(0);"><img src="/assets/img/icons/users1.svg" alt="img"><span> Utilizadores </span> <span class="menu-arrow"></span></a>
                        <ul>
                            <?php if (has_permission($idUser, 2, 'view')) { ?>
                                <li><a href="userlist.php" class="<?php if ($estouEm == 11) echo 'active'; ?>"> Utilizadores</a></li>
                            <?php } ?>
                            <?php if (has_permission($idUser, 2, 'view')) { ?>
                                <li><a href="user_typelist.php" class="<?php if ($estouEm == 12) echo 'active'; ?>"> Tipos Utilizadores</a></li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>
                    <li class="<?php if ($estouEm == 5) echo 'active'; ?>">
                        <a href="profile.php"><img src="assets/img/icons/product.svg" alt="img"><span> Perfil</span></a>
                    </li>
            </ul>
        </div>
    </div>
</div>