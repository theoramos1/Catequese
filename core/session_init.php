<?php
ini_set('session.save_handler', 'files');
ini_set('session.save_path', 'C:/xampp/tmp');
if (session_status() === PHP_SESSION_NONE) {
    session_name('SSESID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        // NÃO defina 'domain' em localhost, deixe o padrão!
        // 'domain' => '',  // REMOVA ESSA LINHA!
        'secure' => true, // Se usar https, deve ser true!
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

?>