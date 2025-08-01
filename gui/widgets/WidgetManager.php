<?php


namespace catechesis\gui
{
    require_once(__DIR__ . '/Widget.php');
    require_once(__DIR__ . '/../../core/Configurator.php');

    use catechesis\Configurator;


    /**
     * Class WidgetManager
     * @package catechesis
     *
     * Description:
     *  Allows to register the UI widgets used in a web page, and automatically handle the rendering of
     *  CSS and JS dependencies of those widgets.
     */
    class WidgetManager
    {
        /**
         * Core JS scripts that should always be included.
         */
        private const ESSENTIAL_JS = [
            'js/jquery.min.js',
            'js/bootstrap.min.js',
            'js/index.js'
        ];

        // (Note: typed properties are only allowed in PHP 7.4+, so types are commented in the meanwhile...)
        private /*array*/  $_widgets = array();                        // List of widgets added to this manager
        private /*array*/  $_additional_css_dependencies = array();    // Additional CSS dependencies besides the ones declared by the widgets
        private /*array*/  $_additional_js_dependencies = array();     // Additional JS dependencies besides the ones declared by the widgets
        private /*string*/ $_path_prefix = "";               // Prefix to add to the path of every widget import of CSS or JS files


        /**
         * Creates a new widget manager.
         * Optional argument $pathPrefix allows the usage of the widgets framework in pages located in a directory other
         * than the CatecheSis root.
         * @param string $prefix
         */
        public function __construct(string $pathPrefix = "")
        {
            if ($pathPrefix === "") {
                $pathPrefix = defined('CATECHESIS_BASE_URL')
                    ? constant('CATECHESIS_BASE_URL') . '/'
                    : '';
            }

            $this->setPathPrefix($pathPrefix);
        }


        /**
         * Adds a prefix to the path of every CSS or JS file imported by the widgets that are registered in this manager.
         * Allows the usage of the widgets framework in pages located in a directory other than the CatecheSis root.
         * NOTE this is not applied to the paths manually added directly to the manager by addCSSDependency() or addJSDependency().
         * @param string $prefix
         * @return $this
         */
        public function setPathPrefix(string $prefix)
        {
            $this->_path_prefix = $prefix;

            //Update existing widgets
            foreach($this->_widgets as $widget)
                $widget->setPathPrefix($prefix);

            return $this;
        }

        /**
         * Adds a widget to this widget manager.
         * @param Widget $widget
         * @return $this
         */
        public function addWidget(Widget &$widget)
        {
            if(!in_array($widget, $this->_widgets))
            {
                $this->_widgets[] = $widget;
                $widget->setPathPrefix($this->_path_prefix); //Widget inherits path prefix from the manager
            }

            return $this;
        }



        /**
         * Adds the path of a CSS script to the list of extra dependencies.
         * This CSS path will be included in the HTML page where this widget is used.
         * @param string $path
         * @return $this
         */
        public function addCSSDependency(string $path)
        {
            if(!in_array($this->_path_prefix . $path, $this->_additional_css_dependencies))
                $this->_additional_css_dependencies[] = $this->_path_prefix .  $path;

            return $this;
        }



        /**
         * Adds the path of a JS script to the list of extra dependencies.
         * This JS path will be included in the HTML page where this manager is used.
         * @param string $path
         * @return $this
         */
        public function addJSDependency(string $path)
        {
            if(!in_array($this->_path_prefix . $path, $this->_additional_js_dependencies))
                $this->_additional_js_dependencies[] = $this->_path_prefix .  $path;

            return $this;
        }


        /**
         * Renders all the CSS 'link' lines and inline code declared as dependencies by all the registered widgets,
         * and also any additional dependencies directly declared through this manager.
         */
        public function renderCSS()
        {
            $rendered_css = array(); //Auxiliary array to check and avoid including duplicate dependencies

            // Include additional dependencies directly declared in this manager
            foreach($this->_additional_css_dependencies as $path)
            {
                if(!in_array($path, $rendered_css))
                {
                    echo("<link rel=\"stylesheet\" href=\"$path\">");
                    $rendered_css[] = $path;
                }
            }

            // Include CSS dependencies of all the registered widgets
            foreach($this->_widgets as $widget)
            {
                // Include dependencies declared by this widget
                foreach($widget->getCSSDependencies() as $path)
                {
                    if (!in_array($this->_path_prefix .$path, $rendered_css))
                    {
                        echo("<link rel=\"stylesheet\" href=\"". $this->_path_prefix . $path . "\">");
                        $rendered_css[] = $this->_path_prefix . $path;
                    }
                }
            }

            // Render CSS inline code produced by all the registered widgets
            foreach($this->_widgets as $widget)
            {
                $widget->renderCSS();
            }
        }



        /**
         * Renders all the JS 'script' include lines and inline code declared as dependencies by all the registered widgets,
         * and also any additional dependencies directly declared through this manager.
         */
public function renderJS()
{
    $rendered_js = array();

    // Adiciona scripts essenciais (sempre!)
    foreach (self::ESSENTIAL_JS as $path) {
        $fullPath = $this->_path_prefix . $path;
        if (!in_array($fullPath, $rendered_js)) {
            $rendered_js[] = $fullPath;
        }
    }

    // Adiciona dependências JS adicionais declaradas manualmente
    foreach ($this->_additional_js_dependencies as $path) {
        if (!in_array($path, $rendered_js)) {
            $rendered_js[] = $path;
        }
    }

    // Adiciona dependências JS dos widgets registrados
    foreach ($this->_widgets as $widget) {
        foreach ($widget->getJSDependencies() as $path) {
            $fullPath = $this->_path_prefix . $path;
            if (!in_array($fullPath, $rendered_js)) {
                $rendered_js[] = $fullPath;
            }
        }
    }

    // Imprime todos os scripts na ordem correta
    foreach ($rendered_js as $path) {
        echo("<script src=\"$path\"></script>");
    }

    // Renderiza JS inline dos widgets (se houver)
    foreach ($this->_widgets as $widget)
    {
        $widget->renderJS();
    }
}


}

}