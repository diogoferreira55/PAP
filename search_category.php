<?php
include "db.config.php";

// Pegando o termo de pesquisa, se houver
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : "";

// Consultando os produtos com o termo de pesquisa
$sqlProducts = "SELECT *
                FROM product_category 
                WHERE (category LIKE ?)
                ORDER BY id
                LIMIT 20";

// Preparando a consulta
$stmt = $con->prepare($sqlProducts);
$searchWildcard = "%" . $searchTerm . "%";  // Adiciona o '%' para busca parcial

// Bind dos parâmetros com o tipo correto
$stmt->bind_param("s", $searchWildcard);

$stmt->execute();
$result = $stmt->get_result();

// Array para armazenar as categorias encontradas
$categories = [];

while ($row = $result->fetch_assoc()) {
    $cat = htmlspecialchars($row['category']);
    $subcat = "";
    $idcat = htmlspecialchars($row['id']);
    $idmaincat = htmlspecialchars($row['idmaincategory']);

    if ($idmaincat > 0) {
        $subcat = $cat;
        $cat = '';
        $sql = "SELECT category
                FROM product_category
                WHERE id = ? LIMIT 1";

        // Preparando nova consulta
        $stmtSubcat = $con->prepare($sql);
        $stmtSubcat->bind_param("i", $idmaincat);
        $stmtSubcat->execute();
        $resultAux = $stmtSubcat->get_result();

        if ($resultAux->num_rows > 0) {
            $ff = $resultAux->fetch_assoc();
            $cat = $ff['category'];
        }

        // Fechar o statement auxiliar
        $stmtSubcat->close();
    }

    // Armazenando as categorias no array
    $categories[] = [
        'category' => $cat,
        'subcat' => $subcat,
        'idcat' => $idcat,
        'idmaincat' => $idmaincat
    ];
}

// Fechar o statement principal
$stmt->close();

// Retornando a resposta em formato JSON
header('Content-Type: application/json');
echo json_encode($categories);
