<?php
/**
 * Importer class.
 *
 * @author  Iskandar Soesman <k4ndar@yahoo.com>
 * @link    http://panadaframework.com/
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @since   version 1.0.0
 * @package Resources
 */
namespace Resources;

class Import {
    
    public static function vendor($filePath, $className = false, $arguments = array()){
        
        $config = Config::main();
        
        if( ! file_exists( $file = $config['vendor']['path'] . $filePath.'.php' ) )
            return false;
        
        include_once $file;
        
        if( ! $className ){
            $arr = explode('/', $filePath);
            $className = end( $arr );
        }
        
        $reflector = new \ReflectionClass($className);
        
        try{
            $object = $reflector->newInstanceArgs($arguments);
        }
        catch(\ReflectionException $e){
            $object = new $class;
        }
        
        return $object;
    }
}