<?php
require_once '../functions/database.php';

// 商品カテゴリーの一覧
$categories = ["モーニング", "セット", "スープ", "一品料理", "パン・ご飯", "サラダ", "ドリンク", "スイーツ"];

// 商品一覧を取得（絞り込みがあればカテゴリーでフィルタリング）
$categoryFilter = $_GET['category'] ?? null;

$sql = "SELECT * FROM products";
if ($categoryFilter && $categoryFilter != 'ALL') {
    $sql .= " WHERE product_category = ?";
}
$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
if ($categoryFilter && $categoryFilter != 'ALL') {
    $stmt->execute([$categoryFilter]);
} else {
    $stmt->execute();
}

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>

<?php require_once '../parts/header.php'; ?>

<title>商品一覧 | MenuMate</title>
</head>

<body ontouchstart="">


    <main id="admin-home" class="adminPage">
        <div class="content">

            <?php require_once '../parts/side.php'; ?>

            <div class="hasSide">
                <h1>商品一覧</h1>

                <a href="form.php" class="add_item">+ 新しい商品を追加</a>

                <div class="searchWrap">
                    <h2>カテゴリー</h2>
                    <ul class="search">

                        <?php foreach ($categories as $category): ?>
                            <li>
                                <a href="?category=<?php echo urlencode($category); ?>">
                                    <?php echo htmlspecialchars($category); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>

                        <li><a href="?category=ALL">ALL</a></li>
                    </ul>
                </div>

                <ul class="itemList">
                    <?php foreach ($products as $product): ?>
                        <li>
                            <p class="toggle">
                                <span class="open <?php echo $product['is_visible'] == 1 ? 'active' : ''; ?>">表示中</span>
                                <span class="close <?php echo $product['is_visible'] == 0 ? 'active' : ''; ?>">非表示</span>
                            </p>

                            <div class="imageWrap">
                                <img src="<?php echo htmlspecialchars($product['product_image'] ?: '/menumate/assets/img/dummy.png'); ?>"
                                    alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                            </div>




                            <div class="titleWrap">
                                <p class="category"><?php echo htmlspecialchars($product['product_category']); ?></p>
                                <p class="name"><?php echo htmlspecialchars($product['product_name']); ?></p>
                            </div>

                            <p class="price"><?php echo number_format($product['product_price']); ?>円</p>

                            <a class="copy" href="../functions/copy-product.php?id=<?php echo $product['id']; ?>">コピー</a>

                            <a class="edit" href="form.php?id=<?php echo $product['id']; ?>">編集</a>
                            <a class="delete" href="../functions/delete-product.php?id=<?php echo $product['id']; ?>">削除</a>

                        </li>
                    <?php endforeach; ?>
                </ul>

            </div>
        </div>
    </main>

    <?php require_once '../parts/footer.php'; ?>