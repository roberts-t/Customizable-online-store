<?php
include_once 'head.php';
include_once 'header.php';
require_once('objects/Product.php');

$productIdAndQuantity = array();
$products = array();
$totalCartPrice = 0.00;

if (isset($_SESSION['user_id'])) {
    foreach ($cartItems as $item) {
        $productIdAndQuantity[(int)$item['product_id']] = (int)$item['quantity'];
    }
}
else if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $productIdAndQuantity = $_SESSION['cart'];
}
//var_dump($productIds);
if (!empty($productIdAndQuantity)) {
    $inQuery = implode(',', array_fill(0, count($productIdAndQuantity), '?'));
    $productSql = "
    SELECT P.*, I.quantity, D.discount_percent, C.name AS category, (SELECT photo_path FROM product_photo PP WHERE P.id = PP.product_id LIMIT 1) AS photo_path  FROM `product` P
    LEFT JOIN product_inventory I ON P.inventory_id = I.id
    LEFT JOIN product_category C ON P.category_id = C.id
    LEFT JOIN product_discount D ON D.id = (SELECT MAX(PD.id) FROM product_discount PD WHERE PD.product_id = P.id AND PD.is_active = 1 AND (NOW() between PD.starting_at AND PD.ending_at))
    WHERE P.id IN (" . $inQuery . ")
    ORDER BY P.name
";
    $stmt = $conn->prepare($productSql);

    $i = 1;
    foreach ($productIdAndQuantity as $id => $quantity) {
        $stmt->bindValue(($i), $id);
        $i++;
    }
    $stmt->execute();

    $productRows = $stmt->fetchAll();
    $products = array();

    foreach ($productRows as $row) {
        $product = new Product();
        $product->getProductDataFromRow($row);
        $products[] = $product;
    }
}
?>
<script type="text/javascript" src="./js/cart.js"></script>
<script type="text/javascript">
    $( document ).ready(function() {
        getCart(<?=json_encode($cartItems)?>);
    });
</script>

<link href="css/cart.css?<?=time()?>" rel="stylesheet">
<div class="container">
    <div class="row">
        <h2 class="w-100 mt-md-5 mt-4 mb-4">Cart</h2>
    </div>
    <div class="cart-container">
        <div class="row bg-light p-3 d-md-flex d-none text-muted border border-bottom-0">
            <div class="col"></div>
            <div class="col">
                Product
            </div>
            <div class="col">
                Price
            </div>
            <div class="col">
                Quantity
            </div>
            <div class="col">
                Total
            </div>
        </div>
        
        <div class="cart-rows">
            
        </div>
<!--        --><?php
//        if (!empty($products)) {
//            $error = false;
//            foreach ($products as $cartProduct) {
//            ?>
<!---->
<!--        <div class="row cart-item p-3 border">-->
<!--            <div class="col-md text-center text-md-start">-->
<!--                <img src="test_images/--><?//=$cartProduct->photoPath?><!--" height="130" width="130" class="d-inline-block" alt="Product image">-->
<!--            </div>-->
<!--            <div class="d-inline-block px-3 product-title col-md text-center text-md-start my-md-0 my-3">-->
<!--                <div>--><?//=$cartProduct->name?><!--</div>-->
<!--            </div>-->
<!--            <div class="px-3 col-md text-center text-md-start">-->
<!--                <div>--><?//=$cartProduct->discountPrice?><!-- €</div>-->
<!--            </div>-->
<!--            <div class="d-inline-block px-3 col-md text-center text-md-start">-->
<!--                <div class="quantity-container">-->
<!--                    <div class="quantity-picker-container">-->
<!--                        <div onclick="this.parentNode.querySelector('input[type=number]').stepDown()"-->
<!--                             class="minus"><i class="fas fa-minus"></i></div>-->
<!--                        <input class="form-control" type="number" value="--><?//=$productIdAndQuantity[$cartProduct->id]?><!--" min="0">-->
<!--                        <div onclick="this.parentNode.querySelector('input[type=number]').stepUp()"-->
<!--                             class="plus"><i class="fas fa-plus"></i></div>-->
<!--                    </div>-->
<!--                    --><?php
//                    if ($productIdAndQuantity[$cartProduct->id] > $product->inventoryAmount) {
//                        $error = true;
//                    
//                    ?>
<!--                    <p class="text-danger pt-2"><i class="fas fa-info-circle"></i> Selected quantity is not available</p>-->
<!--                    --><?php
//                    }
//                    ?>
<!--                    <div class="mt-3 ms-md-1 me-md-0 me-1 mb-2 mb-md-0"><a onclick="return confirm('Are you sure you want to delete this item')" href="cart_remove_item.php?--><?//= (isset($_SESSION['user_id'])) ? 'cart_id=' . $cartId . '&' : ''?><!--product_id=--><?//=$cartProduct->id?><!----><?//=(isset($_SESSION['user_token'])) ? '&token=' . $_SESSION['user_token'] : ''?><!--" class="link-dark"><i-->
<!--                                    class="fas fa-times-circle pe-1"></i>Remove</a></div>-->
<!--                </div>-->
<!--            </div>-->
<!--            <div class="d-inline-block px-3 col-md text-center text-md-start fs-5">-->
<!--                <span class="d-inline-block d-md-none">Total:</span>-->
<!--                <div class="fw-bold d-inline-block">--><?//=$cartProduct->getProductTotalPrice($productIdAndQuantity[$cartProduct->id])?><!-- €</div>-->
<!--            </div>-->
<!--        </div>-->
<!--            -->
<!--        --><?php
//            $totalCartPrice += (float)$cartProduct->getProductTotalPrice($productIdAndQuantity[$cartProduct->id]);
//        }
//        } else {
//        ?>
<!--        <div class="row cart-item p-3 border justify-content-center fs-5">Cart is empty!</div>-->
<!--        --><?php
//        }
//        ?>
    </div>
    
    <div class="cart-footer text-end fs-4 mt-3 mb-5 d-flex align-items-center justify-content-end">
        <div class="d-inline-block">
            <span class="d-block">Total:</span>
            <span class="fw-bold"><?=number_format($totalCartPrice, 2, '.', '')?> €</span>
        </div>
        <button <?=empty($products) || $error ? 'disabled' : 'onclick="location.href=\'checkout.php\'"'?> class="ms-4 btn btn-primary cart-continue fs-5 fw-bold">Continue <i class="fas fa-arrow-right"></i></button>
    </div>
</div>

<?php include_once 'footer.php'?>