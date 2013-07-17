<?php if ( ! defined('ROOT')) exit('No direct script access allowed');

return array(
	'login' => array(
		'username'	=>	array('type'=>'input:text', 'attr'=>'class="req-any minlength4 input-block-level" maxlength="16"'),
		'passwd'	=>	array('type'=>'input:password', 'attr'=>'class="req-any minlength4 input-block-level" maxlength="20"'),
		'captcha'	=>	array('type'=>'input:text', 'attr'=>'class="req-any minlength4" maxlength="4" size="4"'),
	),
	'register' => array(
		'username'	=>	array('type'=>'input:text', 'attr'=>'class="req-any minlength4" maxlength="16"'),
		'passwd'	=>	array('type'=>'input:password', 'attr'=>'class="req-any minlength4 rule-pwd" maxlength="20"'),
		'repasswd'	=>	array('type'=>'input:password', 'attr'=>'class="req-any minlength4 rule-pwd" maxlength="20"'),
		'email'		=>	array('type'=>'input:text', 'attr'=>'class="req-email minlength4" maxlength="50"'),
		'captcha'	=>	array('type'=>'input:text', 'attr'=>'class="req-any minlength4" maxlength="4" size="4"'),
	),
);