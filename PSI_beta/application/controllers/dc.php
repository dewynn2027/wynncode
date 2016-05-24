<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Dc extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('nginv2_model');	
		$this->load->model('whip_model');
	}
	
	function logo()
	{
	
		$this->whip_model->logme("Access DateTime:".gmDate("Y-m-d H:i:s"),"dc");
		$this->whip_model->logme((array)$_SERVER,"dc");
		$this->load->view("dc/logo");
		$data_arr["remoteAddr"] 		= $_SERVER["REMOTE_ADDR"];
		$data_arr["requestUri"] 		= $_SERVER["REQUEST_URI"];
		$data_arr["httpReferer"] 		= $_SERVER["HTTP_REFERER"];
		$data_arr["httpUserAgent"]		= $_SERVER["HTTP_USER_AGENT"];
		$data_arr["httpAcceptLanguage"]	= $_SERVER["HTTP_ACCEPT_LANGUAGE"];
		$this->nginv2_model->insertDC($data_arr);
		
	}
}