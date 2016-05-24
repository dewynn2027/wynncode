<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

// CI Default Config
$route['default_controller'] 	= "welcome";
$route['404_override'] = '';

//Gateway (XML-RPC API)
$route['gateway/xmlrpc/1.0'] 	= "nginv2";

//DC (Kount Data Collector)
$route['dc/logo.gif'] 			= "dc/logo";
$route['dc/logo.htm'] 			= "dc/logo";

//3-D Secure ACS Proxy | Development
$route['acs/(:any)/(:any)'] 		= "tdsecacsproxy/$1/$2";
#$route['acs/(:any)/002'] 			= "tdsecacsproxy/$1/002";

//3-D Secure ACS Proxy | Stage
#$route['acs/stage/001'] 		= "tdsecacsproxy/stage/001";
#$route['acs/stage/002'] 		= "tdsecacsproxy/stage/002";

//3-D Secure ACS Proxy | Stage :: Auth Emulator
$route['acs/vform'] 			= "tdsecacsproxy/vform";

//3-D Secure ACS Proxy | Stage :: PayU Auth Emulator (Redundant..)
$route['acs/tdresponse'] 		= "tdsecacsproxy/getresponse"; 

/* End of file routes.php */
/* Location: ./application/config/routes.php */