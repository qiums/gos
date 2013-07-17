<?php  if ( ! defined('ROOT')) exit('No direct script access allowed');

$hook['load_controller'][] = array(
	'filepath' => 'hooks',
	'filename' => 'pre.php',
	'class' => 'prectrl',
	'function' => 'init',
);
$hook['load_controller'][] = array(
	'filepath' => 'hooks',
	'filename' => 'acl.php',
	'class' => 'acl',
	'function' => 'init',
);
$hook['pre_display'] = array(
	'filepath' => 'hooks',
	'filename' => 'display.php',
	'class' => 'pre_display',
	'function' => 'init',
);