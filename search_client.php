<?php
include "db.config.php";

// Pegando o termo de pesquisa, se houver
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : "";

// Consultando os produtos com o termo de pesquisa
$sqlClients = "SELECT c.*, ct.category
                FROM client c 
                LEFT JOIN client_type ct ON c.idClientType = ct.id 
                WHERE (responsableName LIKE ? 
                OR companyName LIKE ? 
                OR contact LIKE ? 
                OR category LIKE ?)
                ORDER BY id
                LIMIT 20";

// Preparando a consulta
$stmt = $con->prepare($sqlClients);
$searchWildcard = "%" . $searchTerm . "%";  // Adiciona o '%' para busca parcial

// Bind dos parâmetros com o tipo correto
$stmt->bind_param(
    "ssss",
    $searchWildcard,
    $searchWildcard, 
    $searchWildcard,
    $searchWildcard  
);

$stmt->execute();
$result = $stmt->get_result();

$clients = [];
while ($row = $result->fetch_assoc()) {
    $clients[] = $row;
}

header('Content-Type: application/json');
echo json_encode($clients);
