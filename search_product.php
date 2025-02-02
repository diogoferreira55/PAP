<?php
include "db.config.php";

// Pegando o termo de pesquisa, se houver
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : "";

// Consultando os produtos com o termo de pesquisa
$sqlProducts = "SELECT p.*, 
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
        WHERE (
            p.id LIKE ? OR pc1.category LIKE ? OR pc2.category LIKE ?
            OR p.item LIKE ? OR p.brand LIKE ? OR p.model LIKE ?
            OR p.location LIKE ? OR p.value LIKE ? OR 
            CASE 
                WHEN rs.status = 'Reservado' THEN 'Reservado'
                WHEN rs.status = 'Em Espera' AND r.orderDateStart > NOW() THEN 'Disponível'
                WHEN rs.status = 'Em Espera' AND r.orderDateStart <= NOW() THEN 'Reservado'
                ELSE 'Disponível'
            END LIKE ?
        )
        ORDER BY p.id 
        LIMIT 20";

// Preparando a consulta
$stmt = $con->prepare($sqlProducts);
$searchWildcard = "%" . $searchTerm . "%";
$stmt->bind_param("sssssssss", $searchWildcard, $searchWildcard, $searchWildcard, $searchWildcard, $searchWildcard, $searchWildcard, $searchWildcard, $searchWildcard, $searchWildcard);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = [
            'id' => $row['id'],
            'idProduct' => $row['idProduct'],
            'item' => $row['item'] ?? 'N/A',
            'brand' => $row['brand'] ?? 'N/A',
            'model' => $row['model'] ?? 'N/A',
            'location' => $row['location'] ?? 'N/A',
            'discounted_value' => $row['discounted_value'] ?? 'N/A',
            'value' => $row['value'] ?? 'N/A',
            'status' => $row['status'] ?? 'N/A',
            'img' => $row['img'] ?? null
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($products);
?>
