<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/*
* create by EditPlus v3.01
* author by Sam <wuyou192@163.com>.
*/
class app_task_model extends model{
	function cache(){
		$key = 'app.tasks';
		if (FALSE===($data = cache::fq($key))){
			$data = $this->order('id','asc')
				->property('rstype', 3)
				->findAll();
			cache::fq($key,$data);
		}
		return $data;
	}
	function build(){
		if (!($tasks = $this->cache())) return FALSE;
		$key = 'app.task_begin';
		$time = D::get('curtime');
		$begin = 0;
		$data = getdate($time);
		$rs = array();
		$run = cache::fq($key);
		foreach ($tasks as $id=>$one){
			if (!$one['published']) continue;
			if ($one['atweek']){
				if (7==$one['atweek']) $one['atweek'] = 0;
				$one['atday'] = $data['mday']+($one['atweek']+7-$data['wday'])%7;
			}
			$at = mktime(0>$one['athours']?$data['hours']+1:0,
				0,0,
				$one['atmonth']?$one['atmonth']:(($one['atday']&&$one['atday']<=$data['mday'])?$data['mon']+1:$data['mon']),
				$one['atday']?$one['atday']:(($one['athours']>-1&&$one['athours']<=$data['hours'])?$data['mday']+1:$data['mday']),
				$one['atyear']?$one['atyear']:(($one['atmonth']&&$one['atmonth']<=$data['mon'])?$data['year']+1:$data['year']));
			if ($at<$begin OR !$begin) $begin = $at;
			$rs[$id] = $at;
		}
		$tasks = cache::fq($key, array('begin'=>$begin,'tasks'=>$rs));
		if (!$run) $run = $tasks;
		return $run;
	}
	function run($logfile){
		if (FALSE===($tasks=$this->cache()) || FALSE===($run=$this->build())) return ;
		$task_time = D::get('curtime');
		foreach ($run['tasks'] as $id=>$at){
			if ($at>$task_time) continue;
			$task = $tasks[$id];
			$code = $task['runcode'];
			$log = '';
			if (';'!=substr($code,-1)) $code .= ';';
			if ('php'==$task['runtype']){
				$rs = @eval('?><?php '.$code.' ?>');
				if (is_null($rs)) $rs = TRUE;
				if ($rs) $log = '[php]Eval php code success.';
			}elseif ('db'==$task['runtype']){
				$rs = Db::run($code);
				if ($rs) $log = '[db]Database query success.';
			}elseif ('file'==$task['runtype']){
				$file = dirname(__FILE__).DS. 'processes'.DS. trim($code,';').'.php';
				$rs = FALSE;
				if (!is_file($file)){
					$log = '[file]The file ('.$file.') Not found.';
				}else{
					$log = '[file]Include file ('.$file.') success.';
					$rs = TRUE;
					include $file;
				}
			}
			if ($log) io::write($logfile, date('Y-m-d H:i:s', $task_time)."\t".ip_address()."\t".$log);
			if ($rs) $this->limit(1)->update(array('runtime'=>$task_time),"id='$id'");
		}
		return TRUE;
	}
	function runfile($file){
		include $file;
	}
}
?>