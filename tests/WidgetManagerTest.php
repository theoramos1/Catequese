<?php

use PHPUnit\Framework\TestCase;
use catechesis\gui\WidgetManager;
use catechesis\gui\MinimalNavbar;

require_once __DIR__ . '/../core/config/catechesis_config.inc.php';
require_once __DIR__ . '/../gui/widgets/WidgetManager.php';
require_once __DIR__ . '/../gui/widgets/Navbar/MinimalNavbar.php';

class WidgetManagerTest extends TestCase
{
    public function testDefaultConstructorUsesBaseUrl(): void
    {
        $manager = new WidgetManager();
        $navbar = new MinimalNavbar();
        $manager->addWidget($navbar);

        ob_start();
        $manager->renderJS();
        $output = ob_get_clean();

        $expectedPrefix = constant('CATECHESIS_BASE_URL') . '/';
        $this->assertStringContainsString(
            '<script src="' . $expectedPrefix . 'js/jquery.min.js"></script>',
            $output
        );

    }

    public function testRenderJSOutputsCoreScriptsWithoutWidgets(): void
    {
        $manager = new WidgetManager();

        ob_start();
        $manager->renderJS();
        $output = ob_get_clean();

        $prefix = constant('CATECHESIS_BASE_URL') . '/';
        $expected = [
            '<script src="' . $prefix . 'js/jquery.min.js"></script>',
            '<script src="' . $prefix . 'js/bootstrap.min.js"></script>',
            '<script src="' . $prefix . 'js/index.js"></script>',
        ];

        $this->assertSame(implode('', $expected), $output);
    }
}
?>
