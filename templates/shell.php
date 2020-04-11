<?php if (!$this->pageHeaderSent): ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$this->title?></title>
    <!-- <link rel="stylesheet" href="/style/style.css"> -->
    <?php $this->requireResource('head') ?>
</head>
<body>
<?php
    // header
    require_once "$this->root/templates/header.html";

    if ($this->sendingPageHeader) return;
endif; // page header sent

// content
$this->requireResource('content');

// footer
// require_once "$this->root/templates/footer.html";

// libraries
$this->requireResource('libraries');

// scripts
$this->requireResource('scripts');
?>
</body>
</html>