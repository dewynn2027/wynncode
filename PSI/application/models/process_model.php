<?
class Process_model extends CI_Model {


	function __construct()
	{
		parent::__construct();
		$this->db = $this->load->database('psidb', TRUE);
	}
	
	
	function sendRequest($serverurl,$param,$method)
	{
		$this->xmlrpc->server((string)$serverurl, 443,"https");
		$this->xmlrpc->timeout(120);
		// $this->xmlrpc->server((string)$serverurl);
		$this->xmlrpc->method($method);
		$request = array(
			array(
				$param		
			),'struct'
		);
		$this->xmlrpc->request($request);

		if ( ! $this->xmlrpc->send_request())
		{
			$rsp =  $this->xmlrpc->display_error();
		}
		else
		{
			//~ echo '<pre>';
			$rsp = $this->xmlrpc->display_response();
			//~ echo '</pre>';
		}
 		return $rsp;
	}
	
}
?>
