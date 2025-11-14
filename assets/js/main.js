






/* -----------------------------------------------------
管理者側 商品登録＆更新フォーム
----------------------------------------------------- */

$(document).ready(function () {
    const $productForm = $('#productForm');
    const $dropZone = $('#dropZone');
    const $imageInput = $('#productImage');
    const $preview = $('#preview');

    // 商品登録・編集
    $productForm.on('submit', function (e) {
        e.preventDefault(); // 通常のフォーム送信をキャンセル


        // 商品IDがある場合（更新時）はポップアップを表示せずにそのまま処理
        const formData = new FormData($productForm[0]); // フォームデータを FormData で作成
        const formAction = $productForm.attr('action'); // フォームの送信先URLを取得

        // Ajax送信
        $.ajax({
            url: formAction, // フォームの送信先URL
            type: 'POST', // POSTメソッドで送信
            data: formData, // FormDataをそのまま送信
            contentType: false, // content-typeを自動設定
            processData: false, // データ処理をjQueryに任せる
            success: function (response) {
                alert('商品情報が正常に更新されました。');
            },
            error: function () {
                alert('更新に失敗しました。');
            }
        });
    });

    // 商品画像をドラッグアンドドロップ
    $dropZone.on('dragover', function (e) {
        e.preventDefault();
        $(this).addClass('dragover');
    });

    $dropZone.on('dragleave', function () {
        $(this).removeClass('dragover');
    });

    $dropZone.on('drop', function (e) {
        e.preventDefault();
        $(this).removeClass('dragover');

        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            const file = files[0];
            if (file.type.startsWith('image/')) {
                $imageInput[0].files = files; // フォーム送信時に使用するため設定
                displayPreview(file);
            } else {
                alert('画像ファイルをドロップしてください');
            }
        }
    });

    // プレビュー表示
    function displayPreview(file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            $preview.html(`<img src="${e.target.result}" alt="プレビュー">`);
        };
        reader.readAsDataURL(file);
    }

    // クリックでファイル選択
    $dropZone.on('click', function () {
        $imageInput[0].click();
    });

    // ファイル選択時の処理
    $imageInput.on('change', function (e) {
        const file = this.files[0];
        if (file && file.type.startsWith('image/')) {
            displayPreview(file);
        }
    });
});





$(document).ready(function () {
    // 画像削除ボタンがクリックされたら
    $('#deleteImage').on('click', function () {
        $('#preview').empty(); // プレビューを削除
        $('#productImage').val(''); // inputの値をリセット
        $('#deleteImageFlag').val(1); // inputの値をリセット
    });
});


// 商品削除の確認
$('#admin-home .delete').on('click', function (e) {
    e.preventDefault(); // 通常のリンク遷移をキャンセル

    if (confirm('商品を削除してもよろしいですか？')) {
        window.location.href = $(this).attr('href'); // 削除リンクへリダイレクト
    }
});

// オプションアコーディオンの動作
$('.accordion').on('click', function () {
    $(this).toggleClass('active');
    $(this).next().slideToggle();
});



// エンターで確定させない
$(document).ready(function () {
    $('#productForm').on('keydown', 'input', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault(); // Enterキーで送信を防ぐ
        }
    });
});


/* -----------------------------------------------------
管理者側 商品一覧
----------------------------------------------------- */

// カテゴリーで絞り込み
$(document).ready(function () {
    // 現在のURLのカテゴリーを取得
    var urlParams = new URLSearchParams(window.location.search);
    var currentCategory = urlParams.get('category') || 'ALL';

    // 初期状態で、現在のカテゴリーにactiveクラスを追加
    $('.search a').each(function () {
        if ($(this).text() === currentCategory) {
            $(this).addClass('active');
        }
    });

    // ボタンがクリックされた時の処理
    $('.search a').on('click', function () {
        // すべてのボタンから active クラスを削除
        $('.search a').removeClass('active');

        // クリックされたボタンに active クラスを追加
        $(this).addClass('active');
    });
});





/* -----------------------------------------------------
ユーザー側 商品一覧 カテゴリー選択
----------------------------------------------------- */
$(document).ready(function () {
    // 初期状態で最初のカテゴリーをアクティブにする
    $(".category li:first").addClass("active");

    // 現在時刻を取得
    var currentTime = new Date();
    var currentHour = currentTime.getHours();  // 現在の時（24時間形式）
    var currentMinute = currentTime.getMinutes();  // 現在の分

    // 10:00以降かどうかを判断
    var isAfter10AM = (currentHour > 10) || (currentHour === 10 && currentMinute >= 0);

    // カテゴリーをクリックしたときの処理
    $(".category li").on("click", function () {
        $(".category li").removeClass("active");
        $(this).addClass("active");

        var category = $(this).attr("class").split(" ")[0]; // 最初のクラスを取得

        // "morning" カテゴリが選択され、かつ10:00以降ならボタンを無効化
        if (category === "morning" && isAfter10AM) {
            $("button[type='submit']").css({
                "opacity": ".4",
                "pointer-events": "none"
            }).text("朝10:00まで");
        } else {
            // ボタンを通常状態に戻す
            $("button[type='submit']").css({
                "opacity": "1",
                "pointer-events": "auto"
            }).text("注文かごに入れる");
        }

        // すべての商品を一旦非表示
        $(".itemList li").hide();

        // "all" をクリックした場合はすべてを表示、それ以外は該当のクラスを持つ商品を表示
        if (category === "all") {
            $(".itemList li").fadeIn(300);
        } else {
            $(".itemList li." + category).fadeIn(300);
        }
    });

    // 初回読み込み時に最初のカテゴリーをクリックした状態にする
    $(".category li:first").trigger("click");
});
















/* -----------------------------------------------------
ユーザー側 商品詳細ポップアップ
----------------------------------------------------- */
$(document).ready(function () {


    // ✅ 商品詳細ポップアップが開かれた時の処理
    $(".itemList > li").on("click", function () {
        var clickedClass = $(this).attr("class").split(" ")[0]; // 最初のクラス名を取得
        $(".popupList form > li").fadeOut(0); // すべてのポップアップを非表示
        $(".popupList form > li." + clickedClass).fadeIn(300); // クリックした商品のポップアップを表示

        $(".popupList, .mask").fadeIn(300); // ポップアップとマスクを表示
        $("body").addClass("no-scroll"); // スクロールを防ぐ

        var $popup = $(".popupList form > li." + clickedClass); // 表示されたポップアップを取得

        // ✅ 各オプションの最初の選択肢を選択状態にする
        $popup.find(".options > li").each(function () {
            let $firstOption = $(this).find(".option li:first-child label input[type='radio']"); // 最初のオプション
            let $currentDisplay = $(this).find(".current"); // .current を取得

            if ($firstOption.length > 0 && !$firstOption.prop("checked")) {
                let selectedValue = $firstOption.val(); // 最初のオプションの値を取得
                $currentDisplay.text("- " + selectedValue); // .current に反映
                $firstOption.prop("checked", true).trigger("change"); // 初期選択
            }
        });

        // ✅ 小計を計算
        calculateTotal();
    });


    // ✅ オプション変更時に .current を更新
    $(document).on("change", ".popupList input[type='radio']", function () {
        let optionContainer = $(this).closest("ul");
        let currentDisplay = optionContainer.siblings(".current"); // `.current` を取得
        let selectedValue = $(this).val(); // 選択されたオプションの値

        // 同じオプショングループ内のすべてのラベルから `selected` を削除
        $(this).closest(".option").find("label").removeClass("selected");

        // 選択されたラベルに `selected` を追加
        $(this).closest("label").addClass("selected");

        // `.current` のテキストを選択されたオプション名に更新
        if (selectedValue) {
            currentDisplay.text("- " + selectedValue); // `.current` に反映
        }
        calculateTotal();
    });

    // ✅ 閉じるボタンまたはマスククリック時の処理
    $(".close, .mask").on("click", function () {
        $(".popupList, .mask").fadeOut(300);
        $("body").removeClass("no-scroll");
    });
});












/* -----------------------------------------------------
小計金額の切り替え
----------------------------------------------------- */
function calculateTotal() {
    var $popup = $(".popupList form > li:visible"); // 現在表示している商品ポップアップを取得
    var basePrice = parseInt($popup.find(".syoukei").data("base-price")) || 0; // 商品の基本価格
    var totalPrice = basePrice;

    // 選択されたオプションの価格を合計する
    $popup.find("input[type='radio']:checked").each(function () {
        var optionPrice = parseInt($(this).closest("label").find(".price").text().replace(/[^0-9-]/g, ""), 10) || 0;
        totalPrice += optionPrice;
    });

    // 合計金額を表示
    $popup.find(".syoukei").text(totalPrice.toLocaleString() + "円");

    // 合計金額を hidden input に反映してカートに送信
    $popup.find("input[name='product_syokei']").val(totalPrice);
}





/* -----------------------------------------------------
選択中オプションの切り替え
----------------------------------------------------- */
function updateSelectedOption(radio) {
    let optionContainer = $(radio).closest("li"); // `li.option` を取得
    let currentDisplay = optionContainer.closest("li").find(".current"); // `.current` を取得
    let selectedValue = $(radio).val(); // 選択されたオプションの値

    // 同じグループ内のすべてのラベルから `selected` を削除
    $(radio).closest(".option").find("label").removeClass("selected");

    // 選択されたラベルに `selected` を追加
    $(radio).closest("label").addClass("selected");

    if (selectedValue) {
        currentDisplay.text("- " + selectedValue); // `.current` に反映
    }
}






/* -----------------------------------------------------
ユーザー側 商品詳細 オプション開閉
----------------------------------------------------- */

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".flexBox").forEach(function (box) {
        const optionList = box.nextElementSibling;
        const btn = box.querySelector(".openBtn");

        // 初期状態で optionList を非表示に設定
        optionList.style.display = "none";
        btn.textContent = "選ぶ"; // 初期ボタンテキスト

        // flexBox をクリックするとリストを開閉
        box.addEventListener("click", function () {
            if (optionList.style.display === "none") {
                optionList.style.display = "block";
                btn.textContent = "とじる";
            } else {
                optionList.style.display = "none";
                btn.textContent = "選ぶ";
            }
        });

        // optionList 内の li をクリックするとリストを閉じる
        optionList.querySelectorAll("li").forEach(function (item) {
            item.addEventListener("click", function () {
                optionList.style.display = "none";
                btn.textContent = "選ぶ"; // ボタンのテキストを元に戻す
            });
        });
    });
});








/* -----------------------------------------------------
管理画面 注文確認画面
----------------------------------------------------- */
$(document).ready(function () {
    let previousOrderCount = $("#status01 .orders li").length; // 初回の注文数を取得
    let isAdminPage = window.location.pathname.includes("order.php"); // 管理画面か判定
    
    // 経過時間を更新する関数
    function updateElapsedTime($element) {
        let elapsedSeconds = parseInt($element.data("elapsed"));

        function tick() {
            let minutes = Math.floor(elapsedSeconds / 60);
            let seconds = elapsedSeconds % 60;

            $element.find(".minutes").text(String(minutes).padStart(2, '0'));
            $element.find(".seconds").text(String(seconds).padStart(2, '0'));

            $element.removeClass("yellow red");
            if (minutes >= 20) {
                $element.addClass("red");
            } else if (minutes >= 10) {
                $element.addClass("yellow");
            }

            elapsedSeconds++;
        }

        tick();
        setInterval(tick, 1000);
    }

    // 注文一部提供済み（クリック時に.activeクラスをトグル）
    // $(document).on('click', '.orderListPage #status01 ul.orders > li .rightWrap .order li', function () {
    //     $(this).toggleClass('active');
    // });

    // 通知音を2回連続で再生する関数
    function playNotificationSound() {
        if (!isAdminPage) return; // 管理画面でない場合は音を鳴らさない

        function playSequence(count) {
            if (count > 0) {
                let audio = new Audio("/menumate/assets/img/sound.mp3"); // 直接ファイルを指定
                audio.volume = 0.5; // 音量調整（50%）
                audio.play().then(() => {
                    setTimeout(() => playSequence(count - 1), 1000);
                }).catch(error => console.error("音声再生エラー:", error));
            }
        }

        playSequence(2); // 2回連続で再生
    }

    // オーダーの即時追加（5秒間隔で注文データを更新）
    function updateOrderList() {
        $.ajax({
            url: '../public/order.php',
            method: 'GET',
            data: { update_status: true }, // 任意のパラメータ（注文の更新を確認）
            success: function(data) {
                let newOrders = $(data).find("#status01 .orders li").length;

                $("#status01").html($(data).find("#status01").html());

                // 注文データが更新された後、経過時間を更新
                $("#status01 .time").each(function () {
                    updateElapsedTime($(this));
                });

                // 注文が増えたら通知音を鳴らす（管理画面のみ）
                if (isAdminPage && newOrders > previousOrderCount) {
                    playNotificationSound();
                }

                previousOrderCount = newOrders;
            },
            error: function(xhr, status, error) {
                console.error("注文一覧の更新に失敗しました: " + error);
            }
        });
    }

    // 初回の注文一覧更新
    updateOrderList();

    // 5秒ごとに注文一覧を更新
    setInterval(updateOrderList, 5000);
});




// 経過時間をDBに入れる
$(document).ready(function () {
    $(".btn-update-status").on("click", function (event) {
        event.preventDefault(); // フォーム送信を一時停止

        let $form = $(this).closest("form"); // フォームの取得
        let $timeElement = $(this).closest("li").find(".time"); // `data-elapsed` の要素
        let elapsedSeconds = parseInt($timeElement.data("elapsed"), 10); // `data-elapsed` 取得

        // フォーム送信
        $form.find(".serving-time").val(elapsedSeconds); // hidden input にセット

        $form.submit(); // フォーム送信
    });
});











