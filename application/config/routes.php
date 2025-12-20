<?php
defined('BASEPATH') OR exit('No direct script access allowed');

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
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'dashboard';

$route['shipments']                  = 'shipments/index';
$route['shipments/create']           = 'shipments/create';
$route['shipments/edit/(:num)']      = 'shipments/edit/$1';
$route['shipments/save']             = 'shipments/save';
$route['shipments/save/(:num)']      = 'shipments/save/$1';
$route['shipments/detail/(:num)']    = 'shipments/detail/$1';
//$route['shipments/import']           = 'shipments/import';
$route['shipments/export']           = 'shipments/export';
$route['shipments/import'] = 'imports/index';

$route['shipments/check_unique'] = 'shipments/check_unique';



$route['shipments/detail/(:num)'] = 'shipments/detail/$1';
$route['files/upload/(:num)']     = 'files/upload/$1';     // $1 = shipment_id
$route['files/download/(:num)']   = 'files/download/$1';   // $1 = file_id
$route['files/delete/(:num)']     = 'files/delete/$1';     // $1 = file_id

// application/config/routes.php
$route['scores']            = 'scores/index';
$route['scores/(:num)']     = 'scores/index/$1';   // /scores/2025
$route['alerts/run']        = 'alerts/run';        // jalankan manual/cron

$route['shipments/import/commit']   = 'imports/commit';    // step 2: commit ke DB

$route['audit'] = 'audit/index';

$route['periods']             = 'periods/index';
$route['periods/create']      = 'periods/create';
$route['periods/set_active/(:num)'] = 'periods/set_active/$1';
$route['periods/lock/(:num)'] = 'periods/lock/$1';
$route['periods/unlock/(:num)']= 'periods/unlock/$1';

$route['login']  = 'auth/login';
$route['logout'] = 'auth/logout';
// application/config/routes.php
$route['forgot']      = 'auth/forgot';
$route['reset']       = 'auth/reset';        // pakai query: /reset?token=xxxxx
$route['account/password'] = 'account/password';

// application/config/routes.php
$route['rekanan']            = 'rekanan/index';
$route['rekanan/create']     = 'rekanan/create';
$route['rekanan/edit/(:num)']= 'rekanan/edit/$1';
$route['rekanan/save']       = 'rekanan/save';
$route['rekanan/save/(:num)']= 'rekanan/save/$1';
$route['rekanan/delete/(:num)']= 'rekanan/delete/$1';
// application/config/routes.php
$route['dashboard'] = 'dashboard/index';
$route['dashboard/period_data'] = 'dashboard/period_data'; // API JSON untuk chart''

// application/config/routes.php
$route['users']                 = 'users/index';
$route['users/create']          = 'users/create';
$route['users/edit/(:num)']     = 'users/edit/$1';
$route['users/save']            = 'users/save';
$route['users/delete/(:num)']   = 'users/delete/$1';
$route['users/reset/(:num)']    = 'users/reset/$1';



$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
