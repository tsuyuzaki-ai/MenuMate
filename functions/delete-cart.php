<?php
session_start();

if (isset($_POST['cart_key']) && isset($_POST['table_id'])) {
    $cart_key = $_POST['cart_key'];
    $table_id = $_POST['table_id'];

    if (isset($_SESSION['cart'][$table_id][$cart_key])) {
        unset($_SESSION['cart'][$table_id][$cart_key]);

        if (empty($_SESSION['cart'][$table_id])) {
            unset($_SESSION['cart'][$table_id]);
        }
    }
}



header("Location: ../public/cart.php");
exit();
?>
