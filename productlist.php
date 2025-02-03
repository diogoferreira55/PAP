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

$sql = "SELECT p.*, 
        pc1.category AS category, 
        pc2.category AS subCategory,
        CASE 
            WHEN rs.status = 'Reservado' THEN 'Reservado'
            WHEN rs.status = 'Em Espera' AND r.orderDateStart > NOW() THEN 'Disponível'
            WHEN rs.status = 'Em Espera' AND r.orderDateStart <= NOW() THEN 'Reservado'
            ELSE 'Disponível' 
        END AS status
        FROM product p
        LEFT JOIN product_category pc2 ON p.idSubCategory = pc2.id
        LEFT JOIN product_category pc1 ON pc2.idmaincategory = pc1.id
        LEFT JOIN reservation_product rp ON p.id = rp.idProduct
        LEFT JOIN reservation r ON rp.idReservation = r.id
        LEFT JOIN reservation_status rs ON r.idStatus = rs.id
        WHERE (rs.status IS NULL OR rs.status NOT IN ('Cancelado', 'Concluído'));";
$result = $con->query($sql);



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include "./rel.header.php"; ?>
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
                <style>
                    #openCamera {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: 5px;
                        margin-top: 20px;
                        margin-bottom: 20px;
                    }
                </style>

                <button type="button" id="openCamera" class="btn btn-submit"><img src="/assets/img/icons/scanner.svg" alt="img" class="me-1">Ler Código</button>

                <!-- Área para mostrar o scanner (camara pequena) -->
                <div id="reader" style="width: 300; height: 200; display: none;"></div>

                <!-- Área para mostrar o código escaneado -->
                <div id="qrResult"></div>

                <!-- Campo de pesquisa -->
                <div class="form-group">
                    <input type="text" id="search" placeholder="Pesquisar produto...">
                </div>

                <script src="https://unpkg.com/html5-qrcode"></script>
                <script>
                    let qrScanner = null; // Variável para armazenar o scanner
                    let scannerRunning = false; // Flag para evitar múltiplas leituras

                    document.getElementById('openCamera').addEventListener('click', function() {
                        document.getElementById('reader').style.display = 'block'; // Exibe o scanner
                        document.getElementById('openCamera').style.display = 'none'; // Esconde o botão
                        startScanner();
                    });

                    function startScanner() {
                        if (scannerRunning) return; // Evita inicializar múltiplas vezes
                        scannerRunning = true;

                        qrScanner = new Html5Qrcode("reader");

                        qrScanner.start({
                                facingMode: "environment"
                            }, // Usa a câmera traseira por padrão
                            {
                                fps: 10,
                                qrbox: 250
                            },
                            function onScanSuccess(decodedText) {
                                if (!scannerRunning) return; // Se já parou, não faz nada
                                scannerRunning = false; // Bloqueia leituras repetidas

                                let searchField = document.getElementById('search');
                                searchField.value = decodedText; // Preenche com o código escaneado
                                searchField.removeAttribute("readonly"); // Garante que o usuário pode editar
                                searchField.focus(); // Mantém o cursor ativo
                                searchField.setSelectionRange(searchField.value.length, searchField.value.length); // Põe o cursor no final

                                setTimeout(() => {
                                    $("#search").trigger("input"); // Dispara a busca automaticamente
                                }, 100);

                                stopScanner(); // Para o scanner após a leitura
                            }
                        );
                    }

                    function stopScanner() {
                        if (qrScanner) {
                            qrScanner.stop().then(() => {
                                qrScanner.clear();
                                document.getElementById('reader').style.display = 'none'; 
                                document.getElementById('openCamera').style.display = 'block'; 
                                scannerRunning = false; // Libera para escanear novamente
                            }).catch((err) => console.error("Erro ao parar scanner:", err));
                        }
                    }

                    // Permitir edição manual após a leitura
                    document.getElementById('search').addEventListener('keydown', function() {
                        this.removeAttribute('readonly'); // Garante que possa ser editado
                    });
                </script>
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
                                    <!-- Product rows will be dynamically inserted here -->
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
            var canView = <?php echo $canEdit ? 'true' : 'false'; ?>;
            var canAction = <?php echo $canAction ? 'true' : 'false'; ?>;

            function fetchProduct(query) {
                $.ajax({
                    url: "search_product.php",
                    method: "GET",
                    data: {
                        search: query
                    },
                    dataType: "json",
                    success: function(data) {
                        let productList = $("#product-list");
                        productList.empty(); // Clear the existing products

                        if (data.length > 0) {
                            // Loop through the fetched products and display them
                            data.forEach(function(product) {
                                let status = product.status || "N/A"; // Exibe N/A se não houver status definido

                                productList.append(`
                                <tr>
                                    <td>
                                        ${product.img ? `<img src="${product.img}" alt="Product Image" style="width: 50px; height: 50px; object-fit: cover;">` : 'No Image'}
                                    </td>
                                    <td>${product.idProduct}</td>
                                    <td>${product.item}</td>
                                    <td>${product.location}</td>
                                    <td>${product.value}</td>
                                    <td>${status}</td> <!-- Adicionando status -->
                                    ${canAction ? `
                                        <td>
                                            ${canView ? `
                                                <a title="Detalhes" href="product-details.php?id=${product.id}" class="btn btn-filters ms-auto">
                                                    <img src="/assets/img/icons/eye.svg" alt="Editar">
                                                </a>
                                            ` : ''}
                                            ${canEdit ? `
                                                <a title="Editar" href="editproduct.php?id=${product.id}" class="btn btn-filters ms-auto">
                                                    <img src="/assets/img/icons/edit.svg" alt="Editar">
                                                </a>
                                            ` : ''}
                                            ${canDelete ? `
                                                <a title="Excluir" href="javascript:void(0);" class="btn btn-filters ms-auto" onclick="confirmarExclusao(${product.id})">
                                                    <img src="/assets/img/icons/delete.svg" alt="Excluir">
                                                </a>
                                            ` : ''}
                                        </td>
                                    ` : ''}
                                </tr>
                            `);
                            });

                        } else {
                            // Display a message if no products are found
                            productList.append(`
                        <tr>
                            <td colspan="8">Nenhum produto encontrado.</td>
                        </tr>
                    `);
                        }
                    },
                    error: function() {
                        alert("Erro ao buscar produtos.");
                    }
                });
            }
            // Trigger the search function when user types
            $("#search").on("input", function() {
                const query = $(this).val();
                fetchProduct(query); // Pass the search query
            });

            // Load all products when the page first loads
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