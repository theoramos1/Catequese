<?php

namespace catechesis\gui;



require_once(__DIR__ . '/../Widget.php');
require_once(__DIR__ . '/../ModalDialog/ModalDialogWidget.php');
require_once(__DIR__ . "/../../common/Animation.php");
require_once(__DIR__ . "/../../common/Button.php");
require_once(__DIR__ . "/../../../authentication/Authenticator.php");

use Dompdf\Exception;
use catechesis\Authenticator;


class UpdateDialogWidget extends ModalDialogWidget
{
    public function __construct(?string $id = null)
    {
        parent::__construct($id);

        $this->setSize(ModalDialogWidget::SIZE_MEDIUM);
        $this->setTitle("Atualização do CatecheSis");
        if(Authenticator::isAdmin())
            $this->addButton(new Button("Atualizar", ButtonType::PRIMARY, "window.open('updater/index.php?setup_step=-1')"));
        $this->addButton(new Button("Fechar", ButtonType::SECONDARY));
    }


    /**
     * Setting the body of this modal dialog is unsupported.
     * @param string $contents
     * @return $this
     */
    public function setBodyContents(string $contents)
    {
        throw new Exception("UpdateDialogWidget: The body of a UpdateDialogWidget cannot be set.");
    }

    /**
     * Renders the body of the about dialog.
     * @return void
     */
    protected function renderBodyContents()
    {
        ?>
        <div style="overflow: hidden;">
            <div class="container col-xs-12">
                <p>Está disponível uma nova versão do CatecheSis!</p>
                <div class="col-xs-2">
                    <img src="img/CatecheSis_Logo_About.svg" class="img-responsive">
                </div>
                <div class="col-xs-10">
                    <div class="col-xs-12">
                        <div style="margin-bottom: 10px;"></div>
                        <table>
                            <thead>
                                <th></th>
                                <th></th>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="padding-right: 20px;">Versão instalada:</td>
                                    <td><?= $_SESSION['CURRENT_VERSION']  ?></td>
                                </tr>
                                <tr>
                                    <td style="padding-right: 10px">Versão disponível:</td>
                                    <td><?= $_SESSION['LATEST_AVAILABLE_VERSION'] ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div style="margin-bottom: 40px;"></div>
                <p><a href="<?= $_SESSION['UPDATE_CHANGELOG_URL'] ?>" target="_blank">Saiba mais</a> acerca das novidades incluídas atualização.</p>

                <?php
                if(!Authenticator::isAdmin())
                {
                    ?>
                    <p>Peça a um administrador para atualizar o CatecheSis na sua paróquia.</p>
                    <?php
                }
                ?>
            </div>
            <div class="clearfix"></div>
        </div>
        <?php
    }
}