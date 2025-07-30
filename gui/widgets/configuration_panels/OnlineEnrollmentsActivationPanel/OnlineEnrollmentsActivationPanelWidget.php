<?php

namespace catechesis\gui;

require_once(__DIR__ . '/../AbstractSettingsPanel/AbstractSettingsPanelWidget.php');
require_once(__DIR__ . '/../../../../authentication/Authenticator.php');
require_once(__DIR__ . '/../../../../core/PdoDatabaseManager.php');
require_once(__DIR__ . '/../../../../core/Configurator.php');
require_once(__DIR__ . '/../../../../core/Utils.php');
require_once(__DIR__ . '/../../../../core/DataValidationUtils.php');

use catechesis\Authenticator;
use catechesis\Configurator;
use catechesis\DataValidationUtils;
use catechesis\PdoDatabaseManager;
use catechesis\Utils;
use uLogin;


/**
 * A panel providing a switch that allows to open or close the online enrollments public page.
 */
class OnlineEnrollmentsActivationPanelWidget extends AbstractSettingsPanelWidget
{
    public static $ACTION_CHANGE_STATUS = "edit_online_enrollments_status";
    public static $ACTION_CHANGE_DETAILS = "edit_online_enrollments_details";
    private /*bool*/ $showAllSettings = true;

    public function __construct(?string $id = null, bool $showAllSettings = true)
    {
        parent::__construct($id, true);

        // Declare this widget's dependencies
        $this->addCSSDependency('css/bootstrap-switch.css');
        $this->addCSSDependency("css/quill-1.3.6/quill.snow.css");

        $this->addJSDependency('js/bootstrap-switch.js');
        $this->addJSDependency('js/jQuery-Mask-Plugin-1.14.16/jquery.mask.min.js');
        $this->addJSDependency("js/quill-1.3.6/quill.min.js");
        $this->addJSDependency('gui/widgets/configuration_panels/OnlineEnrollmentsActivationPanel/OnlineEnrollmentsActivationPanelWidget.js');

        $this->showAllSettings = $showAllSettings;

        parent::setTitle("Inscrições online");
        //parent::setURL($_SERVER['PHP_SELF']);
        parent::useHeaderButtons($showAllSettings);
    }


    protected function renderBody()
    {
        $enrollmentsOpen = false;
        $enrollmentCustomText = null;
        $showPaymentData = false;
        $paymentEntity = null;
        $paymentReference = null;
        $paymentAmount = 0.0;
        $acceptDonations = true;
        $paymentProof = null;
        $pixReceiver = null;
        $pixCity = null;
        $pixKey = null;
        $pixApiUrl = null;
        $pixApiToken = null;

        //Load configuration settings
        try
        {
            $enrollmentsOpen = Configurator::getConfigurationValueOrDefault(Configurator::KEY_ONLINE_ENROLLMENTS_OPEN);
            $enrollmentCustomText =  Configurator::getConfigurationValueOrDefault(Configurator::KEY_ENROLLMENT_CUSTOM_TEXT);
            $showPaymentData =  Configurator::getConfigurationValueOrDefault(Configurator::KEY_ENROLLMENT_SHOW_PAYMENT_DATA);
            $paymentEntity =  Configurator::getConfigurationValueOrDefault(Configurator::KEY_ENROLLMENT_PAYMENT_ENTITY);
            $paymentReference =  Configurator::getConfigurationValueOrDefault(Configurator::KEY_ENROLLMENT_PAYMENT_REFERENCE);
            $paymentAmount =  Configurator::getConfigurationValueOrDefault(Configurator::KEY_ENROLLMENT_PAYMENT_AMOUNT);
            $acceptDonations =  Configurator::getConfigurationValueOrDefault(Configurator::KEY_ENROLLMENT_PAYMENT_ACCEPT_BIGGER_DONATIONS);
            $paymentProof =  Configurator::getConfigurationValueOrDefault(Configurator::KEY_ENROLLMENT_PAYMENT_PROOF);
            $pixReceiver = Configurator::getConfigurationValueOrDefault(Configurator::KEY_PIX_RECEIVER);
            $pixCity = Configurator::getConfigurationValueOrDefault(Configurator::KEY_PIX_CITY);
            $pixKey = Configurator::getConfigurationValueOrDefault(Configurator::KEY_PIX_KEY);
            $pixApiUrl = Configurator::getConfigurationValueOrDefault(Configurator::KEY_PIX_API_URL);
            $pixApiToken = Configurator::getConfigurationValueOrDefault(Configurator::KEY_PIX_API_TOKEN);
        }
        catch(\Exception $e)
        {
            echo("<div class=\"alert alert-danger\"><a href=\"#\" class=\"close\" data-dismiss=\"alert\">&times;</a><strong>Erro!</strong> " . $e->getMessage() . "</div>");
        }
        ?>
        <div class="col-md-12">
            <p>Utilize o interruptor abaixo para abrir/fechar as inscrições e renovações online para o público.</p>
            <div><input type="checkbox" class="<?= $this->getID() ?>_checkbox-admin" id="<?= $this->getID() ?>_online_enrollments_switch" name="online_enrollments_switch" <?php if($enrollmentsOpen) echo("checked"); ?>> <span><b> Período de inscrições <?php if($enrollmentsOpen) echo("aberto"); else echo("fechado"); ?></b></span></div>
            <?php
            if($enrollmentsOpen)
            {
                $enrollmentURL = Utils::getBaseUrl() . "publico/inscricoes.php";
                ?>
                <div class="clearfix" style="margin-bottom: 20px"></div>
                <ol class="breadcrumb">
                    <li><p>As inscrições estão disponíveis para o público no endereço <a target="_blank" rel="noopener noreferrer" href="<?php echo(Utils::sanitizeOutput($enrollmentURL));?>"><?php echo(Utils::sanitizeOutput($enrollmentURL));?></a></p></li>
                </ol>
            <?php
            }?>
        </div>

        <?php
        if($this->showAllSettings)
        {
        ?>
            <div class="row clearfix" style="margin-bottom: 20px"></div>
            <hr>
            <div class="row clearfix" style="margin-bottom: 20px"></div>

            <!-- Custom info text -->
            <div class="col-md-12">
                <label for="<?=$this->getID()?>_info_text">Texto informativo a ser mostrado durante a inscrição/renovação:</label>
                <p id="<?=$this->getID()?>_info_text_help_block" class="help-block">Este campo é opcional. Se não o preencher aqui, não será mostrado durante a inscrição/renovação.</p>
                <!-- Quill editor -->
                <div id="<?=$this->getID()?>_standalone-container">
                    <div id="<?=$this->getID()?>_toolbar_container" style="pointer-events: none">
                        <span class="ql-formats">
                          <button class="ql-bold" title="Negrito"></button>
                          <button class="ql-italic" title="Itálico"></button>
                          <button class="ql-underline" title="Sublinhado"></button>
                        </span>
                    </div>
                    <div id="<?=$this->getID()?>_editor_container"></div>
                </div>
                <input type="hidden" class="form-control" id="<?=$this->getID()?>_info_text" name="info_text" value="<?= $enrollmentCustomText ?>" readonly>
                <input type="hidden" class="form-control" id="<?=$this->getID()?>_info_text_backup" value="<?= $enrollmentCustomText ?>" readonly>
            </div>

            <div class="row clearfix" style="margin-bottom: 40px"></div>

            <!-- Payment data -->
            <div class="form-group">
                <div class="col-md-12">
                    <div class="checkbox disabled" id="<?=$this->getID()?>_div_enable_payment">
                        <label for="<?=$this->getID()?>_enable_payment" style="display: contents;">
                            <input type="checkbox" class="" id="<?=$this->getID()?>_enable_payment" name="enable_payment" placeholder="" style="cursor: auto;" aria-describedby="<?=$this->getID()?>_enable_payment_help_block" onchange="onCheckShowPaymentInfo('<?=$this->getID()?>');" <?php if($showPaymentData) echo("checked"); ?> disabled>
                            Apresentar referência multibanco para pagamento
                        </label>
                        <input type="hidden" class="form-control" id="<?=$this->getID()?>_enable_payment_backup" value="<?php if($showPaymentData) echo("on"); else echo("off"); ?>" readonly>
                        <p id="<?=$this->getID()?>_enable_payment_help_block" class="help-block">Mostra os detalhes para pagamento por multibanco ao utilizador, na página de confirmação de inscrição/renovação, depois desta ser submetida.</p>
                    </div>
                </div>
                <div id="<?=$this->getID()?>_payment_info_collapsible" class="collapse <?php if($showPaymentData) echo("in"); ?>">
                    <div class="row clearfix" style="margin-bottom: 10px;"></div>
                    <div class="col-md-4">
                        <label for="<?=$this->getID()?>_entity">Entidade:</label>
                        <input type="text" class="form-control" id="<?=$this->getID()?>_entity" name="entity" placeholder="" data-mask="00000" style="cursor: auto;" value="<?= $paymentEntity ?>" readonly>
                        <input type="hidden" class="form-control" id="<?=$this->getID()?>_entity_backup" value="<?= $paymentEntity ?>" readonly>
                    </div>
                    <div class="row clearfix" style="margin-bottom: 10px;"></div>
                    <div class="col-md-4">
                        <label for="<?=$this->getID()?>_reference">Referência:</label>
                        <input type="text" class="form-control" id="<?=$this->getID()?>_reference" name="reference" placeholder="" data-mask="000 000 000" style="cursor: auto;" value="<?= $paymentReference ?>" readonly>
                        <input type="hidden" class="form-control" id="<?=$this->getID()?>_reference_backup" value="<?= $paymentReference ?>" readonly>
                    </div>
                    <div class="row clearfix" style="margin-bottom: 10px;"></div>
                    <div class="col-md-4">
                        <label for="<?=$this->getID()?>_amount">Montante:</label>
                        <input type="number" min="0" step="0.01" class="form-control" id="<?=$this->getID()?>_amount" name="amount" placeholder="" style="cursor: auto;" value="<?= number_format((float)$paymentAmount, 2, '.', ''); ?>" readonly>
                        <input type="hidden" class="form-control" id="<?=$this->getID()?>_amount_backup" value="<?= number_format((float)$paymentAmount, 2, '.', ''); ?>" readonly>

                        <div class="checkbox disabled" id="<?=$this->getID()?>_div_allow_donations">
                            <label for="<?=$this->getID()?>_allow_donations">
                                <input type="checkbox" class="" id="<?=$this->getID()?>_allow_donations" name="allow_donations" placeholder="" style="cursor: auto;" <?php if($acceptDonations) echo("checked"); ?> disabled>
                                Permitir donativos maiores <span class="fas fa-question-circle" data-toggle="popover" data-placement="top" data-content='Se selecionado, é acrescentada a frase "<i>(ou mais, se assim o entender)</i>" à frente do campo montante, informando o utilizador que o montande indicado é meramente informativo e que são aceites donativos.'></span>
                            </label>
                            <input type="hidden" class="form-control" id="<?=$this->getID()?>_allow_donations_backup" value="<?php if($acceptDonations) echo("on"); else echo("off"); ?>" readonly>
                        </div>
                    </div>
                    <div class="row clearfix" style="margin-bottom: 20px;"></div>
                    <div class="col-md-6">
                        <label for="<?=$this->getID()?>_proof">Enviar comprovativo para:</label> <span class="fas fa-question-circle" data-toggle="popover" data-placement="top" data-content='Introduza um endereço de e-mail, uma URL (ex: para uma pasta na cloud), um endereço postal, ou simplesmente o nome da pessoa responsável.'></span>
                        <input type="text" class="form-control" id="<?=$this->getID()?>_proof" name="proof" placeholder="" style="cursor: auto;" value="<?= $paymentProof ?>" readonly>
                        <input type="hidden" class="form-control" id="<?=$this->getID()?>_proof_backup" value="<?= $paymentProof ?>" readonly>
                    </div>

                    <div class="row clearfix" style="margin-bottom: 10px;"></div>
                    <div class="col-md-4">
                        <label for="<?=$this->getID()?>_pix_receiver">Destinatário PIX:</label>
                        <input type="text" class="form-control" id="<?=$this->getID()?>_pix_receiver" name="pix_receiver" placeholder="" style="cursor: auto;" value="<?= $pixReceiver ?>" readonly>
                        <input type="hidden" class="form-control" id="<?=$this->getID()?>_pix_receiver_backup" value="<?= $pixReceiver ?>" readonly>
                    </div>
                    <div class="row clearfix" style="margin-bottom: 10px;"></div>
                    <div class="col-md-4">
                        <label for="<?=$this->getID()?>_pix_city">Cidade PIX:</label>
                        <input type="text" class="form-control" id="<?=$this->getID()?>_pix_city" name="pix_city" placeholder="" style="cursor: auto;" value="<?= $pixCity ?>" readonly>
                        <input type="hidden" class="form-control" id="<?=$this->getID()?>_pix_city_backup" value="<?= $pixCity ?>" readonly>
                    </div>
                    <div class="row clearfix" style="margin-bottom: 10px;"></div>
                    <div class="col-md-4">
                        <label for="<?=$this->getID()?>_pix_key">Chave PIX:</label>
                        <input type="text" class="form-control" id="<?=$this->getID()?>_pix_key" name="pix_key" placeholder="" style="cursor: auto;" value="<?= $pixKey ?>" readonly>
                        <input type="hidden" class="form-control" id="<?=$this->getID()?>_pix_key_backup" value="<?= $pixKey ?>" readonly>
                    </div>

                    <div class="row clearfix" style="margin-bottom: 10px;"></div>
                    <div class="col-md-6">
                        <label for="<?=$this->getID()?>_pix_api_url">PIX API URL:</label>
                        <input type="text" class="form-control" id="<?=$this->getID()?>_pix_api_url" name="pix_api_url" placeholder="" style="cursor: auto;" value="<?= $pixApiUrl ?>" readonly>
                        <input type="hidden" class="form-control" id="<?=$this->getID()?>_pix_api_url_backup" value="<?= $pixApiUrl ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="<?=$this->getID()?>_pix_api_token">PIX API Token:</label>
                        <input type="text" class="form-control" id="<?=$this->getID()?>_pix_api_token" name="pix_api_token" placeholder="" style="cursor: auto;" value="<?= $pixApiToken ?>" readonly>
                        <input type="hidden" class="form-control" id="<?=$this->getID()?>_pix_api_token_backup" value="<?= $pixApiToken ?>" readonly>
                    </div>
                </div>
            </div>

        <?php
        }
        else
        {
        ?>
            <div class="col-md-12">
                <div class="alert alert-info" role="alert">
                    <span><b><span class="fas fa-info-circle"></b>  Aceda à página de <a href="configuracoes.php">Configurações</a> para ajustar mais opções.</span>
                </div>
            </div>
        <?php
        }
        ?>

        <input type="hidden" id="<?=$this->getID()?>_form_action" name="action" value="<?= self::$ACTION_CHANGE_STATUS ?>">

        <?php
    }




    /**
     * @inherit
     */
    protected function onEdit()
    {?>
        <?=$this->getID()?>_quill.root.innerHTML = document.getElementById("<?=$this->getID()?>_info_text").value;
        <?=$this->getID()?>_quill.enable();
        document.getElementById("<?=$this->getID()?>_toolbar_container").style = 'pointer-events: initial'; //Enable Quill toolbar
        document.getElementById("<?=$this->getID()?>_enable_payment").disabled = false;
        document.getElementById("<?=$this->getID()?>_div_enable_payment").classList.remove("disabled");
        document.getElementById("<?=$this->getID()?>_entity").readOnly = false;
        document.getElementById("<?=$this->getID()?>_reference").readOnly = false;
        document.getElementById("<?=$this->getID()?>_amount").readOnly = false;
        document.getElementById("<?=$this->getID()?>_allow_donations").disabled = false;
        document.getElementById("<?=$this->getID()?>_div_allow_donations").classList.remove("disabled");
        document.getElementById("<?=$this->getID()?>_proof").readOnly = false;
        document.getElementById("<?=$this->getID()?>_pix_receiver").readOnly = false;
        document.getElementById("<?=$this->getID()?>_pix_city").readOnly = false;
        document.getElementById("<?=$this->getID()?>_pix_key").readOnly = false;
        document.getElementById("<?=$this->getID()?>_pix_api_url").readOnly = false;
        document.getElementById("<?=$this->getID()?>_pix_api_token").readOnly = false;
        document.getElementById("<?=$this->getID()?>_form_action").value = "<?= self::$ACTION_CHANGE_DETAILS ?>"; //Change form mode to details
        <?php
    }


    /**
     * @inherit
     */
    protected function onCancel()
    {
        ?>
        <?=$this->getID()?>_quill.disable();
        document.getElementById("<?=$this->getID()?>_toolbar_container").style = 'pointer-events: none'; //Disable Quill toolbar
        document.getElementById("<?=$this->getID()?>_enable_payment").disabled = true;
        document.getElementById("<?=$this->getID()?>_div_enable_payment").classList.add("disabled");
        document.getElementById("<?=$this->getID()?>_entity").readOnly = true;
        document.getElementById("<?=$this->getID()?>_reference").readOnly = true;
        document.getElementById("<?=$this->getID()?>_amount").readOnly = true;
        document.getElementById("<?=$this->getID()?>_allow_donations").disabled = true;
        document.getElementById("<?=$this->getID()?>_div_allow_donations").classList.add("disabled");
        document.getElementById("<?=$this->getID()?>_proof").readOnly = true;
        document.getElementById("<?=$this->getID()?>_pix_receiver").readOnly = true;
        document.getElementById("<?=$this->getID()?>_pix_city").readOnly = true;
        document.getElementById("<?=$this->getID()?>_pix_key").readOnly = true;
        document.getElementById("<?=$this->getID()?>_pix_api_url").readOnly = true;
        document.getElementById("<?=$this->getID()?>_pix_api_token").readOnly = true;

        <?=$this->getID()?>_quill.root.innerHTML = document.getElementById("<?=$this->getID()?>_info_text_backup").value;
        document.getElementById("<?=$this->getID()?>_entity").value = document.getElementById("<?=$this->getID()?>_entity_backup").value;
        document.getElementById("<?=$this->getID()?>_reference").value = document.getElementById("<?=$this->getID()?>_reference_backup").value;
        document.getElementById("<?=$this->getID()?>_amount").value = document.getElementById("<?=$this->getID()?>_amount_backup").value;
        document.getElementById("<?=$this->getID()?>_proof").value = document.getElementById("<?=$this->getID()?>_proof_backup").value;
        document.getElementById("<?=$this->getID()?>_pix_receiver").value = document.getElementById("<?=$this->getID()?>_pix_receiver_backup").value;
        document.getElementById("<?=$this->getID()?>_pix_city").value = document.getElementById("<?=$this->getID()?>_pix_city_backup").value;
        document.getElementById("<?=$this->getID()?>_pix_key").value = document.getElementById("<?=$this->getID()?>_pix_key_backup").value;
        document.getElementById("<?=$this->getID()?>_pix_api_url").value = document.getElementById("<?=$this->getID()?>_pix_api_url_backup").value;
        document.getElementById("<?=$this->getID()?>_pix_api_token").value = document.getElementById("<?=$this->getID()?>_pix_api_token_backup").value;

        if(document.getElementById("<?=$this->getID()?>_enable_payment_backup").value == "on")
            document.getElementById("<?=$this->getID()?>_enable_payment").checked = true;
        else
            document.getElementById("<?=$this->getID()?>_enable_payment").checked = false;

        if(document.getElementById("<?=$this->getID()?>_allow_donations_backup").value == "on")
            document.getElementById("<?=$this->getID()?>_allow_donations").checked = true;
        else
            document.getElementById("<?=$this->getID()?>_allow_donations").checked = false;

        onCheckShowPaymentInfo('<?=$this->getID()?>');

        document.getElementById("<?=$this->getID()?>_form_action").value = "<?= self::$ACTION_CHANGE_STATUS ?>";  //Change form mode to open/close online enrollments
        <?php
    }


    /**
     * @inherit
     */
    protected function onSubmit()
    {?>
        if(<?=$this->getID()?>_quill.getText().trim().length > 0)
            document.getElementById("<?=$this->getID()?>_info_text").value = <?=$this->getID()?>_quill.root.innerHTML;
        else
            document.getElementById("<?=$this->getID()?>_info_text").value = ''; //Avoid the "<p><br></p>" that Quill adds to empty contents
    <?php
    }


    /**
     * @inherit
     */
    public function renderJS()
    {
        parent::renderJS();
        ?>
        <script>
            //Setup bootstrap-switch
            $(function () {
                $("[class='<?= $this->getID() ?>_checkbox-admin']").bootstrapSwitch({size: 'small',
                    onText: 'On',
                    offText: 'Off'
                });
            });

            $('input[class="<?= $this->getID() ?>_checkbox-admin"]').on('switchChange.bootstrapSwitch', function(event, state) {

                $('#form_settings_<?= $this->getID() ?>').submit();
            });


            //Setup Quill HTML editor
            var <?=$this->getID()?>_quill = new Quill('#<?=$this->getID()?>_editor_container', {
                modules: {
                    toolbar: '#<?=$this->getID()?>_toolbar_container',
                    clipboard: {
                        matchVisual: false //Avoids Quill writing "<p><br></p>" when the content is empty
                    }
                },
                placeholder: 'Pode escrever aqui informações relativamente ao pagamento da inscrição, por exemplo.',
                readOnly: true,
                theme: 'snow'
            });

            <?=$this->getID()?>_quill.root.innerHTML = document.getElementById("<?=$this->getID()?>_info_text").value;
            if(document.getElementById("<?=$this->getID()?>_info_text").value !== '')
            {
                <?=$this->getID()?>_quill.root.dataset.placeholder = ''; //Remove placeholder
            }
        </script>
        <?php
    }



    /**
     * @inheritDoc
     */
    public function handlePost()
    {
        if($this->requires_admin_privileges && !Authenticator::isAdmin())
            return; //Do not render this widget if the user is not admin and it requires admin priviledges

        if(!Utils::verifyCSRFToken($_POST['csrf_token'] ?? null))
        {
            echo("<div class=\"alert alert-danger\"><strong>Erro!</strong> Pedido inválido.</div>");
            return;
        }

        if($_POST['action'] == self::$ACTION_CHANGE_STATUS && Authenticator::isAdmin())
        {
            // Enable/disable online enrollments

            $setting = Utils::sanitizeInput($_POST['online_enrollments_switch']);

            if($setting=="on")
            {
                try
                {
                    Configurator::setConfigurationValue(Configurator::KEY_ONLINE_ENROLLMENTS_OPEN, true);
                    writeLogEntry("Abriu o período de inscrições/renovações de matrícula online.");
                    echo("<div class=\"alert alert-success\"><a href=\"#\" class=\"close\" data-dismiss=\"alert\">&times;</a><strong>Sucesso!</strong> Abriu o período de inscrições/renovações de matrícula online.</div>");
                    $periodo_activo = true;
                }
                catch (\Exception $e)
                {
                    echo("<div class=\"alert alert-danger\"><a href=\"#\" class=\"close\" data-dismiss=\"alert\">&times;</a><strong>Erro!</strong> " . $e->getMessage() . "</div>");
                }
            }
            else
            {
                try
                {
                    Configurator::setConfigurationValue(Configurator::KEY_ONLINE_ENROLLMENTS_OPEN, false);
                    writeLogEntry("Fechou o período de inscrições/renovações de matrícula online.");
                    echo("<div class=\"alert alert-success\"><a href=\"#\" class=\"close\" data-dismiss=\"alert\">&times;</a><strong>Sucesso!</strong> Fechou o período de inscrições/renovações de matrícula online.</div>");
                    $periodo_activo = false;
                }
                catch (\Exception $e)
                {
                    echo("<div class=\"alert alert-danger\"><a href=\"#\" class=\"close\" data-dismiss=\"alert\">&times;</a><strong>Erro!</strong> " . $e->getMessage() . "</div>");
                }
            }
        }
        else if($_POST['action'] == self::$ACTION_CHANGE_DETAILS && Authenticator::isAdmin())
        {
            // Change online enrollments details

            $enrollmentCustomText =  Utils::sanitizeKeepFormattingTags($_POST['info_text']);
            $showPaymentData =  Utils::sanitizeInput($_POST['enable_payment']); $showPaymentData = ($showPaymentData=="on");
            $paymentEntity =  Utils::removeWhiteSpaces(Utils::sanitizeInput($_POST['entity']));
            $paymentReference = Utils::removeWhiteSpaces(Utils::sanitizeInput($_POST['reference']));
            $paymentAmount =  Utils::sanitizeInput($_POST['amount']);
            $acceptDonations = Utils::sanitizeInput($_POST['allow_donations']); $acceptDonations = ($acceptDonations=="on");
            $paymentProof = Utils::sanitizeInput($_POST['proof']);
            $pixReceiver = Utils::sanitizeInput($_POST['pix_receiver']);
            $pixCity = Utils::sanitizeInput($_POST['pix_city']);
            $pixKey = Utils::sanitizeInput($_POST['pix_key']);
            $pixApiUrl = Utils::sanitizeInput($_POST['pix_api_url']);
            $pixApiToken = Utils::sanitizeInput($_POST['pix_api_token']);

            $inputs_valid = true;
            if($showPaymentData)
            {
                if(!DataValidationUtils::validateMbEntity($paymentEntity))
                {
                    echo("<div class=\"alert alert-danger\"><a href=\"#\" class=\"close\" data-dismiss=\"alert\">&times;</a><strong>Erro!</strong> A entidade indicada é inválida.</div>");
                    $inputs_valid = false;
                }

                if(!DataValidationUtils::validateMbReference($paymentReference))
                {
                    echo("<div class=\"alert alert-danger\"><a href=\"#\" class=\"close\" data-dismiss=\"alert\">&times;</a><strong>Erro!</strong> A referência indicada é inválida.</div>");
                    $inputs_valid = false;
                }

                if(!DataValidationUtils::validatePositiveFloat($paymentAmount))
                {
                    echo("<div class=\"alert alert-danger\"><a href=\"#\" class=\"close\" data-dismiss=\"alert\">&times;</a><strong>Erro!</strong> O montante indicado é inválido.</div>");
                    $inputs_valid = false;
                }
            }

            $paymentEntity = intval($paymentEntity);
            $paymentReference = intval($paymentReference);
            $paymentAmount = floatval($paymentAmount);

            if($inputs_valid)
            {
                try
                {
                    Configurator::setConfigurationValue(Configurator::KEY_ENROLLMENT_CUSTOM_TEXT, $enrollmentCustomText);
                    Configurator::setConfigurationValue(Configurator::KEY_ENROLLMENT_SHOW_PAYMENT_DATA, $showPaymentData);
                    Configurator::setConfigurationValue(Configurator::KEY_ENROLLMENT_PAYMENT_ENTITY, $paymentEntity);
                    Configurator::setConfigurationValue(Configurator::KEY_ENROLLMENT_PAYMENT_REFERENCE, $paymentReference);
                    Configurator::setConfigurationValue(Configurator::KEY_ENROLLMENT_PAYMENT_AMOUNT, $paymentAmount);
                    Configurator::setConfigurationValue(Configurator::KEY_ENROLLMENT_PAYMENT_ACCEPT_BIGGER_DONATIONS, $acceptDonations);
                    Configurator::setConfigurationValue(Configurator::KEY_ENROLLMENT_PAYMENT_PROOF, $paymentProof);
                    Configurator::setConfigurationValue(Configurator::KEY_PIX_RECEIVER, $pixReceiver);
                    Configurator::setConfigurationValue(Configurator::KEY_PIX_CITY, $pixCity);
                    Configurator::setConfigurationValue(Configurator::KEY_PIX_KEY, $pixKey);
                    Configurator::setConfigurationValue(Configurator::KEY_PIX_API_URL, $pixApiUrl);
                    Configurator::setConfigurationValue(Configurator::KEY_PIX_API_TOKEN, $pixApiToken);

                    writeLogEntry("Modificou configurações das inscrições/renovações de matrícula online.");
                    echo("<div class=\"alert alert-success\"><a href=\"#\" class=\"close\" data-dismiss=\"alert\">&times;</a><strong>Sucesso!</strong> Modificou as configurações das inscrições/renovações de matrícula online.</div>");
                }
                catch (\Exception $e)
                {
                    echo("<div class=\"alert alert-danger\"><a href=\"#\" class=\"close\" data-dismiss=\"alert\">&times;</a><strong>Erro!</strong> " . $e->getMessage() . "</div>");
                }
            }
        }
    }
}