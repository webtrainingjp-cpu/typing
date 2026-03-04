<?php
// ページごとに上書きできるように
$page_title = $page_title ?? 'プログラミング向けタイピング練習｜WebTraining';
$page_description = $page_description ?? 'Web制作・プログラミング学習者向けのタイピング練習ツール。HTML・CSS・JavaScript・PHPなどの実務コード入力を想定したトレーニングができます。';
?>
<!doctype html>
<html lang="ja" data-bs-theme="dark">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="<?= htmlspecialchars($page_description, ENT_QUOTES); ?>">

    <title><?= htmlspecialchars($page_title, ENT_QUOTES); ?></title>

    <link rel="shortcut icon" href="images/favicon.ico">
    <!-- Apple Touch Icon -->
    <link rel="apple-touch-icon" href="images/apple-touch-icon.png">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css" />

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-CHTHZWVWBP"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-CHTHZWVWBP');
    </script>
</head>

<body class="bg-dark text-light wt-dark">

    <div class="container-fluid">

        <!-- タイトル -->
        <h1 class="text-center mb-2">
            <a href="https://typing.webtraining.jp/">
                <img
                    src="images/logo_typing.svg"
                    alt="Web制作学習者のためのタイピング練習"
                    width="500"
                    class="img-fluid">
            </a>
        </h1>

        <p class="text-center small text-secondary mb-5 typing-lead">
            Web制作の基礎からモダン開発技術まで、実務コードで鍛えるタイピングトレーニング
        </p>