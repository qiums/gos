<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
return array(
    'app_name' => 'Summary',
    'data_table' => 'summary',
	'access_level'=>'*',
	'item' => array(
		'venue'=>array(
			'needlogin'=>1,
			'mid'=>4,
		),
		'events'=>array(
			'needlogin'=>1,
			'mid'=>5,
		),
		'jobs'=>array(
			'needlogin'=>1,
		),
		'house'=>array(
			'needlogin'=>1,
		),
		'other'=>array(
			'needlogin'=>0,
			'formdata'=>array(
				'rcid'=>'run_bug','content_error','proposal',
			),
		),
	),
);
?>
