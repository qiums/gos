<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
$config['ads'] = array(
    'app_name' => 'Advertisement',
    'data_table' => 'ads',
	'view_table' => 'ads_view',
	'click_table' => 'ads_click',
	'page_size' => 20,
	'sort_fields' => array(
		'published' => array('published','desc'),
		'sortby' => array('sortby','asc'),
		'begindate' => array('begindate','asc'),
		'id'=>array('id','desc'),
	),
	'form_data' => array(
		'adtype'=>array('params'=>'select','search'=>'*','optext'=>'[label]'),
		'subject'=>array('params'=>'text/class/req-any minlength2/maxlength/200','search'=>'admin|u1','pattern'=>'m'),
		'linkurl'=>array('params'=>'text/class/req-url minlength2 cannull/maxlength/200'),
		'pathname'=>array('params'=>'text/class/req-any minlength2/maxlength/200'),
		'description'=>array('params'=>'textarea/class/req-any/maxlength/255','search'=>'admin|u1','pattern'=>'m'),
		'begindate'=>array('params'=>'text/class/req-date lt_ele-text-enddate minlength2 shtxt/maxlength/25'),
		'enddate'=>array('params'=>'text/class/req-date gt_ele-text-begindate minlength2 shtxt/maxlength/25'),
		'coverpic'=>array('params'=>'text/class/getimage/maxlength/200'),
		'width'=>array('params'=>'text/class/req-num cannull shtxt'),
		'height'=>array('params'=>'text/class/req-num cannull shtxt'),
		'position'=>array('params'=>'radio','search'=>'*|>select','optext'=>'[label]'),
		'pages'=>array('params'=>'checkbox','search'=>'*|>select','optext'=>'[label]'),
		'modules'=>array('params'=>'checkbox','search'=>'*|>select','source'=>'module/get','field'=>'module_name','optext'=>'[label]'),
		'category'=>array('params'=>'text'),
	),
);
?>
