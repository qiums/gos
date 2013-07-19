<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
return array(
	'index|save' => array(
		'catename' => array('type'=>'input:text', 'attr'=>'class="req-any minlength2" maxlength="50"'),
		'mid' => array('type'=>'select', 'attr'=>'class="req-any minlength1"'),
		'sortby' => array('type' => 'input:text', 'attr' =>'class="req-num" maxlength="5"', 'value'=>'0', 'islist'=>'1'),
		'tpltype' => array('type' => 'select', 'attr' =>'class="req-any minlength2"', 'value'=>'list'),
		'coverpic' => array('type'=>'file'),
		'keywords' => array('type'=>'input:text'),
		'description' => array('type'=>'textarea', 'attr'=>' rows="3"'),
		'options' => array('type'=>'textarea', 'attr'=>' rows="5"'),
		'contents.content' => array('type'=>'htmleditor', 'attr'=>'style="height:300px"', 'group'=>'2'),
	),
);