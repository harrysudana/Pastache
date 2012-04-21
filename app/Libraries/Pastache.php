<?php
namespace Libraries;
use Resources, Mustache;

class Pastache {
    private $panadaConfig;
    private $mustache;
    
    public function __construct() {
        $this->panadaConfig = Resources\Config::main();
        include_once $this->panadaConfig['vendor']['path'] . '/Mustache/Mustache.php';
        try {
            $this->mustache = new Mustache;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Renders a template using Mustache.php.
     *
     * @see View::render()
     * @param string $template The template name
     * @param array $data The template data
     * @return string
     */
    public function render($template, $data = array()) {
        if(file_exists( $this->panadaConfig['theme']['path'] . '/' . ltrim($template, '/') ))
            $contents = file_get_contents( $this->panadaConfig['theme']['path'] . '/' . ltrim($template, '/') );
        else
            $contents = $template;
        
        return $this->mustache->render($contents, $data);
    }

}

?>
