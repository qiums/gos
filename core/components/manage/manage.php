<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/*
* create by EditPlus v3.01
* author by Sam <wuyou192@163.com>.
*/
$app_conf = import('app.manage')->conf;
if ('index'==$ACTION){
	$head_title = $APPDATA['manage']['app_name'];
	$template_file = 'app_index';
}
