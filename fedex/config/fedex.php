<?php
	if(!defined('BASEPATH')) exit ('No direct script access allowed');
	
	$config['Sandbox'] = TRUE;
	$config['EndPoint'] = $config['Sandbox'] ? 'https://wsbeta.fedex.com:443/web-services' : 'Product End Point Here';
	$config['APIKey'] = $config['Sandbox'] ? 'dOtB32I5YShJ6YZX' : 'Production Key Goes Here';
	$config['APIPassword'] = $config['Sandbox'] ? 'Sb6LKkZbg2sL0zH9bsSFcrBmZ' : 'Product Password Goes Here';
	$config['APIAccountNumber'] = $config['Sandbox'] ? '510087763' : 'Production Account Number Goes Here';
	$config['APIMeterNumber'] = $config['Sandbox'] ? '118583810' : 'Product Meter Number Goes Here';
	
?>
