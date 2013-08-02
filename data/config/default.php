<?php  if ( ! defined('ROOT')) exit('No direct script access allowed');
/*
* create by EditPlus v3.01
* author by Max
*/
// More configuration see QC_CORE/config.php;
// Site
$config['site']['domain'] = 'http://www.guideofshanghai.com';
$config['site']['short_domain'] = 'guideofshanghai.com';
$config['site']['contact_email'] = 'info@guideofshanghai.com';
$config['site']['contact_phone'] = '021-52068511';
$config['site']['icp_number'] = '沪ICP备12043399号';
$config['site']['lang_package'] = array('zh-cn'=>'简体中文', 'en'=>'English');
$config['site']['language'] = 'zh-cn';
//$config['site']['static_path'] = 'http://www.guideofshanghai.com/static/bootstrap/';
$config['site']['captcha'] = 0; //验证码 0-不使用 1-使用 2-操作错误3次后使用
$config['site']['static_path'] = array('http://test.localhost/jq/', 'http://test.localhost/bootstrap/');
// Base
$config['base']['run_mode'] = 'debug';
// Cache
$config['cache']['handle'] = 'qccache';
$config['cache']['qccache']['gzip'] = FALSE;
// Dispatch
$config['dispatch']['uri_protocol'] = 'rewrite';
$config['dispatch']['rename_group'] = array('gosupe' => 'admin');
// Template
$config['tpl']['theme'] = 'default';

$config['image']['avatar_size'] = array('small'=>'30x30','mid'=>'80x80','big'=>'160x160');
$config['image']['thumb_size'] = array('small'=>'120x120x1','8050'=>'80x50x1');
$config['image']['thumb_extension'] = 'jpg';
// Upload
$config['upload']['tmpdir'] = TMP_PATH. 'uploads'. DS;
$config['upload']['subpath'] = '[ext]'.DS.'[Y][m]'. DS;
$config['upload']['savepath'] = UPLOAD_PATH;
$config['upload']['thumbdir'] = UPLOAD_PATH. 'thumb'.DS;
// Email
$config['email']['protocol'] = 'smtp';
$config['email']['smtp_host'] = 'smtp.qiye.163.com';
$config['email']['smtp_port'] = '25';
$config['email']['smtp_user'] = 'newsletter@urbanatomy.com';
$config['email']['smtp_pass'] = 'wspl2004sh';
$config['email']['smtp_timeout'] = '5';
$config['email']['fromname'] = 'URBANATOMY MEDIA';
$config['email']['from'] = 'newsletter@urbanatomy.com';
$config['email']['mailtype'] = 'html';
