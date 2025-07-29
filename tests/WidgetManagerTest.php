<?php
use PHPUnit\Framework\TestCase;
use catechesis\gui\WidgetManager;
use catechesis\gui\Widget;
use catechesis\Configurator;

require_once __DIR__ . '/../gui/widgets/WidgetManager.php';
require_once __DIR__ . '/../gui/widgets/Widget.php';
require_once __DIR__ . '/../core/Configurator.php';

class WidgetManagerTest extends TestCase
{
    public function testRenderJSAddsCoreScripts(): void
    {
        $widget = new class extends Widget {
            public function renderHTML() {}
        };

        $manager = new WidgetManager();
        $manager->addWidget($widget);

        ob_start();
        $manager->renderJS();
        $output = ob_get_clean();

        $this->assertStringContainsString('<script src="' . Configurator::JQUERY_LIB_PATH . '"></script>', $output);
        $this->assertStringContainsString('<script src="' . Configurator::BOOTSTRAP_BUNDLE_PATH . '"></script>', $output);
    }
}
?>
