<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ler Código QR</title>
    <script src="https://unpkg.com/html5-qrcode"></script>
</head>
<body>
    <h2>Escaneie o código QR</h2>

    <!-- Botão para ativar o scanner -->
    <button type="button" id="openCamera" class="btn btn-submit">Escanear Código</button>

    <!-- Área para mostrar o scanner (com câmera pequena) -->
    <div id="reader" style="width: 250px; height: 250px; display: none;"></div>

    <!-- Área para mostrar o código escaneado -->
    <div id="qrResult" style="margin-top: 20px;"></div>

    <script>
        // Função chamada quando o botão é clicado para iniciar o scanner
        document.getElementById('openCamera').addEventListener('click', function() {
            // Exibe a área do scanner
            document.getElementById('reader').style.display = 'block';

            // Inicia o scanner de QR Code
            startScanner();
        });

        // Função para iniciar o scanner
        function startScanner() {
            const qrScanner = new Html5QrcodeScanner("reader", {
                fps: 10,         // Frames por segundo
                qrbox: 250       // Tamanho da caixa de leitura do código
            });

            // Função chamada quando o código é detectado
            function onScanSuccess(decodedText) {
                console.log("Código detectado: " + decodedText);
                search_products(decodedText);  // Envia o código para buscar o produto
                stopScanner(); // Para o scanner após encontrar o código
            }

            // Inicia o scanner
            qrScanner.render(onScanSuccess);
        }

        // Função para enviar o código escaneado
        function search_products(code) {
            console.log("Enviando código para busca: ", code);
            document.getElementById('qrResult').innerHTML = "Código escaneado: " + code;
            // Aqui você pode enviar o código para o backend ou realizar outra ação
        }

        // Função para parar o scanner (quando já encontrar um código ou se o usuário quiser parar)
        function stopScanner() {
            const qrScanner = new Html5QrcodeScanner("reader");
            qrScanner.clear(); // Para o scanner
            document.getElementById('reader').style.display = 'none'; // Esconde o scanner
        }
    </script>
</body>
</html>
