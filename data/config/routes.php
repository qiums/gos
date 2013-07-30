<?php  if ( ! defined('ROOT')) exit('No direct script access allowed');
/*
* create by EditPlus v3.01
* author by Max
*/
/*$routes['about-shanghai'] = 'archives/detail/mid/1/id/1';
$routes['dining'] = 'archives/detail/mid/1/id/4';
$routes['night-life'] = 'archives/detail/mid/1/id/6';
$routes['shopping'] = 'archives/detail/mid/1/id/7';*/
$routes['gosupe/(login|logout)'] = 'admin/home/$1';
$routes['user/(:num)'] = 'user/home/home/uid/$1';
$routes['user/resetpwd'] = 'user?do=reset';
$routes['user/(home|signup|login|logout|ckfield|reset)'] = 'user/home/$1';
//$routes['search/(article|events|venue|album|blog|download|family)'] = 'search/index/mid/$1';
$routes['plug/(:any)'] = '?plug=$1';
$routes['add/(venue|events)'] = 'member/app/summary/add/item/$1';
/*$routes['listings'] = 'archives/index/mid/venue';
$routes['venue/view/(:num)'] = 'venue/detail/id/$1';
$routes['(venue)/(:any)'] = 'venue/index?cid=$2';
$routes['(venue|events|album|download|family)'] = 'archives/index/mid/$1';*/
$routes['(article|venue|events|album|blog|download)/view/(:num)'] = 'archives/detail/mid/$1/id/$2';
$routes['(article|venue|events|album|blog|download)/view/(:num)/page/(:num)'] = 'archives/detail/mid/$1/id/$2/page/$3';
$routes['(events|venue|album|blog|download|family)/(:any)'] = 'archives/index/mid/$1?cid=$2';
$routes['(?!archives|gosupe|admin|member|user|components|cp|category|search|home|views|plug|forum)(:any)'] = 'archives/index?cid=$1';

#$disable_route_key = array('page','sort','way','word');
