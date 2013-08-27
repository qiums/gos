<?php if ( ! defined('ROOT')) exit('No direct script access allowed');

return array(
	// 会员基础信息表
	'data_table' => 'users',
	// 会员扩展信息表
	'addon_table' => 'userinfos',
	// 会员动态表
	'event_table' => 'userevents',
	// 积分记录表
	'log_table' => 'usercredits',
	// 禁止登录的黑名单，支持 * 号通配符
	// 如：127.0.0.1 => 禁止127.0.0.1的IP地址
	// 192.168.* => 禁止192.168开头的IP地址
	'black_ip_list' => '127.0.0.2|192.168.*',
	// 积分规则
	'credit' => array(
		'register' => 2,
		'login' => array(1, 1),
	),
	// 验证类型: 0-不需要验证 1-人工验证 2-邮箱验证
	'check_type' => 2,
);