<?php
require_once 'database.php';

/* -----------------------------------------------------
画像アップロード
----------------------------------------------------- */
function uploadImage($file)
{
    $uploadDir = '../assets/img/' . date('Y') . '/' . date('m') . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filePath = $uploadDir . substr(uniqid(), -4) . '_' . basename($file['name']);
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        return $filePath;
    }
    return null; // 画像アップロード失敗時に null を返す
}







/* -----------------------------------------------------
フォームデータの取得
----------------------------------------------------- */

$productId = $_POST['productId'] ?? null;
$productName = trim($_POST['productName'] ?? ''); // 空文字をデフォルトに設定
$productBadge = isset($_POST['productBadge']) ? trim($_POST['productBadge']) : null;
$productPrice = isset($_POST['productPrice']) ? intval($_POST['productPrice']) : null; // 存在しない場合は null
$productDescription = $_POST['productDescription'] ?? ''; // 空文字をデフォルトに設定
$isVisible = isset($_POST['isVisible']) ? 1 : 0; // チェックされていれば 1、それ以外は 0
$productCategory = $_POST['productCategory'] ?? ''; // カテゴリー追加

// 商品名が空の場合はエラーメッセージを表示し、処理を中止
if (empty($productName)) {
    exit;  // 商品名が空の場合、処理を中止
}

// 画像処理
$productImage = null;

if (isset($_POST['deleteImage']) && $_POST['deleteImage'] == '1') {
    // 画像削除フラグが立っている場合、画像を削除
    $productImage = null;
} else {
    if (!empty($_FILES['productImage']['name'])) {
        // 新しい画像がアップロードされた場合
        $productImage = uploadImage($_FILES['productImage']);
    } elseif (!empty($_POST['currentImage'])) {
        // 画像がアップロードされていない場合、現在の画像を保持
        $productImage = $_POST['currentImage'];
    }
}


/* -----------------------------------------------------
商品情報テーブルに格納
----------------------------------------------------- */
if (!$productId) {
    // 新規登録の場合
    $sql = "INSERT INTO products (product_name, product_badge, product_price, product_image, product_description, is_visible, product_category) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssissis", $productName, $productBadge, $productPrice, $productImage, $productDescription, $isVisible, $productCategory);
} else {
    // 情報更新
    $sql = "UPDATE products SET 
                product_name = ?, 
                product_badge = ?, 
                product_price = ?, 
                product_description = ?, 
                is_visible = ?, 
                product_category = ?";

    // 画像がアップロードされた場合、新しい画像を設定
    if ($productImage !== null) {
        $sql .= ", product_image = ?";
    } else {
        // 画像が削除された場合（null で送られた場合）
        $sql .= ", product_image = NULL";
    }

    $sql .= " WHERE id = ?";

    $stmt = $conn->prepare($sql);

    // 画像がアップロードされた場合とそうでない場合で bind_param を変更
    if ($productImage !== null) {
        // 画像がアップロードされた場合
        $stmt->bind_param("ssissssi", $productName, $productBadge, $productPrice, $productDescription, $isVisible, $productCategory, $productImage, $productId);
    } else {
        // 画像がアップロードされていない場合、または削除された場合
        $stmt->bind_param("ssisssi", $productName, $productBadge, $productPrice, $productDescription, $isVisible, $productCategory, $productId);
    }
}










/* -----------------------------------------------------
商品オプションテーブルに格納
----------------------------------------------------- */
// 新規追加
if ($stmt->execute()) {
    if (!$productId) {
        $productId = $conn->insert_id;
        echo "商品情報が正常に登録されました。";
    } else {
        echo "商品情報が正常に更新されました。";
    }



    foreach (['option01', 'option02', 'option03', 'option04', 'option05'] as $index => $optionPrefix) {
        $optionTitle = $_POST[$optionPrefix . 'Title'] ?? '';

        if ($optionTitle) {
            $optionOrder = $index + 1; // オプション順序は1から始まる

            // オプションタイトルの存在チェック（product_id と option_order を基準にチェック）
            $optionTitleSql = "SELECT id FROM option_titles WHERE product_id = ? AND option_order = ? LIMIT 1";
            $optionTitleStmt = $conn->prepare($optionTitleSql);
            $optionTitleStmt->bind_param("ii", $productId, $optionOrder);
            $optionTitleStmt->execute();
            $optionTitleStmt->store_result();

            if ($optionTitleStmt->num_rows > 0) {
                // 既存のオプションタイトルがある場合は更新
                $optionTitleStmt->bind_result($optionTitleId);
                $optionTitleStmt->fetch();

                $updateOptionTitleSql = "UPDATE option_titles SET option_title = ? WHERE id = ?";
                $updateOptionTitleStmt = $conn->prepare($updateOptionTitleSql);
                $updateOptionTitleStmt->bind_param("si", $optionTitle, $optionTitleId);
                $updateOptionTitleStmt->execute();
            } else {
                // ない場合は新規追加
                $insertOptionTitleSql = "INSERT INTO option_titles (product_id, option_title, option_order) VALUES (?, ?, ?)";
                $insertOptionTitleStmt = $conn->prepare($insertOptionTitleSql);
                $insertOptionTitleStmt->bind_param("isi", $productId, $optionTitle, $optionOrder);
                if ($insertOptionTitleStmt->execute()) {
                    $optionTitleId = $conn->insert_id;
                }
            }

            // 既存のオプション詳細を削除
            $deleteOptionDetailSql = "DELETE FROM option_details WHERE option_title_id = ?";
            $deleteOptionDetailStmt = $conn->prepare($deleteOptionDetailSql);
            $deleteOptionDetailStmt->bind_param("i", $optionTitleId);
            $deleteOptionDetailStmt->execute();

            // 新しいオプション詳細を挿入
            if (!empty($_POST[$optionPrefix . 'Name'])) {
                foreach ($_POST[$optionPrefix . 'Name'] as $index => $optionName) {
                    $optionPrice = isset($_POST[$optionPrefix . 'Price'][$index]) ? intval($_POST[$optionPrefix . 'Price'][$index]) : 0;
                    if (!empty($optionName)) {
                        $insertOptionDetailSql = "INSERT INTO option_details (option_title_id, option_name, option_price) VALUES (?, ?, ?)";
                        $insertOptionDetailStmt = $conn->prepare($insertOptionDetailSql);
                        $insertOptionDetailStmt->bind_param("isi", $optionTitleId, $optionName, $optionPrice);
                        $insertOptionDetailStmt->execute();
                    }
                }
            }
        }
    }

    echo "商品とオプションが正常に更新されました。";
} else {
    echo "エラー: " . $stmt->error;
}



/* -----------------------------------------------------
オプションタイトルと詳細の削除処理
----------------------------------------------------- */

$existingOptionTitleSql = "SELECT id, option_title FROM option_titles WHERE product_id = ?";
$existingOptionTitleStmt = $conn->prepare($existingOptionTitleSql);
$existingOptionTitleStmt->bind_param("i", $productId);
$existingOptionTitleStmt->execute();
$existingOptionTitleStmt->bind_result($existingOptionTitleId, $existingOptionTitle);

$existingOptionTitles = [];
while ($existingOptionTitleStmt->fetch()) {
    $existingOptionTitles[$existingOptionTitle] = $existingOptionTitleId;
}
$existingOptionTitleStmt->close();

$formOptionTitles = [];
foreach (['option01', 'option02', 'option03', 'option04', 'option05'] as $optionPrefix) {
    if (!empty($_POST[$optionPrefix . 'Title'])) {
        $formOptionTitles[] = $_POST[$optionPrefix . 'Title'];
    }
}

// 削除対象のオプションタイトルを特定
$optionTitlesToDelete = array_diff_key($existingOptionTitles, array_flip($formOptionTitles));

if (!empty($optionTitlesToDelete)) {
    // option_details を削除
    $deleteOptionDetailsSql = "DELETE FROM option_details WHERE option_title_id IN (" . implode(',', array_fill(0, count($optionTitlesToDelete), '?')) . ")";
    $deleteOptionDetailsStmt = $conn->prepare($deleteOptionDetailsSql);
    $deleteOptionDetailsStmt->bind_param(str_repeat('i', count($optionTitlesToDelete)), ...array_values($optionTitlesToDelete));
    $deleteOptionDetailsStmt->execute();
    $deleteOptionDetailsStmt->close();

    // option_titles を削除
    $deleteOptionTitlesSql = "DELETE FROM option_titles WHERE id IN (" . implode(',', array_fill(0, count($optionTitlesToDelete), '?')) . ")";
    $deleteOptionTitlesStmt = $conn->prepare($deleteOptionTitlesSql);
    $deleteOptionTitlesStmt->bind_param(str_repeat('i', count($optionTitlesToDelete)), ...array_values($optionTitlesToDelete));
    $deleteOptionTitlesStmt->execute();
    $deleteOptionTitlesStmt->close();
}


$conn->close();

