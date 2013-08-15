<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/*
* create by EditPlus v3.01
* author by Sam <wuyou192@163.com>.
*/
class com_manage_model extends Model{
	private $orderkey = 'byname';

    public function get_coms($name=''){
		$cachename = 'com.cp_data';
        if (TRUE===$name OR FALSE===($apps = cache::q($cachename))){
            $root = dirname(dirname(__FILE__)).DS;
            $files = scandir($root);
            $apps = array();
            foreach ($files as $one){
				$mdir = $root. $one.DS;
				if ($one=='.' OR $one=='..' OR !is_dir($mdir) OR !is_file($mdir.'config.php')) continue;
				include $mdir.'config.php';
				if (!$config[$one]) continue;
                $apps[$one] = array('app_name'=>$config[$one]['app_name'], 'directory' => $mdir);
            }
            if (!empty($apps)) cache::fq($cachename, $apps);
        }
		if (!empty($name)) return isset($apps[$name]) ? $apps[$name] : NULL;
		if ('byname' == $this->orderkey){
			$names = array();
			foreach ($apps as $one){
				$names[] = strtoupper($one['app_name']);
			}
			array_multisort($names, SORT_ASC, $apps);
		}
        return $apps;
    }
	/*function order($order='bydir'){
		$this->orderkey = $order;
		return $this;
	}*/
}
?>