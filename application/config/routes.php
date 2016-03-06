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
|	http://codeigniter.com/user_guide/general/routing.html
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
$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['user/tags']['POST'] = 'user/addTag';
$route['user/tags/(\d+)']['DELETE'] = 'user/removeTag/$1';
$route['user/requestResetPassword']['POST'] = 'user/requestResetPassword';
$route['user/resetPassword']['POST'] = 'user/resetPassword';

$route['orders']['POST'] = 'orders/add';
$route['orders/(\d+)']['POST'] = 'orders/update/$1';
$route['orders/(\d+)']['GET'] = 'orders/view/$1';
$route['orders/(\d+)/reward']['POST'] = 'orders/reward/$1';
$route['orders/(\d+)/review'] = 'reviews/viewByOrder/$1';
$route['orders/(\d+)']['DELETE'] = 'orders/order/$1';
$route['user/orders']['GET'] = 'orders/myOrders';

$route['reviewers/(\w+)/valid']['GET'] = 'reviewers/valid/$1';
$route['reviewers/(\w+)']['GET'] = 'reviewers/view/$1';

$route['reviewers/(\w+)/reviews']['GET'] = 'reviews/userReviews/$1';
$route['reviews']['GET'] = 'reviews/allReviews';
$route['reviews/(\d+)/tags']['POST'] = 'reviews/addTag/$1';
$route['reviews/(\d+)/tags/(\d+)']['DELETE'] = 'reviews/removeTag/$1/$2';
$route['reviews/(\d+)']['PATCH'] = 'reviews/update/$1';
$route['reviews']['POST'] = 'reviews/add';
$route['reviews/(\d+)']['GET'] = 'reviews/view/$1';
$route['reviews/(\d+)/visits']['POST'] = 'visits/visitReview/$1';

$route['user']['PATCH'] = 'user/update';

$route['videos']['POST'] = 'videos/createVideo';
$route['videos']['GET'] = 'videos/getVideoList';
$route['videos/(\d+)']['GET'] = 'videos/one/$1';
$route['videos/(\d+)/visits']['POST'] = 'visits/visitVideo/$1';

$route['applications']['POST'] = 'applications/create';
$route['applications/(\d+)/agree']['GET'] = 'applications/agree/$1';

$route['admin/reviews/(\d+)']['PATCH'] = 'reviews/adminUpdate/$1';

$route['reviews/(\d+)/comments']['POST'] = 'comments/create/$1';
$route['reviews/(\d+)/comments']['GET'] = 'comments/list/$1';

$route['notifications']['GET'] = 'notifications/list';
$route['notifications']['PATCH'] = 'notifications/markAsRead';
$route['notifications/(\d+)']['PATCH'] = 'notifications/markAsRead/$1';
$route['notifications/count']['GET'] = 'notifications/count';

$route['events']['POST'] = 'events/create';
$route['events/(\d+)']['GET'] = 'events/one/$1';
$route['events/(\d+)/pay']['POST'] = 'events/pay/$1';

$route['attendances']['POST'] = 'attendances/create';
$route['attendances/(\d+)']['GET'] = 'attendances/one/$1';
$route['attendances']['GET'] = 'attendances/list';
