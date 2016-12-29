# CC Avenue Lib


copy lib/ccavenue_lib.php into the application/libraries folder

import the library using:
// DATA will be from the CCAVENUE account
$params = array(
			'merchant_id' => 'abc',
			'access_code' => 'mno',
			'working_key' => 'xyz',
			'redirect_url' => 'http://localhost/checkoutAvenue',
			'cancel_url' => 'http://localhost/cancelAvenue',
	  );
		$this->load->library('ccavenue_lib', $params);
