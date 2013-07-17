<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
return array(
	'login' => array(
		'username'	=>	array('type'=>'input:text', 'attr'=>'class="req-any minlength4" maxlength="16"', 'value'=>$us['name']),
		'passwd'	=>	array('type'=>'input:password', 'attr'=>'class="req-any minlength4" maxlength="20"', 'function'=>'md5'),
		'language'	=>	array('type'=>'select', 'option'=> gc('site.lang_package'), 'value'=>$this->vars['language']),
		//'captcha'	=>	array('type'=>'input:text', 'attr'=>'class="req-any minlength4" maxlength="4"'),
		'subsite'	=>	array('type'=>'select', 'option'=> '1=上海'),
	)
);