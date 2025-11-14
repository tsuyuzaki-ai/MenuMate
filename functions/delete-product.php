<?php
require_once '../functions/database.php';

// 商品IDを取得
$productId = $_GET['id'] ?? null;

if ($productId) {
    // 商品の情報を取得
    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(1, $productId, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        // 商品の削除処理
        $sql = "DELETE FROM products WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $productId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // 削除成功後、商品一覧ページにリダイレクト
            header('Location: ../public/item.php'); // 商品一覧ページへリダイレクト
            exit;
        } else {
            echo "削除に失敗しました。";
        }
    } else {
        echo "商品が見つかりません。";
    }
} else {
    echo "商品IDが指定されていません。";
}
?>
