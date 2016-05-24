<?
class Sdklibraries 
{
	/*
	* @param string $input String to be padded
	* @param int $blocksize Blocksize - in case of Triple DES/ECB this will be 8
	*
	* @return string Unpadded text
	*/
	function pkcs5_pad ($input, $blocksize)
	{
		$pad = $blocksize - (strlen($input) % $blocksize);
		return $input . str_repeat(chr($pad), $pad);
	}
	/*
	* @param string $text PKCS5 Padded text
	*
	* @return string Unpadded text
	*/
	function pkcs5_unpad($input)
	{
		$pad = ord($input{strlen($input)-1});
		if ($pad > strlen($input)) return false;
		if (strspn($input, chr($pad), strlen($input) - $pad) != $pad)
		return false;
		return substr($input, 0, -1 * $pad);
	}
	/*
	* DESede/ECB/PKCS5Padding Encryption
	*
	* @param string $input Input string for de/encryption
	* @param string $key Key for de/encryption
	* @param string $mode Either 'encrypt' or 'decrypt'
	*
	* @return string De/Encrypted result of original input.
	*/
	function dep5_crypt($input, $key, $mode = 'encrypt')
	{
		$key = pack("H48", $key);
		$td = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_ECB, '');
		$iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		if ($mode == 'encrypt') 
		{
			$size = mcrypt_enc_get_block_size($td);
			$input = self::pkcs5_pad($input, $size);
			$data = bin2hex(mcrypt_ecb(MCRYPT_3DES, $key, $input, MCRYPT_ENCRYPT, $iv));
		/*
		ENDEAVOUR INTERNET BUSINESS SOLUTIONS CONFIDENTIAL PAGE 76/86
		*/
		} elseif ($mode == 'decrypt') 
		{
			$data = pack('H*', $input);
			$data = self::pkcs5_unpad(mcrypt_ecb(MCRYPT_3DES, $key, $data, MCRYPT_DECRYPT, $iv));
		}
		mcrypt_module_close($td);
		return $data;
	}

	function testcall()
	{
		return "success";
	}
}
// Usage example [not really needed, but here goes anyway]:
/*
$val = 'INSERT SOME WEIRD TEST STRING HERE';
$key = 'INSERT ULTRA SECURE KEY HERE';
$enc = dep5_crypt($val, $key, 'encrypt');
$dec = dep5_crypt($enc, $key, 'decrypt');
*/
