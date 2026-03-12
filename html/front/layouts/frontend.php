<?php
// Get APP_URL from config
$config = require __DIR__ . '/../../../config/app.php';
$appUrl = rtrim($config['url'], '/');
?><!doctype html>
<html lang="<?php echo $_GET['lang'] ?? 'en'; ?>">
<head>
    <meta charset="UTF-8" />
    <meta
      name="description"
      content="Ekomart-Grocery-Store(e-Commerce) HTML Template: A sleek, responsive, and user-friendly HTML template designed for online grocery stores." />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="keywords" content="Grocery, Store, stores" />
    <title><?php echo isset($pageTitle) ? $pageTitle : 'AfiaZone - Grocery Store'; ?></title>
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo $appUrl; ?>/assets/img/favicon/favicon.ico" />
    
    <!-- Plugins CSS -->
    <link rel="stylesheet" href="<?php echo $appUrl; ?>/html/front/assets/css/plugins.css" />
    
    <!-- Main Style -->
    <link rel="stylesheet" href="<?php echo $appUrl; ?>/html/front/assets/css/style.css" />
    
    <?php if(isset($additionalStyles)): ?>
        <?php foreach($additionalStyles as $style): ?>
            <link rel="stylesheet" href="<?php echo $appUrl; ?>/html/front<?php echo $style; ?>" />
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body class="<?php echo $bodyClass ?? 'shop-main-h'; ?>">
    <!-- Header -->
    <?php include __DIR__ . '/header.php'; ?>
    
    <!-- Main Content -->
    <main class="rts-main-content">
        <?php echo $content ?? ''; ?>
    </main>
    
    <!-- Footer -->
    <?php include __DIR__ . '/footer.php'; ?>
    
    <!-- jQuery from CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Plugins JS -->
    <script defer src="<?php echo $appUrl; ?>/html/front/assets/js/plugins.js"></script>
    
    <!-- Main JS -->
    <script defer src="<?php echo $appUrl; ?>/html/front/assets/js/main.js"></script>
    
    <?php if(isset($additionalScripts)): ?>
        <?php foreach($additionalScripts as $script): ?>
            <script src="<?php echo $appUrl; ?><?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if(isset($inlineScripts)): ?>
        <?php echo $inlineScripts; ?>
    <?php endif; ?>
</body>
</html>

