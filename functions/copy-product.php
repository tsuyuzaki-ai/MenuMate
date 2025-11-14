<?php
require_once '../functions/database.php';

// 商品IDを取得
$productId = $_GET['id'] ?? null;

if ($productId) {
    try {
        $pdo->beginTransaction(); // トランザクション開始

        // コピー元の商品情報を取得
        $sql = "SELECT * FROM products WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            throw new Exception("コピー元の商品が見つかりません。");
        }

        // 新しい商品を挿入
        $newProductName = $product['product_name'] . "のコピー";
        $sql = "INSERT INTO products (product_name, product_badge, product_price, product_image, product_description, is_visible, product_category, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $newProductName,
            $product['product_badge'],
            $product['product_price'],
            $product['product_image'],
            $product['product_description'],
            $product['is_visible'],
            $product['product_category'] 
        ]);

        // 新しく作成された商品のIDを取得
        $newProductId = $pdo->lastInsertId();

        // option_titles をコピー（もし存在すれば）
        $sql = "SELECT * FROM option_titles WHERE product_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$productId]);
        $optionTitles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $optionTitleIdMap = []; // 新旧option_title_idのマッピング

        foreach ($optionTitles as $optionTitle) {
            $sql = "INSERT INTO option_titles (product_id, option_title, option_order, created_at) 
                    VALUES (?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $newProductId,
                $optionTitle['option_title'],
                $optionTitle['option_order']
            ]);

            // 新しく作成された option_title_id を取得し、マッピング
            $newOptionTitleId = $pdo->lastInsertId();
            $optionTitleIdMap[$optionTitle['id']] = $newOptionTitleId;
        }

        // option_details をコピー（option_titles が空でない場合）
        if (!empty($optionTitleIdMap)) {
            $sql = "SELECT * FROM option_details WHERE option_title_id IN (" . implode(',', array_keys($optionTitleIdMap)) . ")";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $optionDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($optionDetails as $optionDetail) {
                $sql = "INSERT INTO option_details (option_title_id, option_name, option_price, created_at) 
                        VALUES (?, ?, ?, NOW())";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $optionTitleIdMap[$optionDetail['option_title_id']],
                    $optionDetail['option_name'],
                    $optionDetail['option_price']
                ]);
            }
        }

        $pdo->commit(); // トランザクションをコミット

        // コピー成功後、商品一覧ページへリダイレクト
        header('Location: ../public/item.php');
        exit;
    } catch (Exception $e) {
        $pdo->rollBack(); // 失敗した場合はロールバック
        echo "コピーに失敗しました: " . $e->getMessage();
    }
} else {
    echo "商品IDが指定されていません。";
}
