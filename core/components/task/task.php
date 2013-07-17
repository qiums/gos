<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/*
* create by EditPlus v3.01
* author by Sam <wuyou192@163.com>.
*/
//include dirname(__FILE__). '/processes/every_day.php';return ;
$task_model = import('app.task');
$app_conf = $task_model->conf;
if ('run'==$ACTION){
	authservice();
	$log = LOGPATH.'task_'.date('Ym').'.log';
	$time = date('Y-m-d H:i:s');
	ignore_user_abort(TRUE);
	set_time_limit(0);
	if (TRUE===$task_model->run($log)){
		io::write($log, "{$time}\t".ip_address()."\t".'Complete.');
	}
	ignore_user_abort(FALSE);
	return ;
}elseif ('index'==$ACTION){
	$template_file = 'task_index';
}

function run_time($row){
	extract($row);
	if ($atweek) return 'Every '.Lang('weekstr>'.($atweek==7 ? 0 : $atweek));
	if (!$atday AND !$atmonth AND !$atyear) return 'Every day ('.$athours.' O\'clock)';
	if (!$atmonth AND !$atyear) return 'Every month ('.$atday.' Day, '.$athours.' O\'clock)';
	if (!$atyear) return 'Every year ('.Lang('monthstr>'.($atmonth-1)).', '.$atday.' Day, '.$athours.' O\'clock)';
	return $atyear. ', '. Lang('monthstr>'.($atmonth-1)).', '.$atday.' Day, '.$athours.' O\'clock';
}
?>