<?php
require_once(__DIR__ . '/core/PdoDatabaseManager.php');
require_once(__DIR__ . '/core/Utils.php');
require_once(__DIR__ . '/core/UserData.php');
require_once(__DIR__ . '/authentication/utils/authentication_verify.php');
require_once(__DIR__ . '/authentication/Authenticator.php');

use catechesis\PdoDatabaseManager;
use catechesis\Utils;
use catechesis\UserData;
use catechesis\Authenticator;

Authenticator::startSecureSession();
if(!Authenticator::isAppLoggedIn()) exit();

$pid = intval(Utils::sanitizeInput($_GET['pid'] ?? '0'));
$db = new PdoDatabaseManager();
try {
    $payment = $db->getPaymentById($pid);
    if(!$payment) exit();
    if(!Authenticator::isAdmin() && $payment['username'] !== Authenticator::getUsername()){
        echo "Sem permissões"; exit();
    }
    $file = $payment['comprovativo'];
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $mime = 'application/pdf';
    if($ext === 'png') $mime = 'image/png';
    if($ext === 'jpg' || $ext === 'jpeg') $mime = 'image/jpeg';
    header('Content-Type: '.$mime);
    header('Content-Disposition: inline; filename="'.$file.'"');
    readfile(UserData::getUploadDocumentsFolder().'/'.$file);
} catch(Exception $e){
    exit();
}
