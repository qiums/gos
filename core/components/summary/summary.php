<?php  if ( ! defined('ROOT')) exit('No direct script access allowed');
/*
* create by EditPlus v3.01
* author by Sam <wuyou192@163.com>.
*/
$app_model = import('app.summary');
$app_conf = $app_model->conf;
$module_model = import('module');
$item = req('item');
unset($_GET['item']);
if ($item){
	if (!isset($app_conf['item'][$item])) cprint_exit('no_define');
	$item_conf = $app_conf['item'][$item];
	if ($item_conf['needlogin'] AND !$user_data['id']) cprint_exit('no_login');
	if (!$item_conf['form_data'] AND $item_conf['mid']){
		$module_conf = $module_model->get($item_conf['mid']);
		$item_conf['form_data'] = $module_model->get_fields($item_conf['mid']);
	}
	if (!$item_conf['form_data']) cprint_exit('data_abort');
	$lib_form = import('libs.form');
}
if ('add'==$ACTION OR ('member'==$DIR AND 'index'==$ACTION)){
	$menu = array();
	foreach ($app_conf['item'] as $key=>$one){
		if ($one['mid']>0){
			$module_conf = $module_model->get($one['mid']);
			$menu["add/{$key}"] = "{$lang[button][add]} {$module_conf['module_name']}";
		}elseif (isset($lang['report_item'][$key])){
			$menu["add/{$key}"] = $lang['report_item'][$key];
		}
	}
}
if ('index'==$ACTION){
	if(!$DIR) cprint_exit('no_premission');
	if('member'==$DIR){
		$cond['uid'] = $user_data['id'];
	}elseif ('admin'==$DIR){
		$cond['sid'] = $city_data['id'];
	}
	$loopdata = $app_model->page(max(1, req('page')),20)->findAll($cond);
	$template_file = 'summary_'.$DIR;
}elseif ('add'==$ACTION){
	!defined('STATICPATH') AND define('STATICPATH', REAL_WEBROOT."views/member/");
	$form = $lib_form->run($item_conf['form_data'], '', 2);
	$template_file = 'summary_index';
}elseif ('save'==$ACTION){
	if (isset($_POST['m'])){
		$m = $_POST['m'];
		unset($_POST['m']);
	}
	$post = $lib_form->verification($_POST, $item_conf['form_data']);
	if (isset($post['error'])) cprint_exit($post);
	$m['uid'] = (int)$user_data['id'];
	$m['createtime'] = D::get('curtime');
	$m['sid'] = (int)$city_data['id'];
	$m['itemname'] = $item;
	if ($post['region'] AND isset($city_result)){
		list($firstct) = explode(',', $post['region']);
		if (isset($city_result[$firstct]) AND $city_result[$firstct]['siteflag']){
			$m['sid'] = $firstct;
		}
		unset($firstct);
	}
	$m['subject'] = $post['subject'];
	$m['content'] = $app_model->build_content($post);
	$app_model->insert($m);
	cprint_exit('insert_report_ok', 'ok');
}
