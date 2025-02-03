<?php

include "db.config.php";

$idUser = $_SESSION['id'];

include "permission.php";

$canDelete = hasPermission($idUser, 1, 'delete', $con);
$canEdit = hasPermission($idUser, 1, 'update', $con);
$canView = hasPermission($idUser, 3, 'view', $con);

if ($canView == 0) {
    header("Location: no_permission.php");
    exit;
}
$canAction = $canEdit || $canDelete;

$estouEm = 2;

/*
$sql = "SELECT p.*, rp.idProduct, rs.status
        FROM product p
        join reservation_product rp on p.id = rp.idProduct
        JOIN reservation r ON rp.idReservation = r.id
        JOIN reservation_status rs ON r.idStatus = rs.id
WHERE r.orderDateStart<='2025-02-03' AND r.orderDateEnd<='2025-02-03'
GROUP BY p.id";
$result = $con->query($sql);
*/
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include "./rel.header.php"; ?>
    <style>
        #botFilter {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
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
                        <h4>Lista Produtos</h4>
                    </div>
                    <div class="page-btn">
                        <a href="addproduct.php" class="btn btn-added"><img src="/assets/img/icons/plus.svg" alt="img" class="me-1">Adicionar Produto</a>
                    </div>
                </div>


                <div class="page-header">
                    <?php
                    $dataAtual = date('Y-m-d');
                    ?>
                    <div>
                        <div class="form-group">
                            <label>Filtro Data Inicial</label>
                            <input type="datetime-local" id="dateStart" name="dateStart" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Filtro Data Final</label>
                            <input type="datetime-local" id="dateEnd" name="dateEnd" class="form-control" required>
                        </div>
                        <input type=button class="filter" id=botFilter value="Filtrar">
                    </div>
                </div>
                <div class="form-group">
                    <input type="text" id="search" placeholder="Pesquisar product...">
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Imagem</th>
                                        <th>ID</th>
                                        <th>Item</th>
                                        <th>Alocado</th>
                                        <th>Valor</th>
                                        <th>Estado</th>
                                        <?php if ($canAction) { ?>
                                            <th>Ações</th>
                                        <?php } ?>
                                    </tr>
                                </thead>
                                <tbody id="product-list">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/assets/js/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            var canDelete = <?php echo $canDelete ? 'true' : 'false'; ?>;
            var canEdit = <?php echo $canEdit ? 'true' : 'false'; ?>;
            var canView = <?php echo $canView ? 'true' : 'false'; ?>;
            var canAction = <?php echo $canAction ? 'true' : 'false'; ?>;

            function fetchProduct(query, dateStart = '', dateEnd = '') {
                $.ajax({
                    url: "search_product.php",
                    method: "GET",
                    data: {
                        search: query,
                        dateStart: $("#dateStart").val(),
                        dateEnd: $("#dateEnd").val(),
                    },
                    dataType: "json",
                    success: function(data) {
                        let productList = $("#product-list");
                        productList.empty();

                        if (data.length > 0) {
                            data.forEach(function(product) {
                                productList.append(
                                    `<tr>
                                        <td>
                                            ${product.img ? `<img src="${product.img}" alt="Product Image" style="width: 50px; height: 50px; object-fit: cover;">` : 'No Image'}
                                        </td>
                                        <td>${product.idProduct}</td>
                                        <td>${product.item}</td>
                                        <td>${product.location}</td>
                                        <td>${product.value}</td>
                                        <td>${product.status}
                                        ${canAction ? 
                                            `<td>
                                                ${canView ? 
                                                    `<a title="Detalhes" href="product-details.php?id=${product.id}" class="btn btn-filters ms-auto">
                                                        <img src="/assets/img/icons/eye.svg" alt="Editar">
                                                    </a>` : ''}
                                                ${canEdit ? 
                                                    `<a title="Editar" href="editproduct.php?id=${product.id}" class="btn btn-filters ms-auto">
                                                        <img src="/assets/img/icons/edit.svg" alt="Editar">
                                                    </a>` : ''}
                                                ${canDelete ? 
                                                    `<a title="Excluir" href="javascript:void(0);" class="btn btn-filters ms-auto" onclick="confirmarExclusao(${product.id})">
                                                        <img src="/assets/img/icons/delete.svg" alt="Excluir">
                                                    </a>` : ''}
                                            </td>`
                                        : ''}
                                    </tr>`
                                );
                            });

                        } else {
                            productList.append(
                                `<tr>
                                    <td colspan="8">Nenhum produto encontrado.</td>
                                </tr>`
                            );
                        }
                    },
                    error: function() {
                        alert("Erro ao buscar produtos.");
                    }
                });
            }
            $("#search").on("input", function() {
                const query = $(this).val();
                fetchProduct(query);
            });
            $("#botFilter").on("click", function() {
                const query = $("#search").val(); // Valor da pesquisa (se houver)
                const dateStart = $("#dateStart").val(); // Valor da data inicial
                const dateEnd = $("#dateEnd").val(); // Valor da data final

                // Chama a função de busca com a pesquisa e os filtros de data
                fetchProduct(query, dateStart, dateEnd);
            });



            fetchProduct("");
        });
    </script>
    <script src="/assets/js/feather.min.js"></script>
    <script src="/assets/js/jquery.slimscroll.min.js"></script>
    <script src="/assets/js/jquery.dataTables.min.js"></script>
    <script src="/assets/js/dataTables.bootstrap4.min.js"></script>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/plugins/select2/js/select2.min.js"></script>
    <script src="/assets/plugins/sweetalert/sweetalert2.all.min.js"></script>
    <script src="/assets/plugins/sweetalert/sweetalerts.min.js"></script>
    <script src="/assets/js/script.js"></script>

    <script>
        function confirmarExclusao(id) {
            Swal.fire({
                title: 'Tem certeza?',
                text: "Esta ação não pode ser desfeita!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Não, cancelar!',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'deleteproduct.php?id=' + id;
                }
            });
        }
    </script>
</body>

</html>