<?php

require_once(__DIR__ . '/core/config/catechesis_config.inc.php');
require_once(__DIR__ . '/authentication/utils/authentication_verify.php');
require_once(__DIR__ . '/authentication/Authenticator.php');
require_once(__DIR__ . '/core/Configurator.php');
require_once(__DIR__ . '/core/Utils.php');
require_once(__DIR__ . '/core/log_functions.php');
require_once(__DIR__ . "/core/PdoDatabaseManager.php");
require_once(__DIR__ . "/gui/widgets/WidgetManager.php");
require_once(__DIR__ . "/gui/widgets/configuration_panels/UserAccountConfigurationPanel/UserAccountConfigurationPanelWidget.php");
require_once(__DIR__ . "/gui/widgets/configuration_panels/OnlineEnrollmentsActivationPanel/OnlineEnrollmentsActivationPanelWidget.php");
require_once(__DIR__ . "/gui/widgets/configuration_panels/CatechumensEvaluationActivationPanel/CatechumensEvaluationActivationPanelWidget.php");
require_once(__DIR__ . "/gui/widgets/configuration_panels/CatechesisSettingsPanel/CatechesisSettingsPanelWidget.php");
require_once(__DIR__ . "/gui/widgets/configuration_panels/ParishSettingsPanel/ParishSettingsPanelWidget.php");
require_once(__DIR__ . "/gui/widgets/configuration_panels/GDPRParishSettingsPanel/GDPRParishSettingsPanelWidget.php");
require_once(__DIR__ . "/gui/widgets/configuration_panels/NextcloudIntegrationConfigurationPanel/NextcloudIntegrationConfigurationPanelWidget.php");
require_once(__DIR__ . "/gui/widgets/configuration_panels/FrontPageCustomizationPanel/FrontPageCustomizationPanelWidget.php");
require_once(__DIR__ . '/gui/widgets/Navbar/MainNavbar.php');

use catechesis\Authenticator;
use catechesis\Configurator;
use catechesis\PdoDatabaseManager;
use catechesis\Utils;
use catechesis\gui\WidgetManager;
use catechesis\gui\MainNavbar;
use catechesis\gui\MainNavbar\MENU_OPTION;
use catechesis\gui\UserAccountConfigurationPanelWidget;
use catechesis\gui\OnlineEnrollmentsActivationPanelWidget;
use catechesis\gui\CatechumensEvaluationActivationPanelWidget;
use catechesis\gui\ParishSettingsPanelWidget;
use catechesis\gui\GDPRParishSettingsPanelWidget;
use catechesis\gui\CatechesisSettingsPanelWidget;
use catechesis\gui\NextcloudIntegrationConfigurationPanelWidget;
use catechesis\gui\FrontPageCustomizationPanelWidget;


// Create the widgets manager
$pageUI = new WidgetManager();

$menu = new MainNavbar(null, MENU_OPTION::SETTINGS);
$pageUI->addWidget($menu);

$settingsPanels = array(); //Array to store all the panels, to process and render them later

// Instantiate the widgets used in this page and register them in the manager and in the array
$configurationWidgets = array(
                            new UserAccountConfigurationPanelWidget(),
                            new OnlineEnrollmentsActivationPanelWidget(),
                            new CatechumensEvaluationActivationPanelWidget(),
                            new CatechesisSettingsPanelWidget(),
                            new NextcloudIntegrationConfigurationPanelWidget(),
                            new FrontPageCustomizationPanelWidget(),
                            new ParishSettingsPanelWidget(),
                            new GDPRParishSettingsPanelWidget()
                        );

$settingsPanels = array(); //Array to store all the panels, to process and render them later

// Add widgets to page according to user permissions
foreach($configurationWidgets as $panel)
{
    if(Authenticator::isAdmin() || !$panel->requiresAdminPriviledges())
    {
        $pageUI->addWidget($panel);
        $settingsPanels[] = $panel;
    }
}


//Change immediately the user name on the menu bar, in case it was changed
$action = Utils::sanitizeInput($_POST['action']);
if($action == UserAccountConfigurationPanelWidget::$ACTION_PARAMETER)
{
    $_SESSION['nome_utilizador'] = Utils::sanitizeInput($_POST['nome']);
}

?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <title>Configurações</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php $pageUI->renderCSS(); // Render the widgets' CSS ?>
  <link rel="stylesheet" href="css/custom-navbar-colors.css">
    <link rel="stylesheet" href="css/side-nav.css">

  
  <style>
  	@media print
	{    
	    .no-print, .no-print *
	    {
		display: none !important;
	    }

	    a[href]:after {
		    content: none;
		  }
		  
	    /*@page {
		    size: 297mm 210mm;*/ /* landscape */
		    /* you can also specify margins here: */
		    /*margin: 35mm;*/
		    /*margin-right: 45mm;*/ /* for compatibility with both A4 and Letter */
		 /* }*/
	}
	
	@media screen
	{
		.only-print, .only-print *
		{
			display: none !important;
		}
	}

  </style>
  
  <style>
	 .btn-group-hover .btn {
	    /*border-color: white;*/
	    background: white;
	    text-shadow: 0 1px 1px white;
	    -webkit-box-shadow: inset 0 1px 0 white;
	    -moz-box-shadow: inset 0 1px 0 white;
	    box-shadow: inset 0 1px 0 white;
	}

     .btn-group-hover {
		    opacity: 0;
	}

    .rowlink {
        cursor: pointer;
    }
  </style>
</head>
<body data-spy="scroll" data-target="#sideMenuScrollspy" data-offset="0">

<?php
$menu->renderHTML();
?>

<div id="wrapper">

    <!--<div class="col-sm-2 sticky sidenav">-->
    <div id="sidebar-wrapper" class="sticky">
        <nav class="sidebar-nav" id="sideMenuScrollspy">
            <div style="margin-bottom: 80px;"></div>
            <ul class="nav nav-pills nav-stacked">
                <?php
                $i = 0;
                foreach($settingsPanels as $panel)
                {
                    ?>
                    <li role="presentation" <?php if($i==0) echo('class="active"'); ?>><a href="#<?= $panel->getID(); ?>"> <?= $panel->getTitle(); ?> </a></li>
                    <?php
                    $i++;
                }
                ?>
            </ul>
        </nav>
    </div>

    <div id="page-content-wrapper">
        <div class="page-content">
            <div class="container" id="contentor">
                <div class="row">
                    <div class="col-md-12">
                        <h2>Configurações</h2>

                        <div class="row" style="margin-bottom: 60px;"></div>


                        <?php

                        // Handle POSTs to change settings
                        if($_SERVER['REQUEST_METHOD'] === 'POST')
                        {
                            foreach($settingsPanels as $panel)
                                $panel->handlePost();
                        }

                        // Render settings panels
                        foreach($settingsPanels as $panel)
                        {
                            $panel->renderHTML();
                        ?>

                        <div class="row" style="margin-bottom: 20px;"></div>

                        <?php
                        }
                        ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php
$pageUI->renderJS(); // Render the widgets' JS code
?>
<script src="js/rowlink.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var sidebar = document.getElementById('sideMenuScrollspy');
        var panels = Array.prototype.slice.call(document.querySelectorAll('.settings_panel_widget'));

        function showPanel(hash) {
            if (!hash) {
                hash = panels.length > 0 ? '#' + panels[0].id : '';
            }
            var id = hash.replace(/^#/, '');
            var target = document.getElementById(id);
            if (!target) return;

            for (var i = 0; i < panels.length; i++) {
                var p = panels[i];
                p.style.display = (p === target) ? '' : 'none';
            }

            if (sidebar) {
                var liNodes = sidebar.querySelectorAll('li');
                for (var i = 0; i < liNodes.length; i++) {
                    var li = liNodes[i];
                    var a = li.querySelector('a');
                    if (a && a.getAttribute('href') === hash) {
                        li.classList.add('active');
                    } else {
                        li.classList.remove('active');
                    }
                }
            }
        }

if (sidebar) {
    var anchorNodes = sidebar.querySelectorAll('a');
    for (var i = 0; i < anchorNodes.length; i++) {
        (function (link) {
            link.addEventListener('click', function (ev) {
                var href = link.getAttribute('href');
                if (href && href.charAt(0) === '#') {
                    ev.preventDefault();
                    if (history.pushState) {
                        history.pushState(null, '', href);
                    } else {
                        window.location.hash = href;
                    }
                    showPanel(href);
                }
            });
        })(anchorNodes[i]);
    }
}


        window.addEventListener('hashchange', function () {
            showPanel(window.location.hash);
        });

        showPanel(window.location.hash);
    });
</script>

</body>
</html>
