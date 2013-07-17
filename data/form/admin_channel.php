<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
return array(
	'index|save' => array(
		'prefix' => array('type'=>'input:text', 'attr'=>'class="req-any minlength2" maxlength="16"'),
		'channel_name' => array('type'=>'input:text', 'attr'=>'class="req-any minlength2" maxlength="40"'),
		'data_table' => array('type'=>'input:text', 'attr'=>'class="req-any minlength2" maxlength="40"', 'value'=>'archives'),
		//'addon_table' => array('type'=>'input:text', 'attr'=>'class="req-any" maxlength="20"'),
		'category_table' => array('type'=>'input:text', 'attr' => 'class="req-any minlength2" maxlength="40"'),
		'data_pk' => array('type' => 'input:text', 'attr' =>'class="req-any minlength2" maxlength="20"', 'value'=>'id'),
		'other_setting' => array('type'=>'textarea', 'attr'=>' rows="5"'),
	),
	'field' => array(
		'label' => array('type'=>'input:text', 'attr'=>'class="req-any minlength2" maxlength="50"'),
		'field_name' => array('type'=>'input:text', 'attr'=>'class="req-any minlength2" maxlength="50"'),
		'type' => array('type'=>'select', 'option'=>'l.channel_field_type'),
		'attr' => array('type'=>'textarea', 'attr'=>'maxlength="200" rows="2"'),
		'value' => array('type' => 'input:text', 'attr'=>'maxlength="100"'),
		'option' => array('type'=>'textarea', 'attr'=>'rows=3'),
		'function' => array('type'=>'input:text', 'attr'=>'maxlength="50"'),
	),
);