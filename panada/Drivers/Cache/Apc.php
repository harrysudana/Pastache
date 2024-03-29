<?php
/**
 * Panada APC API Driver.
 *
 * @package	Driver
 * @subpackage	Cache
 * @author	Iskandar Soesman
 * @since	Version 0.2
 *
 * Install APC on Ubuntu: aptitude install libpcre3-dev;
 * pecl install apc
 */
namespace Drivers\Cache;
use
    Resources\Interfaces as Interfaces,
    Resources\RunException as RunException;
    
class Apc implements Interfaces\Cache {
    
    public function __construct(){
        
        /**
        * Makesure APC extension is enabled
        */
       if( ! extension_loaded('apc') )
           throw new RunException('APC extension that required by APC Driver is not available.');
    }
    
    /**
     * PHP Magic method for calling a method dinamicly
     * 
     * @param string $name
     * @param mix $arguments
     * @return mix
     */
    public function __call($name, $arguments){
        
        return call_user_func_array($name, $arguments);
    }
    
    /**
     * PHP Magic method for calling a static method dinamicly
     * 
     * @param string $name
     * @param mix $arguments
     * @return mix
     */
    public static function __callStatic($name, $arguments) {
        
        return call_user_func_array($name, $arguments);
    }
    
    /**
     * @param string $key
     * @param mix $value
     * @param int $expire
     * @return void
     */
    public function setValue( $key, $value, $expire = 0, $namespace = false ){
        
        $key = $this->keyToNamespace($key, $namespace);
        return apc_store($key, $value, $expire); 
    }
    
    /**
     * Cached the value if the key doesn't exists,
     * other wise will false.
     *
     * @param string $key
     * @param mix $value
     * @param int $expire
     * @return void
     */
    public function addValue( $key, $value, $expire = 0, $namespace = false ){
        
        $key = $this->keyToNamespace($key, $namespace);
        return apc_add($key, $value, $expire);
    }
    
    /**
     * Update cache value base on the key given.
     *
     * @param string $key
     * @param mix $value
     * @param int $expire
     * @return void
     */
    public function updateValue( $key, $value, $expire = 0, $namespace = false ){
        
        $key = $this->keyToNamespace($key, $namespace);
        return $this->setValue($key, $value, $expire);
    }
    
    /**
     * @param string $key
     * @return mix
     */
    public function getValue( $key, $namespace = false ){
        
        $key = $this->keyToNamespace($key, $namespace);
        return apc_fetch($key); 
    }
    
    /**
     * @param string $key
     * @return void
     */
    public function deleteValue( $key, $namespace = false ){
        
        $key = $this->keyToNamespace($key, $namespace);
        return apc_delete($key);
    }
    
    /**
     * Flush all cached object.
     * @return bool
     */
    public function flushValues(){
        
        return apc_clear_cache('user');
    }
    
    /**
     * Namespace usefull when we need to wildcard deleting cache object.
     *
     * @param string $namespace_key
     * @return int Unixtimestamp
     */
    private function keyToNamespace( $key, $namespace_key = false ){
	
	if( ! $namespace_key )
	    return $key;
	
	if( ! $namespace_value = apc_fetch($namespace_key) ){
	    $namespace_value = time();
	    apc_store($namespace_key, $namespace_value, 0);
	}
	
	return $namespace_value.'_'.$key;
    }
}