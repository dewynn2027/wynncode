<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Nginclient extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->post = $this->input->post('parameters');
		$this->environment = ($this->input->post('server')=="LIVE") ? (string)$this->input->post('server') : (string)$this->input->post('server');	//LIVE or DEVELOPMENT
		$this->serverurl_dev = 'http://stage.rttransaccion.com/ngin/';
		$this->serverurl_live = 'http://api.rttransaccion.com/ngin/';
		$this->serverurl = ($this->environment=="LIVE") ? $this->serverurl_live : $this->serverurl_dev;
		$this->debug = ($this->input->post('mydebug') > 0) ? true : false;
		

	}
	
	public function index($option="")
	{
		
		$data = array("method" => $option);
		$this->load->view('main',$data);
		
	}
	
	public function convertArrayToxml($option="")
	{		
		
		$param  = array(
			"accountId"				=> 24,
			"keyword"				=> "",
			"accountType"			=> (string)"",
			"startDate"				=> (string)"",
			"endDate"				=> (string)"",
			"pageNum"				=> (int) 0,
			"perPage"				=>(int) 15
		);
		$xml = "";
		foreach($param as $k => $v)
		{
			$xml .= "<".$k.">".$v."</".$k.">";
		
		}
		$data = array("method" => $method,"response" => $xml, "postme" => $this->post, "postme" => $this->post, "server_url" => $this->serverurl, "server_method" => $method);
		$this->load->view('main',$data);
	}
	
	public function processCardngn($option="")
	{		
		$method = strtolower(stripslashes($this->uri->segment(3)));
		$param  = $this->post;
		$getResponse = $this->sendRequest($param, $method);
		$data = array("method" => $method,"response" => $getResponse, "postme" => $this->post, "postme" => $this->post, "server_url" => $this->serverurl, "server_method" => $method);
		print_r($this->debug);
		$this->load->view('main',$data);
		
	}

	public function sendRequest($param,$server_method)
	{
		$this->xmlrpc->set_debug($this->debug);
		$this->xmlrpc->server($this->serverurl, 80);
		$this->xmlrpc->method($server_method);
		$param  = $this->post;
		$request = array(
					array(
						$param		
					),'struct'
				);
		$this->xmlrpc->request($request);

		if ( ! $this->xmlrpc->send_request())
		{
			$response =  $this->xmlrpc->display_error();
		}else{
			$response =  $this->xmlrpc->display_response();
		}
		return $response;
	}
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */