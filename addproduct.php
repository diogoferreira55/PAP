<?php
include "db.config.php";

include "permission.php";

$idUser = $_SESSION['id'];

$canAdd = hasPermission($idUser, 3, 'insert', $con);

if ($canAdd == 0) {
    header("Location: no_permission.php");
    exit;
}

$estouEm = 2;
$categories = [];
$subcategories = [];
$idCategorySelected = '';

$msgOp = "";

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
/*
const categorias = {
    "Tecnologia": ["Computadores", "Smartphones", "Acessórios"],
    "Moda": ["Roupas", "Calçados", "Acessórios de Moda"],
    "Alimentos": ["Frescos", "Congelados", "Bebidas"]
};
*/
// if ($op == "category") {
//     $sqlCategories = "SELECT id, category FROM product_category WHERE idmaincategory = 0";
//     $resultCategories = $con->query($sqlCategories);
//     $categories = $resultCategories->fetch_all(MYSQLI_ASSOC);

//     $idCategorySelected = $_POST['idCategory'] ?? '';
//     if (!empty($idCategorySelected)) {
//         $sqlSubcategories = "SELECT id, category FROM product_category WHERE idmaincategory = ?";
//         $stmtSubcategories = $con->prepare($sqlSubcategories);
//         $stmtSubcategories->bind_param("i", $idCategorySelected);
//         $stmtSubcategories->execute();
//         $resultSubcategories = $stmtSubcategories->get_result();
//         $subcategories = $resultSubcategories->fetch_all(MYSQLI_ASSOC);
//     }
// }

// if (isset($_POST['idCategory'])) {
//     $idCategory = $_POST['idCategory'];
//     $sqlSubcategories = "SELECT id, category FROM product_category WHERE idmaincategory = ?";
//     $stmtSubcategories = $con->prepare($sqlSubcategories);
//     $stmtSubcategories->bind_param("i", $idCategory);
//     $stmtSubcategories->execute();
//     $resultSubcategories = $stmtSubcategories->get_result();
//     $subcategories = $resultSubcategories->fetch_all(MYSQLI_ASSOC);

//     $options = '<option value="">Selecione uma subcategoria</option>';
//     foreach ($subcategories as $subcategory) {
//         $options .= '<option value="' . $subcategory['id'] . '">' . htmlspecialchars($subcategory['category']) . '</option>';
//     }
//     echo $options;
//     exit();
// }

// Adicionar produto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['item'])) {
    $idProduct = $_POST['idProduct'];
    $idCategory = $_POST['idCategory'];
    $idSubCategory = $_POST['idSubCategory'];
    $item = $_POST['item'] ?? '';
    $brand = $_POST['brand'] ?? '';
    $model = $_POST['model'] ?? '';
    $seriesNum = $_POST['seriesNum'] ?? '';
    $location = $_POST['location'] ?? '';
    $accessories = $_POST['accessories'] ?? '';
    $code = $_POST['code'] ?? '';
    $observations = $_POST['observations'] ?? '';
    $value = $_POST['value'];
    $discount = $_POST['discount'] ?? 0;
    $discountedValue = $value - ($value * ($discount / 100));
    $imgPath = null;

    if (!empty($_FILES['img']['name'])) {
        $targetDir = "uploads/"; // Pasta onde a imagem será armazenada
        $imgPath = $targetDir . uniqid() . "-" . basename($_FILES['img']['name']);

        // Valida se a pasta de destino existe, caso contrário cria
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        if (!move_uploaded_file($_FILES['img']['tmp_name'], $imgPath)) {
            die("Erro ao salvar a imagem.");
        }
    }


    $sql = "INSERT INTO product (idProduct, idCategory, idSubCategory, item, brand, model, seriesNum, location, accessories, code, img, observations, value, discount,discounted_value)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param(
        "siisssssssssddd",
        $idProduct,
        $idCategory,
        $idSubCategory,
        $item,
        $brand,
        $model,
        $seriesNum,
        $location,
        $accessories,
        $code,
        $imgPath,
        $observations,
        $value,
        $discount,
        $discountedValue
    );

    if ($stmt->execute()) {
        $msgOp = "OK";
        /*echo "<script>
        Swal.fire({
            title: 'Produto adicionado com sucesso!',
            text: 'Deseja adicionar outro produto ou ir para a lista?',
            icon: 'success',
            showCancelButton: true,
            confirmButtonText: 'Adicionar Outro',
            cancelButtonText: 'Ir para a Lista'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'addproduct.php';
            } else {
                window.location.href = 'productlist.php';
            }
        });
    </script>";*/
    } else {
        $msgOp = "ERROR";
        /*error_log("Erro ao adicionar produto: " . $stmt->error);
        echo "<script>alert('Erro ao adicionar produto. Verifique os logs.');</script>";*/
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include "./rel.header.php"; ?>
    <style>
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
                    option.value = subcategoria.id; // Define o ID como valor
                    option.setAttribute("data-titulo", subcategoria.titulo); // Define o título como um atributo
                    option.textContent = subcategoria.titulo; // Exibe o título no select
                    subcategoriaSelect.appendChild(option);
                });
            } else {
                // Adiciona uma opção padrão caso não existam subcategorias
                const option = document.createElement("option");
                option.value = "";
                option.textContent = "Nenhuma subcategoria disponível";
                subcategoriaSelect.appendChild(option);
            }
        }

        // Função para capturar os valores selecionados
        function capturarValores() {
            const categoriaSelect = document.getElementById("idCategory");
            const subcategoriaSelect = document.getElementById("idSubCategory");

            // Categoria selecionada
            const categoriaId = categoriaSelect.value;
            const categoriaTitulo = categoriaSelect.options[categoriaSelect.selectedIndex].text;

            // Subcategoria selecionada
            const subcategoriaId = subcategoriaSelect.value;
            const subcategoriaTitulo = subcategoriaSelect.options[subcategoriaSelect.selectedIndex]?.getAttribute("data-titulo");

            alert(`Categoria: ${categoriaTitulo} (ID: ${categoriaId})\nSubcategoria: ${subcategoriaTitulo} (ID: ${subcategoriaId})`);
        }
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
                        <h4>Adicionar Produto</h4>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" action="?op=savenew">
                            <div class="row">
                                <div class="col-lg-3 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>ID do Produto</label>
                                        <input type="text" name="idProduct" required>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Item</label>
                                        <input type="text" name="item" required>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-sm-6 col-12">
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
                                <div class="col-lg-3 col-sm-6 col-12">
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
                                <div class="col-lg-3 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Marca</label>
                                        <input type="text" name="brand" required>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Modelo</label>
                                        <input type="text" name="model" required>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Numero de Serie</label>
                                        <input type="text" name="seriesNum">
                                    </div>
                                </div>
                                <div class="col-lg-3 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Armazenado em</label>
                                        <input type="text" name="location">
                                    </div>
                                </div>
                                <div class="col-lg-3 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Accessorios</label>
                                        <input type="text" name="accessories">
                                    </div>
                                </div>
                                <div class="col-lg-3 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Codigo</label>
                                        <input type="text" name="code">
                                    </div>
                                </div>
                                <div class="col-lg-3 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Desconto (%)</label>
                                        <input type="number" step="0.01" name="discount" required>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-sm-6 col-12">
                                    <div class="form-group">
                                        <label>Valor base</label>
                                        <input type="number" step="0.01" name="value">
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label>Observações</label>
                                        <textarea name="observations"></textarea>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label>Imagem</label>
                                        <input type="file" name="img" accept="image/*">
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <button type="submit" class="btn btn-submit me-2">Criar</button>
                                    <a href="productlist.php" class="btn btn-cancel">Cancelar</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <script src="/assets/js/jquery-3.6.0.min.js"></script>
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
            /*$('#categorySelect').change(function() {
                var categoryId = $(this).val();

                if (categoryId) {
                    $.ajax({
                        type: 'POST',
                        url: 'addproduct.php', // O mesmo arquivo
                        data: {
                            idCategory: categoryId
                        },
                        success: function(response) {
                            $('#subCategorySelect').html(response);
                        }
                    });
                }
            });*/
        });

        <?php
        if ($msgOp == "OK") {
            echo "
        Swal.fire({
            title: 'Produto adicionado com sucesso!',
            text: 'Deseja adicionar outro produto ou ir para a lista?',
            icon: 'success',
            showCancelButton: true,
            confirmButtonText: 'Adicionar Outro',
            cancelButtonText: 'Ir para a Lista'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'addproduct.php';
            } else {
                window.location.href = 'productlist.php';
            }
        });";
        } elseif ($msgOp == "ERROR") {
            error_log("Erro ao adicionar produto: " . $stmt->error);
            echo "alert('Erro ao adicionar produto. Verifique os logs.');";
        }
        ?>
    </script>
</body>

</html>