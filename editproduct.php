<?php

include "db.config.php";

include "permission.php";

$id = intval($_GET['id']);
$idUser = $_SESSION['id'];
$canEdit = hasPermission($idUser, 3, 'update', $con);

if ($canEdit == 0) {
    header("Location: no_permission.php");
    exit;
}



if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "utilizador não especificado.";
    exit;
}

$estouEm = 2;
$categories = [];
$subcategories = [];
$idCategorySelected = '';
$product = [];

$varCats_SubCats = 'const categorias = {';
$sqlCategories = "SELECT id, category FROM product_category WHERE idmaincategory = 0";
$resultCategories = $con->query($sqlCategories);
$categories = $resultCategories->fetch_all(MYSQLI_ASSOC);

foreach ($categories as $category):
    $varCats_SubCats .= '"' . $category['id'] . '": [';
    //htmlspecialchars($category['category'])

    $sqlSubcategories = "SELECT id, category FROM product_category WHERE idmaincategory = ?";
    $stmtSubcategories = $con->prepare($sqlSubcategories);
    $stmtSubcategories->bind_param("i", $category['id']);
    $stmtSubcategories->execute();
    $resultSubcategories = $stmtSubcategories->get_result();
    $subcategories = $resultSubcategories->fetch_all(MYSQLI_ASSOC);
    foreach ($subcategories as $subcategory) {
        $varCats_SubCats .= '{ id: "' . $subcategory['id'] . ' ", titulo: "' . htmlspecialchars($subcategory['category']) . '" },';
    }

    $varCats_SubCats .= '],';
endforeach;
$varCats_SubCats .= '};';

$sql = "SELECT p.*, pc.idmaincategory AS idCategory, pc.id AS idSubCategory 
        FROM product p 
        LEFT JOIN product_category pc ON p.idSubCategory = pc.id 
        WHERE p.id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
    $idCategorySelected = $product['idCategory'];
    $idSubCategorySelected = $product['idSubCategory'];
} else {
    echo "Produto não encontrado.";
    exit;
}
// // Consultando dados do utilizador
// $sql = "SELECT * FROM product WHERE id = ?";
// $stmt = $con->prepare($sql);
// $stmt->bind_param("i", $id);
// $stmt->execute();
// $result = $stmt->get_result();

// if ($result->num_rows > 0) {
//     $product = $result->fetch_assoc();
// } else {
//     echo "produto não encontrado.";
//     exit;
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idProduct = $_POST['idProduct'] ?? '';
    $item = $_POST['item'] ?? '';
    $idCategory = $_POST['idCategory'] ?? '';
    $idSubCategory = $_POST['idSubCategory'] ?? '';
    $brand = $_POST['brand'] ?? '';
    $model = $_POST['model'] ?? '';
    $seriesNum = $_POST['seriesNum'] ?? '';
    $location = $_POST['location'] ?? '';
    $accessories = $_POST['accessories'] ?? '';
    $code = $_POST['code'] ?? '';
    $observations = $_POST['observations'] ?? '';
    $value = $_POST['value'] ?? 0;
    $discount = $_POST['discount'] ?? 0;
    $discounted_value = $value - ($value * ($discount / 100));
    $sql_update = "UPDATE product SET idProduct = ?,idCategory=?,idSubCategory=?, item = ?, brand = ?,model=?,seriesNum=?,location=?,accessories=?,code=?,observations=?,value=?,discount=?,discounted_value=? WHERE id = ?";
    $stmt_update = $con->prepare($sql_update);
    $stmt_update->bind_param("siissssssssdddi", $idProduct, $idCategory, $idSubCategory, $item, $brand, $model, $seriesNum, $location, $accessories, $code, $observations, $value, $discount, $discounted_value, $id);
    $stmt_update->execute();

    header("Location: productlist.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include "./rel.header.php"; ?>
    <style>
        .button-container {
            display: flex;
            justify-content: flex-start;
            gap: 20px;
            margin-top: 30px;
        }

        .btn-submit,
        .btn-cancel {
            width: 180px;
            height: 60px;
            text-align: center;
            font-size: 18px;
            border-radius: 8px;
            border: none;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
    </style>
    <script>
        <?php echo $varCats_SubCats; ?>



        // Função para atualizar o select de subcategorias
        function atualizarSubcategorias() {
            const categoriaSelect = document.getElementById("idCategory");
            const subcategoriaSelect = document.getElementById("idSubCategory");

            // Obtém o ID da categoria selecionada
            const categoriaSelecionada = categoriaSelect.value;

            // Limpa as opções anteriores de subcategorias
            subcategoriaSelect.innerHTML = "";

            // Verifica se há subcategorias para a categoria selecionada
            if (categoriaSelecionada in categorias) {
                // Adiciona as novas opções de subcategorias
                categorias[categoriaSelecionada].forEach(subcategoria => {
                    const option = document.createElement("option");
                    option.value = subcategoria.id.trim(); // Remove espaços em branco no ID
                    option.setAttribute("data-titulo", subcategoria.titulo); // Define o título como um atributo
                    option.textContent = subcategoria.titulo; // Exibe o título no select
                    subcategoriaSelect.appendChild(option);
                });

                // Seleciona a subcategoria correta se existir
                const subcategoriaSelecionada = subcategoriaSelect.getAttribute("data-selected");
                if (subcategoriaSelecionada) {
                    subcategoriaSelect.value = subcategoriaSelecionada;
                }
            } else {
                // Adiciona uma opção padrão caso não existam subcategorias
                const option = document.createElement("option");
                option.value = "";
                option.textContent = "Nenhuma subcategoria disponível";
                subcategoriaSelect.appendChild(option);
            }
        }

        function capturarValores() {
            const categoriaSelect = document.getElementById("idCategory");
            const subcategoriaSelect = document.getElementById("idSubCategory");

            // Categoria selecionada
            const categoriaId = categoriaSelect.value;
            const categoriaTitulo = categoriaSelect.options[categoriaSelect.selectedIndex]?.text || "Não selecionada";

            // Subcategoria selecionada
            const subcategoriaId = subcategoriaSelect.value;
            const subcategoriaTitulo = subcategoriaSelect.options[subcategoriaSelect.selectedIndex]?.getAttribute("data-titulo") || "Não selecionada";

            alert(`Categoria: ${categoriaTitulo} (ID: ${categoriaId})\nSubcategoria: ${subcategoriaTitulo} (ID: ${subcategoriaId})`);
        }

        // Executa a atualização de subcategorias ao carregar a página, caso haja uma categoria e subcategoria selecionadas
        document.addEventListener("DOMContentLoaded", function() {
            const categoriaSelect = document.getElementById("idCategory");
            const subcategoriaSelect = document.getElementById("idSubCategory");

            // Define o valor da subcategoria como um atributo de data para ser selecionado posteriormente
            subcategoriaSelect.setAttribute("data-selected", "<?= $idSubCategorySelected ?>");

            // Atualiza as subcategorias se uma categoria já estiver selecionada
            if (categoriaSelect.value) {
                atualizarSubcategorias();
            }
        });
    </script>
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
                        <h4>Editar Produto</h4>
                    </div>
                </div>
                <form method="POST">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Id Produto</label>
                                        <input type="text" name="idProduct" class="form-control" value="<?= $product['idProduct'] ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Item</label>
                                        <input type="text" name="item" class="form-control" value="<?= $product['item'] ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Categoria</label>
                                        <select name="idCategory" id="idCategory" class="select" required onchange="atualizarSubcategorias()">
                                            <option value="">Selecione uma categoria</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?= $category['id'] ?>"
                                                    <?= $idCategorySelected == $category['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($category['category']) ?>
                                                </option>

                                                <?php
                                                // solucao apenas 1 campo para mostrar cats e sub cats 
                                                /*$idCategory = $category['id'];
                                                $sqlSubcategories = "SELECT id, category FROM product_category WHERE idmaincategory = ?";
                                                $stmtSubcategories = $con->prepare($sqlSubcategories);
                                                $stmtSubcategories->bind_param("i", $idCategory);
                                                $stmtSubcategories->execute();
                                                $resultSubcategories = $stmtSubcategories->get_result();
                                                $subcategories = $resultSubcategories->fetch_all(MYSQLI_ASSOC);

                                                foreach ($subcategories as $subcategory) {
                                                    echo '<option value="' . $subcategory['id'] . '"> - ' . htmlspecialchars($subcategory['category']) . '</option>';
                                                }*/

                                                ?>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Subcategoria</label>
                                        <select name="idSubCategory" id="idSubCategory" class="select" required>
                                            <option value="">Selecione uma subcategoria</option>
                                            <?php /*foreach ($subcategories as $subcategory): ?>
                                                <option value="<?= $subcategory['id'] ?>">
                                                    <?= htmlspecialchars($subcategory['category']) ?>
                                                </option>
                                            <?php endforeach;*/ ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Marca</label>
                                        <input type="text" name="brand" class="form-control" value="<?= $product['brand'] ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Modelo</label>
                                        <input type="text" name="model" class="form-control" value="<?= $product['model'] ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Numero de Serie</label>
                                        <input type="text" name="seriesNum" class="form-control" value="<?= $product['seriesNum'] ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Armazenado em</label>
                                        <input type="text" name="location" class="form-control" value="<?= $product['location'] ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Acessorios</label>
                                        <input type="text" name="accessories" class="form-control" value="<?= $product['accessories'] ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Codigo</label>
                                        <input type="text" name="code" class="form-control" value="<?= $product['code'] ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Desconto</label>
                                        <input type="number" name="discount" class="form-control" value="<?= $product['discount'] ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Valor base</label>
                                        <input type="number" name="value" class="form-control" value="<?= $product['value'] ?>" required>
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label>Observações</label>
                                        <input type="text" name="observations" class="form-control" value="<?= $product['observations'] ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="button-container">
                                        <a href="productlist.php" class="btn btn-cancel">Cancelar</a>
                                        <button class="btn btn-submit" type="submit">Atualizar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <script src="assets/js/jquery-3.6.0.min.js"></script>
    <script src="assets/js/feather.min.js"></script>
    <script src="assets/js/jquery.slimscroll.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/plugins/select2/js/select2.min.js"></script>
    <script src="assets/plugins/sweetalert/sweetalert2.all.min.js"></script>
    <script src="assets/plugins/sweetalert/sweetalerts.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>

</html>