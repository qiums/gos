<?php if ( ! defined('ROOT')) exit('No direct script access allowed');

$config['tree']['app_name'] = 'Tree Data';
$config['tree']['treedata_table'] = 'treedata';
$config['tree']['data_table'] = 'treeinfo';
$config['tree']['order_fields'] = array('depth'=>'asc','orderby'=>'asc','dataname'=>'asc');
$config['tree']['form_data'] = array(
	'typename' => array('type'=>'input:text', 'attr'=>'class="req-any minlength2" maxlength="40"'),
	'alias' => array('type'=>'input:text', 'attr'=>'class="req-enword minlength2" maxlength="20"'),
	'treetable' => array('type'=>'input:text', 'attr'=>'class="req-string minlength2" maxlength="20"', 'value'=>'treedata'),
);
$config['tree']['add_form'] = array(
	'adddataname' => array('type'=>'textarea', 'attr'=>'rows="3"'),
);
$config['tree']['edit_form'] = array(
	'dataname' => array('type'=>'input:text', 'attr'=>'maxlength="40"'),
	'options' => array('type'=>'textarea', 'attr'=>'rows="3"'),
);
