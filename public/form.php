<?php
require_once '../parts/header.php';
require_once '../functions/database.php';

// 商品IDの取得
$productId = isset($_GET['id']) ? $_GET['id'] : null;

// 初期化
$product = null;
$optionTitles = [];
$optionDetails = [];

if ($productId) {
    // 商品情報を取得
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    // オプションタイトル情報を取得
    $stmtTitles = $pdo->prepare("SELECT * FROM option_titles WHERE product_id = :product_id ORDER BY option_order ASC");
    $stmtTitles->bindParam(':product_id', $productId, PDO::PARAM_INT);
    $stmtTitles->execute();
    $titles = $stmtTitles->fetchAll(PDO::FETCH_ASSOC);

    // オプションタイトルと詳細を取得
    foreach ($titles as $title) {
        $optionOrder = isset($title['option_order']) ? $title['option_order'] - 1 : 0; // option_orderがない場合は0とする
        $optionTitles[$optionOrder] = $title['option_title'];

        // オプション詳細情報を取得
        $stmtOptions = $pdo->prepare("SELECT * FROM option_details WHERE option_title_id = :option_title_id ORDER BY id");
        $stmtOptions->bindParam(':option_title_id', $title['id'], PDO::PARAM_INT);
        $stmtOptions->execute();
        $options = $stmtOptions->fetchAll(PDO::FETCH_ASSOC);

        // optionDetailsの初期化
        if (!isset($optionDetails[$optionOrder])) {
            $optionDetails[$optionOrder] = []; // 初期化
        }

        foreach ($options as $option) {
            $optionDetails[$optionOrder][] = [
                'option_name' => $option['option_name'],
                'option_price' => $option['option_price']
            ];
        }
    }
}






?>





<title>商品登録・編集 | MenuMate</title>
</head>

<body ontouchstart="">

    <main id="admin-edit" class="adminPage">
        <div class="content">

            <?php require_once '../parts/side.php'; ?>

            <div class="hasSide">
                <h1>商品登録・編集</h1>

                <form id="productForm" class="edit" action="../functions/edit-product.php" method="POST"
                    enctype="multipart/form-data">

                    <div class="btn">
                        <button type="submit" class="done">登録・編集する</button>
                    </div>

                    <!-- こっそりID付与（編集時） -->
                    <input type="hidden" name="productId" value="<?php echo htmlspecialchars($product['id'] ?? ''); ?>">

                    <!-- 商品表示 -->
                    <div class="box">
                        <h2>メニュー掲載</h2>
                        <label class="switch">
                            <input type="checkbox" name="isVisible" <?php echo isset($product['is_visible']) ? ($product['is_visible'] ? 'checked' : '') : ''; ?>>
                            <span class="slider">
                                <span class="label left">非表示</span>
                                <span class="label right">表示中</span>
                            </span>
                        </label>
                    </div>


                    <!-- 商品名 -->
                    <div class="box">
                        <h2 class="req">商品名</h2>
                        <input type="text" class="name full" name="productName"
                            value="<?php echo htmlspecialchars($product['product_name'] ?? ''); ?>" required>
                    </div>

                    <!-- カテゴリー -->
                    <div class="box">
                        <h2>カテゴリー</h2>

                        <div class="category-container">
                            <?php
                            $categories = ["モーニング", "セット", "スープ", "一品料理", "パン・ご飯", "サラダ", "ドリンク", "スイーツ"];
                            $selectedCategory = $product['product_category'] ?? ''; // 既存のカテゴリーがあれば取得
                            ?>

                            <?php foreach ($categories as $category): ?>
                                <input type="radio" class="category" id="<?php echo htmlspecialchars($category); ?>"
                                    name="productCategory" value="<?php echo htmlspecialchars($category); ?>" <?php echo ($selectedCategory === $category) ? 'checked' : ''; ?>>
                                <label
                                    for="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></label>
                            <?php endforeach; ?>
                        </div>

                    </div>

                    <!-- アイコン -->
                    <div class="box">
                        <h2>アイコン</h2>
                        <input type="text" class="badge" name="productBadge"
                            value="<?php echo htmlspecialchars($product['product_badge'] ?? ''); ?>"
                            placeholder="おすすめ / 人気No.1 など">
                    </div>

                    <!-- 価格 -->
                    <div class="box">
                        <h2 class="req">価格</h2>
                        <input type="text" class="price" name="productPrice"
                            value="<?php echo htmlspecialchars($product['product_price'] ?? ''); ?>" required><span
                            class="yen">円</span>
                    </div>

                    <!-- 画像削除フラグ（必ずフォームに含める） -->
                    <input type="hidden" name="deleteImage" id="deleteImageFlag" value="0">

                    <!-- 商品画像 -->
                    <div class="box">
                        <h2>商品画像</h2>
                        <div id="dropZone" class="drop-zone">
                            <p>ここに画像を<br>ドラッグ＆ドロップしてください</p>
                            <input type="file" id="productImage" class="image-input" name="productImage"
                                accept="image/*" hidden="">
                        </div>
                        <div id="preview">
                            <?php if (!empty($product['product_image'])): ?>
                                <div class="image-preview">
                                    <img src="<?php echo htmlspecialchars($product['product_image']); ?>" alt="商品画像"
                                        style="max-width: 100%;">
                                    <img src="/menumate/assets/img/icon_close.png" class="delete-icon" id="deleteImage" alt="削除">
                                </div>
                            <?php endif; ?>
                        </div>
                        <!-- 既存の画像パスを保持 -->
                        <input type="hidden" name="currentImage"
                            value="<?php echo htmlspecialchars($product['product_image'] ?? ''); ?>">
                    </div>




                    <!-- 商品説明 -->
                    <div class="box">

                        <h2>商品説明文</h2>

                        <textarea type="text" class="info full" name="productDescription"
                            rows="3"><?php echo htmlspecialchars($product['product_description'] ?? ''); ?></textarea>

                    </div>





                    <!-- オプション① -->
                    <div class="box">
                        <h2 class="accordion">オプション①</h2>
                        <div class="panel">


                            <h3>オプションタイトル</h3>
                            <input type="text" class="title title01" name="option01Title" placeholder="スープをお選びください"
                                value="<?php echo htmlspecialchars($optionTitles[0] ?? ''); ?>">

                            <ul>
                                <li>
                                    <h3>オプション</h3>
                                    <h3>価格</h3>
                                </li>
                            </ul>

                            <ul class="set set01">
                                <?php
                                $existingOptions = $optionDetails[0] ?? []; // オプション①の詳細を取得
                                $totalOptions = 15;

                                for ($i = 0; $i < $totalOptions; $i++):
                                    $optionName = $existingOptions[$i]['option_name'] ?? '';
                                    $optionPrice = $existingOptions[$i]['option_price'] ?? '';
                                    ?>
                                    <li>
                                        <input type="text" class="name" name="option01Name[]"
                                            value="<?php echo htmlspecialchars($optionName); ?>" placeholder="ラムの薬膳スープ">
                                        <input type="number" class="price" name="option01Price[]"
                                            value="<?php echo htmlspecialchars($optionPrice); ?>" placeholder="680"><span
                                            class="yen">円</span>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </div>
                    </div>



                    <!-- オプション② -->
                    <div class="box">
                        <h2 class="accordion">オプション②</h2>
                        <div class="panel">


                            <h3>オプションタイトル</h3>
                            <input type="text" class="title title02" name="option02Title" placeholder="サラダをお選びください"
                                value="<?php echo htmlspecialchars($optionTitles[1] ?? ''); ?>">




                            <ul>
                                <li>
                                    <h3>オプション</h3>
                                    <h3>価格</h3>
                                </li>
                            </ul>

                            <ul class="set set02">
                                <?php
                                $existingOptions = $optionDetails[1] ?? []; // オプション②の詳細を取得
                                $totalOptions = 15;

                                for ($i = 0; $i < $totalOptions; $i++):
                                    $optionName = $existingOptions[$i]['option_name'] ?? '';
                                    $optionPrice = $existingOptions[$i]['option_price'] ?? '';
                                    ?>
                                    <li>
                                        <input type="text" class="name" name="option02Name[]"
                                            value="<?php echo htmlspecialchars($optionName); ?>" placeholder="シーザーサラダ">
                                        <input type="number" class="price" name="option02Price[]"
                                            value="<?php echo htmlspecialchars($optionPrice); ?>" placeholder="500"><span
                                            class="yen">円</span>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </div>
                    </div>

                    <!-- オプション③ -->
                    <div class="box">
                        <h2 class="accordion">オプション③</h2>
                        <div class="panel">


                            <h3>オプションタイトル</h3>
                            <input type="text" class="title title03" name="option03Title" placeholder="ドリンクをお選びください"
                                value="<?php echo htmlspecialchars($optionTitles[2] ?? ''); ?>">


                            <ul>
                                <li>
                                    <h3>オプション</h3>
                                    <h3>価格</h3>
                                </li>
                            </ul>

                            <ul class="set set03">
                                <?php
                                $existingOptions = $optionDetails[2] ?? []; // オプション③の詳細を取得
                                $totalOptions = 15;

                                for ($i = 0; $i < $totalOptions; $i++):
                                    $optionName = $existingOptions[$i]['option_name'] ?? '';
                                    $optionPrice = $existingOptions[$i]['option_price'] ?? '';
                                    ?>
                                    <li>
                                        <input type="text" class="name" name="option03Name[]"
                                            value="<?php echo htmlspecialchars($optionName); ?>" placeholder="コーヒー">
                                        <input type="number" class="price" name="option03Price[]"
                                            value="<?php echo htmlspecialchars($optionPrice); ?>" placeholder="380"><span
                                            class="yen">円</span>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </div>
                    </div>


                    <!-- オプション④ -->
                    <div class="box">
                        <h2 class="accordion">オプション④</h2>
                        <div class="panel">


                            <h3>オプションタイトル</h3>
                            <input type="text" class="title title04" name="option04Title" placeholder="ドリンクをお選びください"
                                value="<?php echo htmlspecialchars($optionTitles[3] ?? ''); ?>">


                            <ul>
                                <li>
                                    <h3>オプション</h3>
                                    <h3>価格</h3>
                                </li>
                            </ul>

                            <ul class="set set04">
                                <?php
                                $existingOptions = $optionDetails[3] ?? []; // オプション④の詳細を取得
                                $totalOptions = 15;

                                for ($i = 0; $i < $totalOptions; $i++):
                                    $optionName = $existingOptions[$i]['option_name'] ?? '';
                                    $optionPrice = $existingOptions[$i]['option_price'] ?? '';
                                    ?>
                                    <li>
                                        <input type="text" class="name" name="option04Name[]"
                                            value="<?php echo htmlspecialchars($optionName); ?>" placeholder="コーヒー">
                                        <input type="number" class="price" name="option04Price[]"
                                            value="<?php echo htmlspecialchars($optionPrice); ?>" placeholder="380"><span
                                            class="yen">円</span>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </div>
                    </div>



                    <!-- オプション⑤ -->
                    <div class="box">
                        <h2 class="accordion">オプション⑤</h2>
                        <div class="panel">

                            <h3>オプションタイトル</h3>
                            <input type="text" class="title title05" name="option05Title" placeholder="トッピングをお選びください"
                                value="<?php echo htmlspecialchars($optionTitles[4] ?? ''); ?>">

                            <ul>
                                <li>
                                    <h3>オプション</h3>
                                    <h3>価格</h3>
                                </li>
                            </ul>

                            <ul class="set set05">
                                <?php
                                $existingOptions = $optionDetails[4] ?? []; // オプション⑤の詳細を取得
                                $totalOptions = 15;

                                for ($i = 0; $i < $totalOptions; $i++):
                                    $optionName = $existingOptions[$i]['option_name'] ?? '';
                                    $optionPrice = $existingOptions[$i]['option_price'] ?? '';
                                    ?>
                                    <li>
                                        <input type="text" class="name" name="option05Name[]"
                                            value="<?php echo htmlspecialchars($optionName); ?>" placeholder="トッピング名">
                                        <input type="number" class="price" name="option05Price[]"
                                            value="<?php echo htmlspecialchars($optionPrice); ?>" placeholder="100"><span
                                            class="yen">円</span>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </div>
                    </div>


                </form>
            </div>
        </div>

    </main>

    <?php require_once '../parts/footer.php'; ?>