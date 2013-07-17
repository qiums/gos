<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/*
* create by EditPlus v3.01
* author by Sam <wuyou192@163.com>.
*/
// 删除3个月前的广告访问记录/点击记录
$time = D::add('m', -2, 1);
Db::delete('ads_click', array('visit_time'=>"< $time"));
Db::delete('ads_view', array('visit_time'=>"< $time"));
// 删除3个月前的个人短消息
Db::delete('message', array('createtime'=>"< $time"));
// 优化所有数据表
$tables = Db::run('SHOW TABLES');
foreach ($tables as $table){
	Db::run("OPTIMIZE TABLE `{$table['Name']}`");
}
