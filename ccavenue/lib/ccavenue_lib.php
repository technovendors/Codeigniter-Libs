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
 
class CCAvenue_Lib{
	var $CI;
	
	public function __construct($params = array()){
		$this->CI =& get_instance();
		
		$this->CI->load->helper('url');
		$this->CI->config->item('base_url');
		$this->CI->load->database();
	}
	
	public function encrypt($plainText, $key){
		$secretKey = $this->hextobin(md5($key));
		$initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
		$openMode = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', 'cbc', '');
		$blockSize = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, 'cbc');
		$plainPad = $this->pkcs5_pad($plainText, $blockSize);
		
		if(mcrypt_generic_init($openMode, $secretKey, $initVector) != -1){
			$encryptedText = mcrypt_generic($openMode, $plainPad);
			mcrypt_generic_deinit($openMode);
		} 
		
		return bin2hex($encryptedText);
	}
	
	public function decrypt($encryptedText, $key){
		$secretKey = $this->hextobin(md5($key));
		$initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
		$encryptedText = $this->hextobin($encryptedText);
		$openMode = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', 'cbc', '');
		mcrypt_generic_init($openMode, $secretKey, $initVector);
		$decryptedText = mdecrypt_generic($openMode, $encryptedText);
		$decryptedText = rtrim($decryptedText, "\0");
		mcrypt_generic_deinit($openMode);
		return $decryptedText;
	}
	
	//*********** Padding Function *********************//
	private function pkcs5_pad($plainText, $blockSize){
		$pad = $blockSize - (strlen($plainText) % $blockSize);
		return $plainText . str_repeat(chr($pad), $pad);
	}
	
	////********** Hexadecimal to Binary function for php 4.0 version ********//
	private function hextobin($hexString){
		$length = strlen($hexString);
		$binString = "";
		$count = 0;
		
		while($count < $length){
			$subString = substr($hexString, $count, 2);
			$packedString = pack("H*",$subString);
			
			if($count==0){
				$binString = $packedString;
			}else{
				$binString .= $packedString;
			}
			$count += 2;
		}
		return $binString;
	}
}
