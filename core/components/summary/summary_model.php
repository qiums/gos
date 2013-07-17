<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/*
* create by EditPlus v3.01
* author by Sam <wuyou192@163.com>.
*/
class app_summary_model extends model{

	function build(&$data){
	}
	function build_content($data){
		$res = '';
		if (!$data) return $res;
		foreach ($data as $key=>$value){
			$res .= "[{$key}]{$value}[/{$key}]\r\n";
		}
		return $res;
	}
}
