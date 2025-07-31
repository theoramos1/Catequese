<?php
require_once(__DIR__ . '/UploadHandler.php');
require_once(__DIR__ . '/../Utils.php');
require_once(__DIR__ . '/../PdoDatabaseManager.php');
require_once(__DIR__ . '/../DataValidationUtils.php');
require_once(__DIR__ . '/../../authentication/utils/authentication_verify.php');
require_once(__DIR__ . '/../../authentication/Authenticator.php');

use catechesis\Utils;
use catechesis\PdoDatabaseManager;
use catechesis\Authenticator;
use catechesis\DataValidationUtils;

class PaymentUploadHandler extends UploadHandler
{
    protected function initialize()
    {
        $this->options['accept_file_types'] = '/\.(pdf|jpe?g|png)$/i';
        parent::initialize();
    }

    protected function handle_form_data($file, $index)
    {
        $file->cid = intval(Utils::sanitizeInput($_REQUEST['cid'] ?? '0'));
        $file->amount = floatval(Utils::sanitizeInput($_REQUEST['amount'] ?? '0'));
    }

    protected function handle_file_upload($uploaded_file, $name, $size, $type, $error,
            $index = null, $content_range = null)
    {
        $cid = intval(Utils::sanitizeInput($_REQUEST['cid'] ?? '0'));
        $amountRaw = Utils::sanitizeInput($_REQUEST['amount'] ?? '0');
        $amount = floatval($amountRaw);
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $filename = 'payment_'.$cid.'_'.time().'.'.$ext;
        $sanitizedFilename = basename($filename);

        $file = parent::handle_file_upload($uploaded_file, $sanitizedFilename, $size, $type, $error,
                $index, $content_range);

        if (empty($file->error)) {
            if($cid > 0 && DataValidationUtils::validatePositiveFloat($amountRaw)) {
                $db = new PdoDatabaseManager();
                try {
                    $db->insertPayment(Authenticator::getUsername(), $cid, $amount, 'pendente', $sanitizedFilename);
                } catch (Exception $e) {
                    $file->error = 'Erro ao registar pagamento.';
                }
            } else {
                $file->error = 'Dados de pagamento inválidos.';
            }
        }
        return $file;
    }
}
