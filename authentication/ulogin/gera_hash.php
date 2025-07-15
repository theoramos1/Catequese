<?php
define('UL_PWD_ROUNDS', 11); // valor padrão geralmente é 10, 11 ou 12

require_once(__DIR__ . '/Utils.inc.php');
require_once(__DIR__ . '/Password.inc.php');

$senha = 'theo123456'; // a senha que você quer
$hash = ulPassword::Hash($senha);
echo "Hash gerado para '$senha':<br>";
echo "<textarea style='width:100%;height:40px;'>$hash</textarea>";
?>
