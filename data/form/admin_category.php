<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
return array(
	'index|save' => array(
		'catename' => array('type'=>'input:text', 'attr'=>'class="req-any minlength2" maxlength="50"', 'group'=>'0'),
		'mid' => array('type'=>'select', 'attr'=>'class="req-any minlength1"', 'option'=>'m.channel/sget', 'group'=>'0'),
		'tpltype' => array('type' => 'select', 'attr' =>'class="req-any minlength2"', 'value'=>'list', 'group'=>'0'),
		'coverpic' => array('type'=>'input:file', 'group'=>'0'),
		'sortby' => array('type' => 'input:text', 'attr' =>'class="req-num" maxlength="5"', 'value'=>'0', 'islist'=>'1', 'group'=>'0'),
		'keywords' => array('type'=>'input:text', 'group'=>'1'),
		'description' => array('type'=>'textarea', 'attr'=>' rows="2"', 'group'=>'1'),
		'options' => array('type'=>'textarea', 'attr'=>' rows="3"', 'group'=>'1'),
		'contents.content' => array('type'=>'html', 'attr'=>'style="height:200px"', 'group'=>'2'),
	),
);