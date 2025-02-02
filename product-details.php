<?php


include "db.config.php";

$estouEm = 2;

include "permission.php";

$idUser = $_SESSION['id'];

$canView = hasPermission($idUser, 3, 'view', $con);

if ($canView == 0) {
    header("Location: no_permission.php");
    exit;
}
$productId = $_GET['id'] ?? 1;
/*
$sql = "SELECT category  
        FROM product_category pc 
        LEFT JOIN product p ON pc.id = p.idCategory 
        WHERE p.id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();
$cat = $result->fetch_assoc();

$sql = "SELECT category  
        FROM product_category pc 
        LEFT JOIN product p ON pc.id = p.idSubCategory 
        WHERE p.id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();
$subcat = $result->fetch_assoc();
*/

$sql = "SELECT p.*,pc.category AS catP, pcsub.category AS subcatP ";

$sql .= " FROM (product p
                INNER join 
                product_category pc 
                    on pc.id = p.idCategory)
                INNER join 
                product_category pcsub
                    on pcsub.id = p.idSubCategory
        ";

$sql .= " WHERE p.id = ?";

$stmt = $con->prepare($sql);
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include "./rel.header.php"; ?>
    <style>
        .slider-product {
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
                        <h4>Product Details</h4>
                        <h6>Full details of product</h6>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8 col-sm-12">
                        <div class="card">
                            <div class="card-body">
                                <!-- <div class="bar-code-view">
                                    <img src="assets/img/barcode1.png" alt="barcode">
                                    <a class="printimg">
                                        <img src="assets/img/icons/printer.svg" alt="print">
                                    </a>
                                </div> -->
                                <div class="productdetails">
                                    <ul class="product-bar">
                                        <li>
                                            <h4>Cliente</h4>
                                            <h6><?php echo htmlspecialchars($product['idProduct']); ?></h6>
                                        </li>
                                        <li>
                                            <h4>Item</h4>
                                            <h6><?php echo htmlspecialchars($product['item']); ?></h6>
                                        </li>
                                        <li>
                                            <h4>Categoria</h4>
                                            <h6><?php echo htmlspecialchars($product['catP']); ?></h6>
                                        </li>
                                        <li>
                                            <h4>Sub Categoria</h4>
                                            <h6><?php echo htmlspecialchars($product['subcatP']); ?></h6>
                                        </li>
                                        <li>
                                            <h4>Marca</h4>
                                            <h6><?php echo htmlspecialchars($product['brand']); ?></h6>
                                        </li>
                                        <li>
                                            <h4>Modelo</h4>
                                            <h6><?php echo htmlspecialchars($product['model']); ?></h6>
                                        </li>
                                        <li>
                                            <h4>Numero de Serie</h4>
                                            <h6><?php echo htmlspecialchars($product['seriesNum']); ?></h6>
                                        </li>
                                        <li>
                                            <h4>Armazenado em</h4>
                                            <h6><?php echo htmlspecialchars($product['location']); ?></h6>
                                        </li>
                                        <li>
                                            <h4>Accessorios</h4>
                                            <h6><?php echo htmlspecialchars($product['accessories']); ?></h6>
                                        </li>
                                        <li>
                                            <h4>Codigo</h4>
                                            <h6><?php echo htmlspecialchars($product['code']); ?></h6>
                                        </li>
                                        <li>
                                            <h4>Valor</h4>
                                            <h6><?php echo htmlspecialchars(number_format($product['value'], 2)); ?></h6>
                                        </li>
                                        <li>
                                            <h4>Descrição</h4>
                                            <h6><?php echo htmlspecialchars($product['observations']); ?></h6>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-sm-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="slider-product-details">
                                    <div class="owl-carousel owl-theme product-slide">
                                        <div class="slider-product">
                                            <img src="<?php echo htmlspecialchars($product['img']); ?>" alt="Product Image" class="product-image">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="assets/js/jquery-3.6.0.min.js"></script>
    <script src="assets/js/feather.min.js"></script>
    <script src="assets/js/jquery.slimscroll.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/plugins/owlcarousel/owl.carousel.min.js"></script>
    <script src="assets/plugins/select2/js/select2.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>

</html>