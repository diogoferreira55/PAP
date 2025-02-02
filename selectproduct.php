<?php
include "db.config.php";

$idReservation = intval($_GET['idReservation']);
$msgOp = "";

$sqlReservation = "SELECT r.id, c.companyName, c.email, r.orderDateStart, r.totalValue
                   FROM reservation r
                   INNER JOIN client c ON r.idclient = c.id
                   WHERE r.id = '$idReservation'";
$result = $con->query($sqlReservation);

$reservation = $result && $result->num_rows > 0 ? $result->fetch_assoc() : null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idProducts = $_POST['idProduct'] ?? [];
    $totalValue = 0;

    // Verificar e aplicar descontos da reserva
    $sqlReservationDiscount = "SELECT discountMultDays, discountTotalValue FROM reservation WHERE id = '$idReservation'";
    $resultDiscount = $con->query($sqlReservationDiscount);
    $discountData = $resultDiscount && $resultDiscount->num_rows > 0 ? $resultDiscount->fetch_assoc() : [];
    $discountMultDays = floatval($discountData['discountMultDays'] ?? 0);
    $discountTotalValue = floatval($discountData['discountTotalValue'] ?? 0);

    // Inserir produtos selecionados e calcular o valor total
    foreach ($idProducts as $productId) {
        // Evitar duplicidade na inserção de produtos para a mesma reserva
        $checkProduct = "SELECT id FROM reservation_product WHERE idReservation = '$idReservation' AND idProduct = '$productId'";
        $checkResult = $con->query($checkProduct);

        if ($checkResult && $checkResult->num_rows === 0) {
            $sqlInsertProduct = "INSERT INTO reservation_product (idReservation, idProduct) VALUES ('$idReservation', '$productId')";
            if ($con->query($sqlInsertProduct) !== TRUE) {
                $msgOp = "ERROR";
                break;
            }
        }

        // Calcular o valor total da reserva
        $sqlValue = "SELECT discounted_value FROM product WHERE id = '$productId'";
        $resultValue = $con->query($sqlValue);
        if ($resultValue && $resultValue->num_rows > 0) {
            $product = $resultValue->fetch_assoc();
            $totalValue += floatval($product['discounted_value']);
        }
    }

    // Aplicar descontos
    if ($discountMultDays > 0) {
        $totalValue -= ($totalValue * ($discountMultDays / 100));
    }

    if ($discountTotalValue > 0) {
        $totalValue -= ($totalValue * ($discountTotalValue / 100));
    }

    // Atualizar o valor total da reserva
    $sqlUpdate = "UPDATE reservation SET totalValue = '$totalValue' WHERE id = '$idReservation'";
    if ($con->query($sqlUpdate) === TRUE) {
        $msgOp = "OK";
    } else {
        $msgOp = "ERROR";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include "./rel.header.php"; ?>
    <style>
        /* Estilização */
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        .table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .btn-submit {
            margin-top: 20px;
            padding: 12px 24px;
            font-size: 16px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="main-wrapper">
        <?php include "./menu.header.php"; ?>
        <?php include "./menu.lateral.php"; ?>

        <div class="page-wrapper">
            <div class="content">
                <div class="page-header">
                    <div class="page-title">
                        <h4>Detalhes da Reserva</h4>
                    </div>
                </div>
                <?php if ($reservation): ?>
                    <div class="reservation-details">
                        <p><strong>ID da Reserva:</strong> <?php echo $reservation['id']; ?></p>
                        <p><strong>Cliente:</strong> <?php echo htmlspecialchars($reservation['companyName']); ?></p>
                        <p><strong>Data da Reserva:</strong> <?php echo date("d/m/Y", strtotime($reservation['orderDateStart'])); ?></p>
                    </div>
                <?php else: ?>
                    <div class="error-message">
                        <p>Erro ao carregar os detalhes da reserva.</p>
                    </div>
                <?php endif; ?>
                <br>
                <div class="page-header">
                    <div class="page-title">
                        <h4>Selecionar Produtos</h4>
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
                                document.getElementById('reader').style.display = 'none'; // Esconde o scanner
                                document.getElementById('openCamera').style.display = 'block'; // Mostra o botão novamente
                                scannerRunning = false; // Libera para escanear novamente
                            }).catch((err) => console.error("Erro ao parar scanner:", err));
                        }
                    }

                    // Permitir edição manual após a leitura
                    document.getElementById('search').addEventListener('keydown', function() {
                        this.removeAttribute('readonly'); // Garante que possa ser editado
                    });
                </script>
                <form action="" method="POST">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Selecionar</th>
                                    <th>Item</th>
                                    <th>Marca</th>
                                    <th>Modelo</th>
                                    <th>Valor</th>
                                </tr>
                            </thead>
                            <tbody id="product-list">
                                <!-- Produtos carregados via AJAX -->
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" class="btn btn-submit">Criar reserva</button>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/jquery-3.6.0.min.js"></script>
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
        $(document).ready(function() {
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
                        productList.empty();
                        if (data.length > 0) {
                            data.forEach(function(product) {
                                productList.append(`
                                <tr>
                                    <td><input type="checkbox" name="idProduct[]" value="${product.id}"></td>
                                    <td>${product.item}</td>
                                    <td>${product.brand}</td>
                                    <td>${product.model}</td>
                                    <td>${product.discounted_value}</td>
                                </tr>
                            `);
                            });
                        } else {
                            productList.append(`<tr><td colspan="5">Nenhum produto encontrado.</td></tr>`);
                        }
                    },
                    error: function() {
                        alert("Erro ao buscar produtos.");
                    }
                });
            }

            $("#search").on("input", function() {
                fetchProduct($(this).val());
            });

            fetchProduct("");
        });
    </script>
    <script>
        <?php
        if ($msgOp == "OK") {
            echo "
        Swal.fire({
            title: 'Reserva feita com sucesso!',
            text: 'Um email foi enviado para o email do cliente.',
            icon: 'success',
            showCancelButton: true,
            confirmButtonText: 'Adicionar Outra',
            cancelButtonText: 'Ir para a Lista'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'teste_email.php?idReservation=$idReservation';
            } else {
                window.location.href = 'reservationlist.php';
            }
        });";
        } elseif ($msgOp == "ERROR") {
            error_log("Erro ao adicionar produto: " . $con->error);
            echo "alert('Erro ao adicionar produto. Verifique os logs.');";
        }
        ?>
    </script>
</body>

</html>