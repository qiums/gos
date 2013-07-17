<?php if ( ! defined('ROOT')) exit('No direct script access allowed');

$config['comment']['app_name'] = 'Comment';
$config['comment']['data_table'] = 'comments';
$config['comment']['order_fields'] = array('id'=>'desc');
$config['comment']['page_size'] = 10;
$config['comment']['reply_depth'] = 5;
$config['comment']['default_published'] = 1;
$config['comment']['max_length'] = 1000;
?>
