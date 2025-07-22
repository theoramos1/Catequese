<?php
require_once(__DIR__ . '/core/config/catechesis_config.inc.php');
require_once(__DIR__ . '/authentication/utils/authentication_verify.php');
require_once(__DIR__ . '/authentication/Authenticator.php');
require_once(__DIR__ . '/gui/widgets/WidgetManager.php');
require_once(__DIR__ . '/gui/widgets/Navbar/MainNavbar.php');

use catechesis\gui\WidgetManager;
use catechesis\gui\MainNavbar;
use catechesis\gui\MainNavbar\MENU_OPTION;

// Create the widgets manager
$pageUI = new WidgetManager();

// Instantiate the widgets used in this page and register them in the manager
$menu = new MainNavbar(null, MENU_OPTION::PAYMENTS);
$pageUI->addWidget($menu);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <title>Pagamentos</title>
    <meta charset="utf-8">
    <?php $pageUI->renderCSS(); ?>
</head>
<body>
<?php $pageUI->renderHTML(); ?>
<div class="container">
    <!-- TODO: Conteúdo da página de pagamentos -->
</div>
<?php $pageUI->renderJS(); ?>
</body>
</html>

