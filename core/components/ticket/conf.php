<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
return array(
    'app_name' => 'Tickets',
    'data_table' => 'tickets',
	'access_level'=>'*',
	'form_data' => array(
		'username'=>array('params'=>'text/class/req-any minlength2/maxlength/200'),
		'quantity'=>array('params'=>'text/class/req-num','default'=>1),
		'phone'=>array('params'=>'textarea/class/req-cnphone minlength8/maxlength/25'),
		'address'=>array('params'=>'text/class/req-any minlength2/maxlength/200'),
		'note'=>array('params'=>'text/class/req-any/maxlength/255'),
	),
);
?>
