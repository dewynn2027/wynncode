<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Ipay88 extends CI_Controller {
	public function __construct()
	{
		parent::__construct();
	}
	
	function getRestpose()
	{
		print_r($_REQUEST);
	}

}