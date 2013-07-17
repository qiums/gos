<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
return array(
	'venue' => array(
		'arcindex.cid' => array('type'=>'input:hidden', 'search'=>'1'),
		'areatag' => array('type'=>'input:hidden', 'search'=>'m', 'pattern'=>'r:tag'),
		'price' => array('type'=>'select', 'option'=>'10,15,20,25,30,35,40,45,50', 'search'=>'1'),
		'rate' => array('type'=>'select', 'option'=>'10,15,20,25,30,35,40,45,50', 'search'=>'1'),
	),
);