<?php
$senha = 'theo123456';
$hash = '$2a$11$7AUSN2uoL55bIN.MfIonlujSi4rQegMGRLYpJzaOeqbKnGVe4rBSi'; // Cole o hash do banco
if (password_verify($senha, $hash)) {
  echo "Senha confere!";
} else {
  echo "Senha NÃO confere!";
}
?>