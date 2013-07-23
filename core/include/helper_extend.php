<?php if ( ! defined('ROOT')) exit('No direct script access allowed');

require SYS_PATH. 'helper'.DS .'extend.php';

function ismobile() {
	$mobile = array();
	static $mobilebrowser_list =array('iphone', 'android', 'phone', 'mobile', 'wap', 'netfront', 'java', 'opera mobi', 'opera mini',
				'ucweb', 'windows ce', 'symbian', 'series', 'webos', 'sony', 'blackberry', 'dopod', 'nokia', 'samsung',
				'palmsource', 'xda', 'pieplus', 'meizu', 'midp', 'cldc', 'motorola', 'foma', 'docomo', 'up.browser',
				'up.link', 'blazer', 'helio', 'hosin', 'huawei', 'novarra', 'coolpad', 'webos', 'techfaith', 'palmsource',
				'alcatel', 'amoi', 'ktouch', 'nexian', 'ericsson', 'philips', 'sagem', 'wellcom', 'bunjalloo', 'maui', 'smartphone',
				'iemobile', 'spice', 'bird', 'zte-', 'longcos', 'pantech', 'gionee', 'portalmmm', 'jig browser', 'hiptop',
				'benq', 'haier', '^lct', '320x320', '240x320', '176x220');
	$pad_list = array('pad', 'gt-p1000');

	$useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
	if(qstrpos($useragent, $pad_list)) return FALSE;
	global $config;
	if(($v = qstrpos($useragent, $mobilebrowser_list, TRUE))) {
		$config['env']['mobile'] = $v;
		return TRUE;
	}
	$brower = array('mozilla', 'chrome', 'safari', 'opera', 'm3gate', 'winwap', 'openwave', 'myop');
	if(qstrpos($useragent, $brower)) return FALSE;

	$config['env']['mobile'] = 'unknown';
	return req('mobile') === 'yes';
}
define('EARTH_RADIUS', 6378.137);
/**
获取与中心坐标指定距离的坐标点
**/
function near_latlng($lat, $lng, $distance){
	if(!$distance) $distance = (float)req('distance');
	//$distance = $distance/1000;
	$dlng = abs(rad2deg(2 * asin(sin($distance / (2 * EARTH_RADIUS)) / cos($lat))));
	$dlat = abs(rad2deg($distance / EARTH_RADIUS));
	return array('x'=>array($lat-$dlat, $lng-$dlng), 'y'=>array($lat+$dlat, $lng+$dlng));
}
function get_rad($d){
	return (float)$d * M_PI / 180.0;
}
//获取两个坐标间的直线距离 $x: A点座标 $y: B点座标
function get_distance($x, $y){
	$x1 = get_rad($x[0]);
	$y1 = get_rad($y[0]);
	$a = $x1-$y1;
	$b = get_rad($x[1])-get_rad($y[1]);
	$s = 2 * asin(sqrt(pow(sin($a/2),2) + cos($x1)*cos($y1)*pow(sin($b/2),2)));
	$s = $s* EARTH_RADIUS;//地球半径，单位km
	return round($s,3);
}
function get_price($num){
	if (!$num) return 'Free';
	return sprintf('%01.2f', $num);
}
function is_mobile(){
	return preg_match('/(mozilla|m3gate|winwap|openwave)/i', $_SERVER['HTTP_USER_AGENT']);
}
function replace_alias($alias){
	$alias = preg_replace('/(\s|\/)+/s','-', strtolower(trim($alias)));
	$alias = preg_replace('/[^a-z0-9\-\/]/si','', $alias);
	return trim(preg_replace('/\-+/s','-', $alias), '-');
}
function formhash($specialadd = '') {
	$hashadd = defined('IN_ADMINCP') ? 'Only For Admin Control Panel' : '';
	return substr(md5(substr(NOW, 0, -5).
		(string)Base::getInstance()->vars['user_data']['name'].
		(string)Base::getInstance()->vars['user_data']['id'].
		gc('base.encryption_key'). $hashadd. $specialadd), 8, 8);
}
function submitcheck($var='formhash', $allowget = 0) {
	if (!req($var)){
		if (TRUE===authservice()) return TRUE;
		cprint_exit('submit_hash_invalid');
	}
	$hash = req($var);
	unset($_POST[$var]);
	$referer = $_SERVER['HTTP_REFERER'];
	if ($allowget || (
		$_SERVER['REQUEST_METHOD']=='POST' && !empty($hash) && $hash==formhash() && empty($_SERVER['HTTP_X_FLASH_VERSION']) &&
		(empty($referer) ||	preg_replace("/https?:\/\/([^\:\/]+).*/i", "\\1", $referer)==preg_replace("/([^\:]+).*/", "\\1", $_SERVER['HTTP_HOST']))
		)
		){
		$code = strtoupper(req('captcha','p'));
		if($GLOBALS['use_captcha']){
			if($code!=cookie::get('captcha')) cprint_exit('submit_captcha_invalid');
		}
		unset($_POST['captcha']);
		return TRUE;
	} else {
		cprint_exit('submit_invalid');
	}
}
function authservice(){
	if ('debug'==gc('base.run_mode')) return TRUE;
	$key = req('AUTHID');
	$auth = req('AUTHKEY');
	$ws = $GLOBALS['config']['webservice']['authkey'];
	if (!isset($ws[$key]) OR $ws[$key]!=$auth){
		io::append($log, date('Y-m-d H:i:s'). "\t".ip_address()."\t".'Access denied (AUTH).');
		cprint_exit('auth_denied');
	}
	unset($_POST['AUTHID'],$_POST['AUTHKEY']);
	return TRUE;
}
function source_value($key, $val, $pre=', '){
	global $lang;
	if (!isset($lang['form_source'][$key])) return '';
	if (is_scalar($val)) $val = explode(',', $val);
	if (is_array($val)) $val = array_flip($val);
	return join($pre, array_intersect_key($lang['form_source'][$key], $val));
}

function upwebroot($domain=TRUE){
	if (defined('UPLOAD_FILE_URL')) return UPLOAD_FILE_URL;
	$uc = gc('upload');
	return ($domain AND isset($uc['domain'])) ? trim($uc['domain'],'/')
		: gc('env.webroot'). (str_replace(DS, '/', str_replace(array(THISPATH, ROOT), '', $uc['savepath'])));
}
function fileurl($file){
	$file = str_replace(DS, '/', str_replace(gc('upload.savepath'), '', $file));
	$dir = rtrim(upwebroot(), '/').'/';
	return $dir.trim(str_replace($dir,'','/'.trim($file,'/')), '/');
}
function filepath($file){
	$savepath = gc('upload.savepath');
	return $savepath. str_replace(array($savepath,'/'), array('',DS), $file);
}
function cut_thumb($path,$width=120,$height=120,$fixed=0,$df="nopic"){
	if(!$width||!$height) return fileurl($path);
	global $config;
	if (!preg_match('/^\d+$/i', $fixed)){
		$df = $fixed;
		$fixed = 0;
	}
	//$savepath = $config['upload']['savepath'];
	$base = Base::getInstance();
	$base->load->libs('image');
    if(!$base->image) return fileurl($path);
	$realpath = filepath($path);
	$di = $config['env']['static_path']."common/{$df}-{$width}x{$height}.png";
	if (empty($realpath) || !file_exists($realpath) || !is_file($realpath)) return $di;
	$thumb_path = rtrim($config['upload']['thumbdir']?($config['upload']['thumbdir'].date('Ym'.DS,filectime($realpath))):dirname($realpath),DS).DS;
	if(!is_dir($thumb_path)) io::mkdir($thumb_path);
	$basename = basename($realpath, substr($realpath, strrpos($realpath,'.')));
	$filename = $thumb_path.$basename.".{$width}x{$height}.{$config['image']['thumb_extension']}";
	if (is_file($filename)) return fileurl($filename);
	$fn = $fixed?'fixed_cut':'resize_cut';
	$base->image->make_water = false;
	if (TRUE!==$base->image->set_file($realpath,$filename)->$fn($width,$height)) return fileurl($path);
	$base->image->output();
	return fileurl($filename);
}
function del_file($path){
	global $config;
	$savepath = $config['upload']['savepath'];
	$path = $savepath. str_replace(array($savepath,'/'), array('',DS), $path);
	$info = pathinfo($path);
	$size = filesize($path);
	if (preg_match('/(jpg|jpeg|gif|png)$/is',$path)){
		$thumb_path = trim($config['upload']['thumbdir'] ?
			($config['upload']['thumbdir']. date('Ym'. DS, filectime($path)))
			: dirname($path), DS).DS;
		$format = $thumb_path.basename($path, '.'.$info['extension']).'_*.*';
		foreach (glob($format) as $one){
			@unlink($one);
		}
	}
	if (is_file($path)) @unlink($path);
	return $size;
}
function ct($path,$width=120,$height=120,$fixed=0,$df='nopic'){
	return cut_thumb($path,$width,$height,$fixed,$df);
}
function df($path){
	return del_file($path);
}
function check_perm($perm, $value){
	return !$perm OR FALSE!==cstrpos($perm, $value);
}
function get_rundate($data){
	$run = '';
	if ($data['runtype']==3){
		$run .= 'On '.D::cdate($data['begindate'],'M d');
	}else{
		if (2===(int)$data['runtype']){
			$run.=($data['begindate']>0?'From ':'Daily').D::cdate($data['begindate'],'M d Y');
			if ($data['enddate']>0) $run .= ' until '.D::cdate($data['enddate'],'M d Y');
		}elseif (1===(int)$data['runtype']){
			$run.= get_week($data['runweek']);
			if ($data['enddate']>0) $run .= ' until '.D::cdate($data['enddate'],'M d Y');
		}elseif (0===(int)$data['runtype']){
			$run = " Every day";
		}
	}
	return $run;
}
function short_rundate($data){
	if (3==$data['runtype']) return D::cdate($data['begindate'],'M d');
	$run = '';$e=array();
	$b = getdate($data['begindate']);
	if ($data['enddate']>0) $e = getdate($data['enddate']);
	if (2==$data['runtype']){
		$run = D::cdate($data['begindate'],'M j').'-';
		if ($b['year']==$e['year'] AND $b['mon']==$e['mon']){
			$run .= D::cdate($data['enddate'],'j');
		}elseif ($b['year']==$e['year']){
			$run .= D::cdate($data['enddate'],'M j Y');
		}
	}elseif (1==$data['runtype']){
		if ('0,1,2,3,4,5,6'==$data['runweek']){
			$data['runtype'] = 0;
		}else{
			$run = get_week($data['runweek']);
		}
	}
	if (!$data['runtype']) return 'Every day';
	return $run;
}
function get_week($w){
	$week=array('0,1,2,3,4,5'=>'0,5','0,1,2,3,4'=>'0,4','0,1,2,3'=>'0,3','0,1,2'=>'0,2',
		'1,2,3,4,5,6'=>'1,6','1,2,3,4,5'=>'1,5','1,2,3,4'=>'1,4','1,2,3'=>'1,3',
		'2,3,4,5,6'=>'2,6','2,3,4,5'=>'2,5','2,3,4'=>'2,4',
		'3,4,5,6'=>'3,6','3,4,5'=>'3,5',);
	if (isset($week[$w])) $w=$week[$w];
	return 'Every '.array_intersect_value($w, $GLOBALS['lang']['short_weekstr'],'&amp;');
}
/*** Set Phone ***/
function parse_phone($phone){
	if (empty($phone)) return '';
	$phone = str_replace(' ', '', trim($phone));
	$phone = preg_replace('/ext(\.)*/s', '-', $phone);
	$phone = preg_replace('/[^\d\-\,]/s', ',', $phone);
	$phone = explode(',', $phone);
	foreach ($phone as $i=>$row){
		if (empty($row)){
			unset($phone[$i]);
			continue;
		}
		$rephone = str_replace('-', '', trim($row));
		if (11 == strlen($rephone) OR 8 == strlen($rephone)){
			$phone[$i] = $rephone;
		}elseif (FALSE !== strpos($row, '-')){
			$exrow = explode('-', $row);
			if (count($exrow) == 2){
				$phone[$i] = $row;
			}elseif (count($exrow == 3)){
				$phone[$i] = join('', array_slice($exrow, 0, 2)).'-'. $exrow[2];
			}else{
				unset($phone[$i]);
			}
		}
	}
	return join(',', $phone);
}
/*** Get Phone ***/
function make_phone($phone){
	if (empty($phone)) return '';
	$ar = preg_split('/[\/,]+/', $phone);
	foreach ($ar as $i=>$val){
		$val = preg_replace('/\s+/i','',$val);
		$ex = '';
		if (FALSE!==($pos=strpos($val,'-'))){
			$ex = substr($val,$pos);
			$val = substr($val,0,$pos);
		}
		if (8==strlen($val)){
			$val = substr($val,0,4).' '. substr($val,4);
		}elseif (11==strlen($val)){
			$val = substr($val,0,3).' '. substr($val,3,4).' '. substr($val,7);
		}elseif (10==strlen($val)){
			$val = substr($val,0,3).' '. substr($val,3,3).' '. substr($val,6);
		}
		$ar[$i] = $val.$ex;
	}
	return join(', ',$ar);
}
