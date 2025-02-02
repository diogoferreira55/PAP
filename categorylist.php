<?php

include "db.config.php";
$estouEm = 3;

$sql = "SELECT * FROM product_category";
$result = $con->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include "./rel.header.php"; ?>
</head>

<body>
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
                        <h4>Lista Categorias</h4>
                    </div>
                    <div class="page-btn">
                        <a href="addcategory.php" class="btn btn-added"><img src="/assets/img/icons/plus.svg" alt="img" class="me-1">Adicionar Categoria</a>
                    </div>
                </div>
                <div class="form-group">
                    <input type="text" id="search" placeholder="Pesquisar categoria...">
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table ">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Categoria</th>
                                        <th>Sub-Categoria</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="category-list">
                                    <!-- Category rows will be dynamically inserted here -->
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
            function fetchCategory(query) {
                $.ajax({
                    url: "search_category.php",
                    method: "GET",
                    data: {
                        search: query
                    },
                    dataType: "json",
                    success: function(data) {
                        let categoryList = $("#category-list");
                        categoryList.empty(); // Clear the existing categories

                        if (data.length > 0) {
                            // Loop through the fetched categories and display them
                            data.forEach(function(category) {
                                categoryList.append(`
                                    <tr>
                                        <td>${category.idcat}</td>
                                        <td>${category.category}</td>
                                        <td>${category.subcat}</td>
                                        <td>
                                            <a title="Edit" href="editcategory.php?id=${category.idcat}" class="btn btn-filters ms-auto">
                                                <img src="/assets/img/icons/edit.svg" alt="Edit">
                                            </a>
                                            <a title="Delete" href="javascript:void(0);" class="btn btn-filters ms-auto" onclick="confirmarExclusao(${category.idcat})">
                                                <img src="/assets/img/icons/delete.svg" alt="Delete">
                                            </a>
                                        </td>
                                    </tr>
                                `);
                            });
                        } else {
                            categoryList.append(`
                                <tr>
                                    <td colspan="4">Nenhuma categoria encontrada.</td>
                                </tr>
                            `);
                        }
                    },
                    error: function() {
                        alert("Erro ao buscar categorias.");
                    }
                });
            }

            $("#search").on("input", function() {
                const query = $(this).val();
                fetchCategory(query); // Pass the search query
            });

            // Load all categories when the page first loads
            fetchCategory("");
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
                reverseButtons: true,
                customClass: {
                    icon: 'swal-icon-custom'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'deletecategory.php?id=' + idcat;
                }
            });
        }
    </script>

    <style>
        .swal-icon-custom {
            color: #0d6efd !important;
        }

        .swal2-icon.swal2-warning {
            border-color: #0d6efd !important;
            color: #0d6efd !important;
        }

        .swal2-icon.swal2-warning .swal2-icon-content {
            color: #0d6efd !important;
        }
    </style>
</body>

</html>