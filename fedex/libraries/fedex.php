<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package     CodeIgniter
 * @author      Nitin Goyal
 * @copyright   Copyright (c) 2016, Crazywapbox Inc.
 * @license     
 * @link        http://www.crazywapbox.com
 * @since       Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Crazywapbox core CodeIgniter class
 *
 * @package     CodeIgniter
 * @subpackage          Libraries
 * @category            Crazywapbox Inc. 
 * @author      Nitin Goyal
 * @link        http://www.crazywapbox.com
 */
 
if (!class_exists('CI_Driver')) get_instance()->load->library('driver');

define('FEDEX_CONFIG_PATH', realpath(dirname(__FILE__).'/../config'));
define('FEDEX_DRIVER_PATH', realpath(dirname(__FILE__).'/fedex'));
define('FEDEX_VENDOR_PATH', realpath(dirname(__FILE__).'/../vendor'));
 

class Fedex{
	private $_driver;
	
	public function __construct($driver = NULL){
		if(!empty($driver)){
			$this->load($driver)
		}
	}
	
	public function __call($function, $arguments){
		if(!empty($this->_driver)){
			return call_user_func_array(array($this->_driver, $function), $arguments);
		}
	}
	
	public function load($driver){
		$this->_driver = $this->_create_instance($driver);
		return $this->_driver !== FALSE;
	}
	
	public function active_driver(){
		$class_name = get_class($this->_driver);
		if($class_name === FALSE) return FALSE;
		return str_replace('Fedex_', '', $class_name);
	}
	
	private function _create_instance($driver){
		if(stripos($driver, 'fedex_') === 0){
			$driver_class = ucfirst(strtolower($driver));
		}else{
			$driver_class = 'Fedex_'.strtolower($driver);
		}
		
		if(!class_exists($driver_class)){
			$driver_path = FEDEX_DRIVER_PATH . '/' . strtolowe($driver) . '.php';
			
			if(!file_exists($driver_path)) return false;
			
			require_once($driver_path);
			
			if(!class_exists($driver_class)) return false;
		}
		
		$reflection_class = new ReflectionClass($driver_class);
		if($reflection_class->isAbstract()) return false;
		
		return new $driver_class();
	}
	
	public function valid_drivers(){
		static $valid_drivers = array();
		
		if(empty($valid_drivers)){
			foreach(scandir(FEDEX_DRIVER_PATH) as $file_name){
				$driver_path = FEDEX_DRIVER_PATH . '/' . $file_name;
				if(stripos($file_name, 'fedex_')===0 && is_file($driver_path)){
					require_once($driver_path);
					
					$driver_class = ucfirst(str_replace('.php', '', $file_name));
					if(!class_exists($driver_class)) continue;
					
					$reflection_class = new ReflectionClass($driver_class);
					if($reflection_class->isAbstract()) continue;
					
					$valid_drivers[] = str_replace('Fedex_', '', $driver_class);
				}
			}
		}
		
		return $valid_drivers;
	}
}

abstract class Fedex_driver {
	protected $CI;
	
	protected $END_POINT;
	protected $API_KEY;
	protected $API_PASSWORD;
	protected $API_ACCOUNT;
	protected $API_METER;
	
	public function __construct(){
		$this->CI =& get_instance();
	}
}

class Fedex_exception extends Exception {
	
}

class Fedex_response{
	
}
