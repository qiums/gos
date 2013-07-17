<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/*
* create by EditPlus v3.01
* author by Sam <wuyou192@163.com>.
*/
class app_manage_model extends Model{
	var $orderkey = 'bydir';

    function init($args=''){
		return parent::init($args);
    }
    function find($name=''){
		$cachename = 'app.app_list';
        if (TRUE===$name OR FALSE===($apps = cache::fq($cachename))){
            $root = ROOT.'app'.DS;
            $files = scandir($root);
            $apps = array();
            foreach ($files as $one){
				$mdir = $root. $one.DS;
				if ($one=='.' OR $one=='..' OR !is_dir($mdir) OR !is_file($mdir.'conf.php')) continue;
				$conf = include $mdir.'conf.php';
                $apps[$one] = (array)$conf + array('pre'=>$one, 'directory' => $mdir);
            }
            if (!empty($apps)) cache::fq($cachename, $apps);
        }
		if (!empty($name)) return isset($apps[$name]) ? $apps[$name] : NULL;
		if ('byname' == $this->orderkey){
			$names = array();
			foreach ($apps as $one){
				$names[] = strtoupper($one['config']['app_name']);
			}
			array_multisort($names, SORT_ASC, $apps);
		}
        return $apps;
    }
	function order($order='bydir'){
		$this->orderkey = $order;
		return $this;
	}
}
?>