<?php
require_once(__DIR__ . '/core/config/catechesis_config.inc.php');
require_once(__DIR__ . '/core/session_init.php');
require_once(__DIR__ . '/core/Utils.php');
require_once(__DIR__ . '/core/Configurator.php');
require_once(__DIR__ . '/core/PdoDatabaseManager.php');
require_once(__DIR__ . '/core/DataValidationUtils.php');
require_once(__DIR__ . '/gui/widgets/WidgetManager.php');
require_once(__DIR__ . '/gui/widgets/Navbar/MinimalNavbar.php');
require_once(__DIR__ . '/gui/widgets/Footer/SimpleFooter.php');
require_once(__DIR__ . '/core/check_maintenance_mode.php');

use catechesis\Utils;
use catechesis\Configurator;
use catechesis\PdoDatabaseManager;
use catechesis\DataValidationUtils;
use catechesis\gui\WidgetManager;
use catechesis\gui\MinimalNavbar;
use catechesis\gui\SimpleFooter;
use core\domain\Locale;

$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Utils::verifyCSRFToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Pedido inválido.';
    } else {
        $username = Utils::sanitizeInput($_POST['username'] ?? '');
        $name = Utils::sanitizeInput($_POST['name'] ?? '');
        $rawTel = Utils::sanitizeInput($_POST['telefone'] ?? '');
        $tel = ($rawTel !== '') ? preg_replace('/\D/', '', $rawTel) : null;
        $email = Utils::sanitizeInput($_POST['email'] ?? '');
        $password1 = Utils::sanitizeInput($_POST['password1'] ?? '');
        $password2 = Utils::sanitizeInput($_POST['password2'] ?? '');
        $locale = Configurator::getConfigurationValueOrDefault(Configurator::KEY_LOCALIZATION_CODE);

        if (!DataValidationUtils::validateUsername($username)) {
            $errors[] = 'Nome de utilizador inválido.';
        }
        if ($password1 !== $password2) {
            $errors[] = 'As palavras-passe não coincidem.';
        }
        if (!DataValidationUtils::validatePassword($password1)) {
            $errors[] = 'Palavra-passe inválida.';
        }
        if ($email !== '' && !DataValidationUtils::validateEmail($email)) {
            $errors[] = 'E-mail inválido.';
        }
        if ($tel !== null && !DataValidationUtils::validatePhoneNumber($tel, $locale)) {
            $msg = ($locale == Locale::BRASIL) ? "O número de telefone que introduziu é inválido." : "O número de telefone que introduziu é inválido.";
            $errors[] = $msg;
        }

        if (empty($errors)) {
            try {
                $db = new PdoDatabaseManager();
                $db->createUserAccount($username, $name, $password1, false, false, false, $tel, ($email !== '' ? $email : null));
                $success = true;
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        }
    }
}

$pageUI = new WidgetManager();
$navbar = new MinimalNavbar();
$pageUI->addWidget($navbar);
$footer = new SimpleFooter(null, true);
$pageUI->addWidget($footer);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Criar conta</title>
    <link rel="shortcut icon" href="img/favicon.png" type="image/png">
    <link rel="icon" href="img/favicon.png" type="image/png">
    <?php $pageUI->renderCSS(); ?>
</head>
<body>
<?php $navbar->renderHTML(); ?>
<div class="container" id="contentor">
    <h2>Registar nova conta</h2>
    <?php if ($success): ?>
        <div class="alert alert-success">Conta criada com sucesso. Pode agora <a href="login.php">iniciar sessão</a>.</div>
    <?php endif; ?>
    <?php foreach ($errors as $err): ?>
        <div class="alert alert-danger"><?= Utils::sanitizeOutput($err) ?></div>
    <?php endforeach; ?>
    <form class="form-horizontal" method="post" action="register.php">
        <input type="hidden" name="csrf_token" value="<?= Utils::getCSRFToken() ?>">
        <div class="form-group">
            <label class="control-label" for="name">Nome completo:</label>
            <input type="text" class="form-control" id="name" name="name" value="<?= Utils::sanitizeOutput($_POST['name'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label class="control-label" for="username">Nome de utilizador:</label>
            <input type="text" class="form-control" id="username" name="username" value="<?= Utils::sanitizeOutput($_POST['username'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label class="control-label" for="email">E-mail:</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= Utils::sanitizeOutput($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label class="control-label" for="telefone">Telefone:</label>
            <input type="tel" class="form-control" id="telefone" name="telefone" value="<?= Utils::sanitizeOutput($_POST['telefone'] ?? '') ?>" placeholder="<?= (Configurator::getConfigurationValueOrDefault(Configurator::KEY_LOCALIZATION_CODE) == Locale::BRASIL)?'Ex: (65) 3333-4444':'Telefone' ?>">
        </div>
        <div class="form-group">
            <label class="control-label" for="password1">Palavra-passe:</label>
            <input type="password" class="form-control" id="password1" name="password1" required>
        </div>
        <div class="form-group">
            <label class="control-label" for="password2">Confirmar palavra-passe:</label>
            <input type="password" class="form-control" id="password2" name="password2" required>
        </div>
        <button type="submit" class="btn btn-primary">Criar conta</button>
    </form>
    <div class="row" style="margin-bottom:40px;"></div>
</div>
<?php $footer->renderHTML(); ?>
<?php $pageUI->renderJS(); ?>
</body>
</html>
