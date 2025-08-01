<?php
require_once(__DIR__ . '/core/config/catechesis_config.inc.php');
require_once(__DIR__ . '/authentication/utils/authentication_verify.php');
require_once(__DIR__ . '/authentication/Authenticator.php');
require_once(__DIR__ . '/core/Utils.php');
require_once(__DIR__ . '/core/DataValidationUtils.php');
require_once(__DIR__ . '/core/PdoDatabaseManager.php');
require_once(__DIR__ . '/core/Configurator.php');
require_once(__DIR__ . '/core/PixQRCode.php');
require_once(__DIR__ . '/gui/widgets/WidgetManager.php');
require_once(__DIR__ . '/gui/widgets/Navbar/MainNavbar.php');

use catechesis\Authenticator;
use catechesis\PdoDatabaseManager;
use catechesis\Utils;
use catechesis\DataValidationUtils;
use catechesis\Configurator;

use catechesis\DatabaseAccessMode;

use catechesis\PixQRCode;
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

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pid']) && isset($_POST['action']) && Authenticator::isAdmin()) {
    if(!Utils::verifyCSRFToken($_POST['csrf_token'] ?? null)) {
        echo("<div class='alert alert-danger'><strong>ERRO!</strong> Pedido inválido.</div>");
        die();
    }
    $pid = intval(Utils::sanitizeInput($_POST['pid']));
    $obs = Utils::sanitizeInput($_POST['obs'] ?? '');
    $status = $_POST['action'] === 'aprovar' ? 'aprovado' : 'rejeitado';
    try {
        $db->updatePaymentStatus($pid, $status, Authenticator::getUsername(), $obs);
        $message = "<div class='alert alert-success'><strong>Sucesso!</strong> Pagamento atualizado.</div>";
    } catch(Exception $e) {
        error_log($e->getMessage());
        $message = "<div class='alert alert-danger'><strong>Erro!</strong> Não foi possível atualizar o pagamento.</div>";
    }
}

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

        $required = floatval(Configurator::getConfigurationValueOrDefault(Configurator::KEY_ENROLLMENT_PAYMENT_AMOUNT));
        $totalPaid = 0.0;

        try {
            $totalPaid = $db->getTotalPaymentsByCatechumen($cid);
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
        $balance = max($required - $totalPaid, 0.0);
        if($amount > $required) {
            $message = "<div class='alert alert-danger'><strong>Erro!</strong> Valor excede a taxa configurada.</div>";
        } elseif ($amount > $balance) {
            $message = "<div class='alert alert-danger'><strong>Erro!</strong> Valor excede o saldo em aberto.</div>";
        } else {
            $status = 'aprovado';
            try {
                $db->beginTransaction();
                $db->insertPayment(Authenticator::getUsername(), $cid, $amount, $status);
                $total = $db->getTotalPaymentsByCatechumen($cid);
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
        $childrenStatus = $db->getUserCatechumensPaymentStatus(Authenticator::getUsername(), Utils::currentCatecheticalYear());
    } catch (Exception $e) {
        $payments = [];
        $childrenStatus = [];
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
    <div class="form-group" style="margin:10px 0;">
      <label for="situacao_select" class="control-label">Filtrar situação:</label>
      <select id="situacao_select" class="form-control input-sm" style="width:auto; display:inline-block; margin-left:5px;">
        <option value="">Todos</option>
        <option>Em débito</option>
        <option>Pago</option>
      </select>
    </div>
    <table id="lista-pagamentos" class="table table-striped table-bordered">
      <thead>
        <tr><th>CID</th><th>Nome</th><th>Situação</th><th>Valor em aberto</th><th>Total pago</th><th></th></tr>
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
          <td>R$<?= number_format(floatval($row['total_pago']), 2, ',', '.') ?></td>
          <td><button type="button" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#registerPaymentModal" onclick="preparePayment(<?= intval($row['cid']) ?>, <?= intval($row['ano_catecismo']) ?>, <?= json_encode($row['turma']) ?>, <?= $debt ?>)">Registar</button></td>
        </tr>
      <?php } ?>
      </tbody>
    </table>

    <?php
      try {
          $recent = $db->getRecentPayments();
      } catch (Exception $e) {
          $recent = [];
      }
    ?>
    <h4>Comprovativos enviados</h4>
    <table class="table table-striped">
      <thead>
        <tr><th>Data</th><th>Nome</th><th>Valor</th><th>Estado</th><th>Comprovativo</th><th>Ação</th></tr>
      </thead>
      <tbody>
      <?php foreach($recent as $r){ ?>
        <tr>
          <td><?= Utils::sanitizeOutput($r['data_pagamento']) ?></td>
          <td><?= Utils::sanitizeOutput($r['nome']) ?></td>
          <td>R$<?= number_format(floatval($r['valor']),2,',','.') ?></td>
          <td><?= Utils::sanitizeOutput(ucfirst($r['estado'])) ?></td>
          <td><?php if($r['comprovativo']){ ?><a href="descarregaComprovativoPagamento.php?pid=<?= intval($r['pid']) ?>" target="_blank">Ver</a><?php } ?></td>
          <td>
            <form method="post" action="pagamentos.php" class="form-inline">
              <input type="hidden" name="csrf_token" value="<?= \catechesis\Utils::getCSRFToken() ?>">
              <input type="hidden" name="pid" value="<?= intval($r['pid']) ?>">
              <input type="text" name="obs" class="form-control input-sm" placeholder="Obs">
              <button name="action" value="aprovar" class="btn btn-xs btn-success">Aprovar</button>
              <button name="action" value="rejeitar" class="btn btn-xs btn-danger">Rejeitar</button>
            </form>
          </td>
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
        
        var tabela = $('#lista-pagamentos').DataTable({
            paging: true,
            pageLength: 10,
            info: false,
            language: { url: 'js/DataTables/Portuguese.json' }
        });

        $('#situacao_select').on('change', function(){
            var val = $(this).val();
            tabela.column(2).search(val ? '^' + val + '$' : '', true, false).draw();
        });
      });
    </script>
  <?php } else {
      $total_confirmed = 0.0;
      foreach($payments as $p) {
          if($p['estado'] === 'aprovado')
              $total_confirmed += floatval($p['valor']);
      }
      $price = floatval(Configurator::getConfigurationValueOrDefault(Configurator::KEY_ENROLLMENT_PAYMENT_AMOUNT));
      $balance = $price - $total_confirmed;
      if($balance < 0) $balance = 0.0;
      $situation = $balance > 0 ? 'Em débito' : 'Pago';

      $pixKey = Configurator::getConfigurationValueOrDefault(Configurator::KEY_PIX_KEY);
  ?>
  <div class="row" style="margin-top: 20px;">
    <div class="col-md-6">
      <div class="panel panel-default">
        <div class="panel-heading"><strong>Histórico de pagamentos</strong></div>
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr><th>Data</th><th>Valor</th><th>Situação</th></tr>
            </thead>
            <tbody>
              <?php if(count($payments) === 0){ ?>
                <tr><td colspan="3" class="text-center">Nenhum pagamento registrado ainda</td></tr>
              <?php } else { foreach($payments as $row) { ?>
                <tr>
                  <td><?= Utils::sanitizeOutput($row['data_pagamento']) ?></td>
                  <td>R$<?= number_format(floatval($row['valor']), 2, ',', '.') ?></td>
                  <td><?= Utils::sanitizeOutput(ucfirst($row['estado'])) ?></td>
                </tr>
              <?php }} ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="panel panel-default">
        <div class="panel-heading"><strong>Meus catequizandos</strong></div>
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr><th>Nome</th><th>Situação</th><th>Total pago</th></tr>
            </thead>
            <tbody>
            <?php if(count($childrenStatus) === 0){ ?>
              <tr><td colspan="3" class="text-center">Nenhum catequizando inscrito</td></tr>
            <?php } else { foreach($childrenStatus as $c){ ?>
              <tr>
                <td><?= Utils::sanitizeOutput($c['nome']) ?></td>
                <td><?php if($c['estado']==='pago'){ ?><span class="text-success">Pago</span><?php } else { ?><span class="text-danger">Pendente</span><?php } ?></td>
                <td>R$<?= number_format(floatval($c['total_pago']),2,',','.') ?></td>
              </tr>
            <?php }} ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="panel panel-default">
        <div class="panel-heading"><strong>Resumo financeiro</strong></div>
        <div class="panel-body">
          <div class="row">
            <div class="col-xs-6">Valor da taxa</div>
            <div class="col-xs-6 text-right">R$<?= number_format($price, 2, ',', '.') ?></div>
          </div>
          <div class="row">
            <div class="col-xs-6">Total pago</div>
            <div class="col-xs-6 text-right">R$<?= number_format($total_confirmed, 2, ',', '.') ?></div>
          </div>
          <div class="row">
            <div class="col-xs-6">Valor em aberto</div>
            <div class="col-xs-6 text-right">R$<?= number_format($balance, 2, ',', '.') ?></div>
          </div>
          <div class="row">
            <div class="col-xs-6">Situação</div>
            <div class="col-xs-6 text-right">
              <?php if($balance > 0){ ?>
                <span class="text-danger">Em débito</span>
              <?php } else { ?>
                <span class="text-success">Pago</span>
              <?php } ?>
            </div>
          </div>
        </div>
      </div>
      <?php if($pixKey){ ?>
      <div class="panel panel-default" style="margin-top: 20px;">
        <div class="panel-heading"><strong>Pix "copia e cola"</strong></div>
        <div class="panel-body">
          <p>Efetue o pagamento utilizando a chave Pix abaixo. Caso não possa pagar agora, retorne a esta página mais tarde.</p>
          <div class="input-group">
            <input type="text" class="form-control" id="pixCode" value="<?= Utils::sanitizeOutput($pixKey) ?>" readonly>
            <span class="input-group-btn">
              <button type="button" class="btn btn-default" onclick="copyPix()">Copiar código Pix</button>
            </span>
          </div>
        </div>
      </div>
      <?php } ?>
      <?php if(count($childrenStatus) > 0){ ?>
      <div class="panel panel-default" style="margin-top: 20px;">
        <div class="panel-heading"><strong>Enviar comprovativo de pagamento</strong></div>
        <div class="panel-body">
          <form action="carregarComprovativoPagamento.php" method="post" enctype="multipart/form-data" class="form-inline">
            <div class="form-group">
              <label class="sr-only" for="cid_select">Catequizando</label>
              <select name="cid" id="cid_select" class="form-control">
                <?php foreach($childrenStatus as $c){ ?>
                  <option value="<?= intval($c['cid']) ?>"><?= Utils::sanitizeOutput($c['nome']) ?></option>
                <?php } ?>
              </select>
            </div>
            <div class="form-group">
              <label class="sr-only" for="valor_pag">Valor</label>
              <input type="number" step="0.01" name="amount" id="valor_pag" class="form-control" placeholder="Valor" required>
            </div>
            <div class="form-group" style="margin-left:10px;">
              <input type="file" name="files[]" accept="application/pdf,image/jpeg,image/png" required>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-left:10px;">Enviar</button>
          </form>
        </div>
      </div>
      <?php } ?>
    </div>
  </div>
  <script type="text/javascript">
    function copyPix(){
      var field = document.getElementById('pixCode');
      field.select();
      field.setSelectionRange(0, 99999);
      if(navigator.clipboard){
        navigator.clipboard.writeText(field.value);
      } else {
        document.execCommand('copy');
      }
    }
  </script>
  <?php } ?>

<?php $pageUI->renderJS(); ?>
<script type="text/javascript" src="js/DataTables/datatables.min.js"></script>
</body>
</html>
