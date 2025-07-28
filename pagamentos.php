<?php
require_once(__DIR__ . '/core/config/catechesis_config.inc.php');
require_once(__DIR__ . '/authentication/utils/authentication_verify.php');
require_once(__DIR__ . '/authentication/Authenticator.php');
require_once(__DIR__ . '/core/Utils.php');
require_once(__DIR__ . '/core/PdoDatabaseManager.php');
require_once(__DIR__ . '/gui/widgets/WidgetManager.php');
require_once(__DIR__ . '/gui/widgets/Navbar/MainNavbar.php');

use catechesis\Authenticator;
use catechesis\PdoDatabaseManager;
use catechesis\Utils;
use catechesis\DatabaseAccessMode;
use catechesis\gui\WidgetManager;
use catechesis\gui\MainNavbar;
use catechesis\gui\MainNavbar\MENU_OPTION;

// Create the widgets manager
$pageUI = new WidgetManager();

// Instantiate the widgets used in this page and register them in the manager
$menu = new MainNavbar(null, MENU_OPTION::PAYMENTS);
$pageUI->addWidget($menu);

$db = new PdoDatabaseManager();
$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cid'])) {
    if(!Utils::verifyCSRFToken($_POST['csrf_token'] ?? null))
    {
        echo("<div class='alert alert-danger'><strong>ERRO!</strong> Pedido inválido.</div>");
        die();
    }

    $cid = intval(Utils::sanitizeInput($_POST['cid']));
    $amount = 100.0; // Fixed amount
    $status = 'confirmado';
    try {
        $db->beginTransaction();
        $db->insertPayment(Authenticator::getUsername(), $cid, $amount, $status);
        if (isset($_POST['mark_enrollment'])) {
            $year = Utils::currentCatecheticalYear();
            $db->updateCatechumenEnrollmentPayment($cid, $year, intval($_POST['mark_enrollment_catechism'] ?? 0), Utils::sanitizeInput($_POST['mark_enrollment_group'] ?? ''), true);
        }
        $db->commit();
        $message = "<div class='alert alert-success'><strong>Sucesso!</strong> Pagamento registado.</div>";
    } catch (Exception $e) {
        $db->rollBack();
        $message = "<div class='alert alert-danger'><strong>Erro!</strong> " . $e->getMessage() . "</div>";
    }
}

try {
    $payments = $db->getPaymentsByUser(Authenticator::getUsername());
} catch (Exception $e) {
    $payments = [];
    $message = "<div class='alert alert-danger'><strong>Erro!</strong> " . $e->getMessage() . "</div>";
}

try {
    $catechisms = $db->getCatechisms(Utils::currentCatecheticalYear());
    $groups = $db->getGroupLetters(Utils::currentCatecheticalYear());
} catch (Exception $e) {
    $catechisms = [];
    $groups = [];
}

?>
<!DOCTYPE html>
<html lang="pt">
<head>

  <title>Pagamentos</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php $pageUI->renderCSS(); ?>
  <link rel="stylesheet" href="css/custom-navbar-colors.css">
</head>
<body>
<?php
$menu->renderHTML();
?>
<div class="container" id="contentor">
  <h2 class="no-print">Pagamentos</h2>
  <?php if ($message) echo $message; ?>
  <table class="table table-striped table-bordered">
    <thead>
      <tr><th>CID</th><th>Valor</th><th>Estado</th><th>Data</th><th>Falta (R$)</th><th>Situação</th></tr>
    </thead>
    <tbody>
    <?php foreach($payments as $p) { 
        try {
            $totalPaid = $db->getTotalPaymentsByCatechumen(intval($p['cid']));
        } catch (Exception $e) {
            $totalPaid = 0.0;
        }
        $remaining = max(0, 100 - $totalPaid);
        $statusText = $remaining == 0 ? 'Pago' : 'Em Débito';
    ?>
      <tr>
        <td><?= intval($p['cid']) ?></td>
        <td>R$<?= number_format((float)$p['valor'], 2, ',', '.') ?></td>
        <td><?= Utils::sanitizeOutput($p['estado']) ?></td>
        <td><?= Utils::sanitizeOutput($p['data_pagamento']) ?></td>
        <td>R$<?= number_format((float)$remaining, 2, ',', '.') ?></td>
        <td><?= $statusText ?></td>
      </tr>
    <?php } ?>
    </tbody>
  </table>
  <hr>
  <h3>Registar novo pagamento</h3>
  <form method="post" action="pagamentos.php" class="form-inline">
    <input type="hidden" name="csrf_token" value="<?= \catechesis\Utils::getCSRFToken() ?>">
    <div class="form-group">
      <label for="cid">Catequizando:</label>
      <input type="number" class="form-control" id="cid" name="cid" required>
    </div>
    <div class="checkbox" style="margin-left: 10px;">
      <label><input type="checkbox" id="mark_enrollment" name="mark_enrollment" onclick="toggleEnrollmentSelectors()"> Marcar inscrição paga</label>
    </div>
    <div id="enrollment_selectors" style="display:none; margin-left: 10px;" class="form-inline">
      <div class="form-group">
        <label for="mark_enrollment_catechism">Catecismo:</label>
        <select class="form-control" id="mark_enrollment_catechism" name="mark_enrollment_catechism">
          <?php foreach($catechisms as $c) { ?>
            <option value="<?= intval($c['ano_catecismo']) ?>"><?= intval($c['ano_catecismo']) ?>º</option>
          <?php } ?>
        </select>
      </div>
      <div class="form-group" style="margin-left: 5px;">
        <label for="mark_enrollment_group">Grupo:</label>
        <select class="form-control" id="mark_enrollment_group" name="mark_enrollment_group">
          <?php foreach($groups as $g) { $val = Utils::sanitizeOutput($g['turma']); ?>
            <option value="<?= $val ?>"><?= $val ?></option>
          <?php } ?>
        </select>
      </div>
    </div>
    <button type="submit" class="btn btn-primary" style="margin-left: 10px;">Registar R$100.00</button>
  </form>
  <script type="text/javascript">
    function toggleEnrollmentSelectors() {
      var cb = document.getElementById('mark_enrollment');
      var div = document.getElementById('enrollment_selectors');
      if(cb && div) {
        div.style.display = cb.checked ? 'inline-block' : 'none';
      }
    }
  </script>

<?php $pageUI->renderJS(); ?>
</body>
</html>

