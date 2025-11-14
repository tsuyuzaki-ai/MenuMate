<?php
session_start();

// 商品IDとオプション情報を受け取る
if (isset($_POST['product_id']) && isset($_POST['product_name']) && isset($_POST['product_syokei'])) {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $product_syokei = $_POST['product_syokei'];
    $options = $_POST['options'] ?? [];

    // テーブルIDをセッションから取得
    $table_id = $_SESSION['table_id'] ?? null;

    // テーブルIDがない場合、エラー処理やリダイレクト
    if (!$table_id) {
        header("Location: ../public/shop.php");
        exit();
    }

    // カートを初期化（テーブルIDごと）
    if (!isset($_SESSION['cart'][$table_id])) {
        $_SESSION['cart'][$table_id] = [];
    }

    // オプションの組み合わせごとに異なるキーを作成
    $cart_key = $product_id . '_' . md5(json_encode($options));

    // 同じ商品・オプションの組み合わせがカートにある場合、数量を増やす
    if (isset($_SESSION['cart'][$table_id][$cart_key])) {
        $_SESSION['cart'][$table_id][$cart_key]['quantity']++;
    } else {
        // 新しい商品をカートに追加
        $_SESSION['cart'][$table_id][$cart_key] = [
            'product_id' => $product_id,
            'product_name' => $product_name,
            'product_syokei' => $product_syokei,
            'options' => $options,
            'quantity' => 1,
            'total_price' => $product_syokei
        ];
    }

    // 商品追加後、shop.phpにリダイレクト
    header("Location: ../public/shop.php");
    exit();
} else {
    // 必要なデータが受け取れない場合、エラーメッセージを表示後リダイレクト
    header("Location: ../public/shop.php?error=missing_data");
    exit();
}
?>
