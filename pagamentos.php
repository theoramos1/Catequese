<?php
require_once(__DIR__ . '/core/config/catechesis_config.inc.php');
require_once(__DIR__ . '/authentication/utils/authentication_verify.php');
require_once(__DIR__ . '/authentication/Authenticator.php');
require_once(__DIR__ . '/core/Utils.php');
require_once(__DIR__ . '/core/DataValidationUtils.php');
require_once(__DIR__ . '/core/PdoDatabaseManager.php');
require_once(__DIR__ . '/core/Configurator.php');
require_once(__DIR__ . '/core/Pix.php');
require_once(__DIR__ . '/gui/widgets/WidgetManager.php');
require_once(__DIR__ . '/gui/widgets/Navbar/MainNavbar.php');

use catechesis\Authenticator;
use catechesis\PdoDatabaseManager;
use catechesis\Utils;
use catechesis\DataValidationUtils;
use catechesis\Configurator;

use catechesis\DatabaseAccessMode;

use catechesis\Pix;
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
    $amountInput = Utils::sanitizeInput($_POST['amount'] ?? '');
    $catechism = intval($_POST['catechism'] ?? 0);
    $group = Utils::sanitizeInput($_POST['group'] ?? '');

    if(!DataValidationUtils::validatePositiveFloat($amountInput)) {
        $message = "<div class='alert alert-danger'><strong>Erro!</strong> Valor inválido.</div>";
    } else {
        $amount = floatval($amountInput);
        $status = 'confirmado';
        try {
            $db->beginTransaction();
            $db->insertPayment(Authenticator::getUsername(), $cid, $amount, $status);
            $total = $db->getTotalPaymentsByCatechumen($cid);
            $required = floatval(Configurator::getConfigurationValueOrDefault(Configurator::KEY_ENROLLMENT_PAYMENT_AMOUNT));
            if($total >= $required && $catechism > 0 && $group !== '') {
                $year = Utils::currentCatecheticalYear();
                $db->updateCatechumenEnrollmentPayment($cid, $year, $catechism, $group, true);
            }
            $db->commit();
            $message = "<div class='alert alert-success'><strong>Sucesso!</strong> Pagamento registado.</div>";
        } catch (Exception $e) {
            $db->rollBack();
            error_log($e->getMessage());
            $message = "<div class='alert alert-danger'><strong>Erro!</strong> Não foi possível registar o pagamento.</div>";
        }
}
}


if(Authenticator::isAdmin()) {
    try {
        $paymentList = $db->getEnrollmentPaymentStatusList(Utils::currentCatecheticalYear());
    } catch (Exception $e) {
        $paymentList = [];
        error_log($e->getMessage());
        $message = "<div class='alert alert-danger'><strong>Erro!</strong> Não foi possível obter a lista de pagamentos.</div>";
    }
} else {
    try {
        $payments = $db->getPaymentsByUser(Authenticator::getUsername());
    } catch (Exception $e) {
        $payments = [];
        error_log($e->getMessage());
        $message = "<div class='alert alert-danger'><strong>Erro!</strong> Não foi possível obter os pagamentos.</div>";
    }
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
  <link rel="stylesheet" type="text/css" href="css/DataTables/datatables.min.css"/>
</head>
<body>
<?php
$menu->renderHTML();
?>
<div class="container" id="contentor">
  <h2 class="no-print">Pagamentos</h2>
  <?php if ($message) echo $message; ?>
  <?php if(Authenticator::isAdmin()) { ?>
    <table id="lista-pagamentos" class="table table-striped table-bordered">
      <thead>
        <tr><th>CID</th><th>Nome</th><th>Situação</th><th>Valor em débito</th><th></th></tr>
      </thead>
      <tbody>
      <?php foreach($paymentList as $row) {
            $required = floatval(Configurator::getConfigurationValueOrDefault(Configurator::KEY_ENROLLMENT_PAYMENT_AMOUNT));
            $debt = $required - floatval($row['total_pago']);
            if($debt < 0) $debt = 0.0;
            $situacao = $debt > 0 ? 'Em débito' : 'Pago'; ?>
        <tr>
          <td><?= intval($row['cid']) ?></td>
          <td><?= Utils::sanitizeOutput($row['nome']) ?></td>
          <td><?= $situacao ?></td>
          <td>R$<?= number_format($debt, 2, ',', '.') ?></td>
          <td><button type="button" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#registerPaymentModal" onclick="preparePayment(<?= intval($row['cid']) ?>, <?= intval($row['ano_catecismo']) ?>, <?= json_encode($row['turma']) ?>, <?= $debt ?>)">Registar</button></td>
        </tr>
      <?php } ?>
      </tbody>
    </table>

    <div class="modal fade" id="registerPaymentModal" tabindex="-1" role="dialog" aria-labelledby="registarLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <form method="post" action="pagamentos.php">
            <input type="hidden" name="csrf_token" value="<?= \catechesis\Utils::getCSRFToken() ?>">
            <input type="hidden" id="modal_cid" name="cid">
            <input type="hidden" id="modal_catechism" name="catechism">
            <input type="hidden" id="modal_group" name="group">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title" id="registarLabel">Registar pagamento</h4>
            </div>
            <div class="modal-body">
              <div class="form-group">
                <label for="payment_amount">Valor</label>
                <input type="number" step="0.01" class="form-control" id="payment_amount" name="amount" required>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-primary">Registar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <script type="text/javascript">
      function preparePayment(cid, catechism, group, amount) {
        document.getElementById('modal_cid').value = cid;
        document.getElementById('modal_catechism').value = catechism;
        document.getElementById('modal_group').value = group;
        document.getElementById('payment_amount').value = amount.toFixed(2);
      }
      $(document).ready(function(){
        $('#lista-pagamentos').DataTable({
            paging: false,
            info: false,
            language: { url: 'js/DataTables/Portuguese.json' }
        });
      });
    </script>
  <?php } else {
      $total_confirmed = 0.0;
      foreach($payments as $p) {
          if($p['estado'] === 'confirmado')
              $total_confirmed += floatval($p['valor']);
      }
      $price = floatval(Configurator::getConfigurationValueOrDefault(Configurator::KEY_ENROLLMENT_PAYMENT_AMOUNT));
      $balance = $price - $total_confirmed;
      if($balance < 0) $balance = 0.0;
      $situation = $balance > 0 ? 'Em débito' : 'Pago';

      $pixPayload = null;
      try {
          $pixPayload = Pix::generatePayload($balance > 0 ? $balance : null);
      } catch (Exception $e) {

          $pixPayload = null;
      }
  ?>
    <table class="table table-striped">
      <thead>
        <tr><th>Data</th><th>Valor</th><th>Situação</th></tr>
      </thead>
      <tbody>
      <?php foreach($payments as $row) { ?>
        <tr>
          <td><?= Utils::sanitizeOutput($row['data_pagamento']) ?></td>
          <td>R$<?= number_format(floatval($row['valor']), 2, ',', '.') ?></td>
          <td><?= Utils::sanitizeOutput(ucfirst($row['estado'])) ?></td>
        </tr>
      <?php } ?>
      </tbody>
    </table>
    <p>Situação: <?= $situation ?></p>
    <p>Total pago: R$<?= number_format($total_confirmed, 2, ',', '.') ?></p>
    <p>Valor em aberto: R$<?= number_format($balance, 2, ',', '.') ?></p>

    <?php if($balance > 0 && $pixPayload) { ?>
        <div style="margin-top:20px;text-align:center;">
            <p>Pix copia e cola:</p>
            <pre style="white-space: pre-wrap; word-wrap: break-word;"><?= $pixPayload ?></pre>

        </div>
    <?php } ?>
  <?php } ?>

<?php $pageUI->renderJS(); ?>
<script type="text/javascript" src="js/DataTables/datatables.min.js"></script>
</body>
</html>
