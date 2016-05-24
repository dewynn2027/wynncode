<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the 'Database Connection'
| page of the User Guide.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	['hostname'] The hostname of your database server.
|	['username'] The username used to connect to the database
|	['password'] The password used to connect to the database
|	['database'] The name of the database you want to connect to
|	['dbdriver'] The database type. ie: mysql.  Currently supported:
				 mysql, mysqli, postgre, odbc, mssql, sqlite, oci8
|	['dbprefix'] You can add an optional prefix, which will be added
|				 to the table name when using the  Active Record class
|	['pconnect'] TRUE/FALSE - Whether to use a persistent connection
|	['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
|	['cache_on'] TRUE/FALSE - Enables/disables query caching
|	['cachedir'] The path to the folder where cache files should be stored
|	['char_set'] The character set used in communicating with the database
|	['dbcollat'] The character collation used in communicating with the database
|				 NOTE: For MySQL and MySQLi databases, this setting is only used
| 				 as a backup if your server is running PHP < 5.2.3 or MySQL < 5.0.7
|				 (and in table creation queries made with DB Forge).
| 				 There is an incompatibility in PHP with mysql_real_escape_string() which
| 				 can make your site vulnerable to SQL injection if you are using a
| 				 multi-byte character set and are running versions lower than these.
| 				 Sites using Latin-1 or UTF-8 database character set and collation are unaffected.
|	['swap_pre'] A default table prefix that should be swapped with the dbprefix
|	['autoinit'] Whether or not to automatically initialize the database.
|	['stricton'] TRUE/FALSE - forces 'Strict Mode' connections
|							- good for ensuring strict SQL while developing
|
| The $active_group variable lets you choose which connection group to
| make active.  By default there is only one group (the 'default' group).
|
| The $active_record variables lets you determine whether or not to load
| the active record class
*/

$active_group = 'default';
$active_record = TRUE;

$db['default']['hostname'] = '119.81.90.162';
$db['default']['username'] = 'psiapi';
$db['default']['password'] = 'p@55w0rd';
$db['default']['database'] = 'psi';
$db['default']['dbdriver'] = 'mysqli';
$db['default']['dbprefix'] = '';
$db['default']['pconnect'] = FALSE;
$db['default']['db_debug'] = TRUE;
$db['default']['cache_on'] = FALSE;
$db['default']['cachedir'] = '';
$db['default']['char_set'] = 'utf8';
$db['default']['dbcollat'] = 'utf8_general_ci';
$db['default']['swap_pre'] = '';
$db['default']['autoinit'] = TRUE;
$db['default']['stricton'] = FALSE;

$db['psidb']['hostname'] = '119.81.90.162';
$db['psidb']['username'] = 'psiapi';
$db['psidb']['password'] = 'p@55w0rd';
$db['psidb']['database'] = 'psi';
$db['psidb']['dbdriver'] = 'mysqli';
$db['psidb']['dbprefix'] = '';
$db['psidb']['pconnect'] = FALSE;
$db['psidb']['db_debug'] = TRUE;
$db['psidb']['cache_on'] = FALSE;
$db['psidb']['cachedir'] = '';
$db['psidb']['char_set'] = 'utf8';
$db['psidb']['dbcollat'] = 'utf8_general_ci';
$db['psidb']['swap_pre'] = '';
$db['psidb']['autoinit'] = TRUE;
$db['psidb']['stricton'] = FALSE;
//
//$db['sdpay']['hostname'] = '10.64.184.66';
//$db['sdpay']['username'] = 'psiapi';
//$db['sdpay']['password'] = 'p@55w0rd';
//$db['sdpay']['database'] = 'sdpay';
//$db['sdpay']['dbdriver'] = 'mysql';
//$db['sdpay']['dbprefix'] = '';
//$db['sdpay']['pconnect'] = FALSE;
//$db['sdpay']['db_debug'] = FALSE;
//$db['sdpay']['cache_on'] = FALSE;
//$db['sdpay']['cachedir'] = '';
//$db['sdpay']['char_set'] = 'utf8';
//$db['sdpay']['dbcollat'] = 'utf8_general_ci';
//$db['sdpay']['swap_pre'] = '';
//$db['sdpay']['autoinit'] = TRUE;
//$db['sdpay']['stricton'] = FALSE;
//
//$db['pac2pay']['hostname'] = '10.64.184.66';
//$db['pac2pay']['username'] = 'psiapi';
//$db['pac2pay']['password'] = 'p@55w0rd';
//$db['pac2pay']['database'] = 'pac2pay';
//$db['pac2pay']['dbdriver'] = 'mysql';
//$db['pac2pay']['dbprefix'] = '';
//$db['pac2pay']['pconnect'] = FALSE;
//$db['pac2pay']['db_debug'] = FALSE;
//$db['pac2pay']['cache_on'] = FALSE;
//$db['pac2pay']['cachedir'] = '';
//$db['pac2pay']['char_set'] = 'utf8';
//$db['pac2pay']['dbcollat'] = 'utf8_general_ci';
//$db['pac2pay']['swap_pre'] = '';
//$db['pac2pay']['autoinit'] = TRUE;
//$db['pac2pay']['stricton'] = FALSE;


/* End of file database.php */
/* Location: ./application/config/database.php */
