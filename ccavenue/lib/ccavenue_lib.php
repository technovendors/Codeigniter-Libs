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
	private $merchant_id = null; //Shared by CCAVENUES
	private $access_code = null; //Shared by CCAVENUES
	private $working_key = null; //Shared by CCAVENUES
	private $redirect_url = null;
	private $cancel_url = null; 
	
	public function __construct($params = array()){
		$this->CI =& get_instance();
		
		$this->CI->load->helper('url');
		$this->CI->config->item('base_url');
		$this->CI->load->database();
		
		$this->merchant_id = $params['merchant_id'];
		$this->access_code = $params['access_code'];
		$this->working_key = $params['working_key'];
		$this->redirect_url = $params['redirect_url'];
		$this->cancel_url = $params['cancel_url'];
		return true;
	}
	
	private function encrypt($plainText, $key){
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
	
	private function decrypt($encryptedText, $key){
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
	
	public function ccavenueRequestHandler($tempData){
		$merchant_data = '';
		
		$data = array(
			'tid' => time(),
			'merchant_id' => $this->merchant_id,
			'order_id' => ((isset($tempData['order_id']) && !empty($tempData['order_id'])) ? $tempData['order_id'] : 000001),
			'amount' => ((isset($tempData['amount']) && !empty($tempData['amount'])) ? $tempData['amount'] : '1.00'),
			'currency' => ((isset($tempData['currency']) && !empty($tempData['currency'])) ? $tempData['currency'] : 'INR'),
			'redirect_url' => $this->redirect_url,
			'cancel_url' => $this->cancel_url,
			'language' => (isset($data['language']) && !empty($data['language'])) ? $data['language'] : 'EN',
			'submit' => 'CheckOut'
		);
		
		foreach($data as $key=>$value){
			$merchant_data .= $key . '=' . urlencode($value) . '&';
		}
		
		// encrypt the data
		$encrypted_data = $this->encrypt($merchant_data, $this->working_key);
		
		return "
			<html>
				<head>
					<title>CC Avenue Handler</title>
				</head>
				<body>
					<center>
						<form method='post' name='redirect' action='https://secure.ccavenue.com/transaction/transaction.do?command=initiateTransaction'>
							<input type='hidden' name='encRequest' value='{$encrypted_data}' />
							<input type='hidden' name='access_code' value='{$this->access_code}' />
						</form>
					</center>
					<script language='javascript'>document.redirect.submit();</script>
				</body>
			</html>
		";
	}
	
	public function ccavenueResponseHandler(){
		$encResponse = $_POST["encResp"]; // Response sent by the CCAvenue Server
		$rcvdString = $this->decrypt($encResponse, $this->working_key); // decrypt the data
		
		$order_status = "";
		$decryptValues = explode('&', $rcvdString);
		$dataSize = sizeof($decryptValues);
		
		$printData = '';
		for($i=0; $i<$dataSize; $i++){
			$information = explode('=', $decryptValues[$i]);
			
			if($i == 3){
				$order_status = $information[1];
			}
			
			$printData .= "<tr>
				<td>{$information[0]}</td>
				<td>".urldecode($information[1])."</td>
			</tr>";
		}
		
		if($order_status === "Success"){
			$message = "Thank you for shopping with us. Your credit card has been charged and your transaction is successful. We will be shipping your order to you soon.";
		}else if($order_status === "Aborted"){
			$message = "Thank you for shopping with us.We will keep you posted regarding the status of your order through e-mail";
		}else if($order_status === "Failure"){
			$message = "Thank you for shopping with us.However,the transaction has been declined.";
		}else{
			$message = "Security Error. Illegal access detected";
		}
		
		return "
			<center>
				<br />{$message}<br /><br />
				<table cellspacing='4' cellpadding='4'>
					{$printData}
				</table>
				<br />
			</center>
		";
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
