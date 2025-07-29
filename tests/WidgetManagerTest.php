<?php
namespace catechesis\gui {
    require_once __DIR__ . '/../gui/widgets/Widget.php';
    class DummyWidget extends Widget
    {
        public function __construct(array $deps, ?string $id = null)
        {
            parent::__construct($id);
            foreach($deps as $d)
                $this->addJSDependency($d);
        }
        public function renderHTML() {}
    }
}

namespace {
    use catechesis\gui\WidgetManager;
    use catechesis\gui\DummyWidget;
    use PHPUnit\Framework\TestCase;

    require_once __DIR__ . '/../gui/widgets/WidgetManager.php';

    class WidgetManagerTest extends TestCase
    {
        public function testRenderJSSortsCoreScriptsFirst(): void
        {
            $manager = new WidgetManager();

            // Register widgets in an arbitrary order
            $w1 = new DummyWidget(['js/bootstrap.bundle.min.js', 'c.js']);
            $w2 = new DummyWidget(['js/jquery.min.js', 'b.js']);
            $w3 = new DummyWidget(['d.js']);

            $manager->addWidget($w1); // bootstrap first
            $manager->addWidget($w2); // jquery second
            $manager->addWidget($w3);

            ob_start();
            $manager->renderJS();
            $output = ob_get_clean();

            $expected = '<script src="js/jquery.min.js"></script>' .
                        '<script src="js/bootstrap.bundle.min.js"></script>' .
                        '<script src="c.js"></script>' .
                        '<script src="b.js"></script>' .
                        '<script src="d.js"></script>';

            $this->assertEquals($expected, $output);
        }
    }
}
?>
