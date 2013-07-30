<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/**
 * Description of pagination
 *
 * @author QiuMS
 */
class Widget_page{
	public function __construct() {
		//return $this->run();
	}
	public function run($conf=array()){
		if (is_scalar($conf)) $conf = str2array($conf);
		$conf = array_merge(
			gc('widget.page', array()),
			array(
				'perpage' => 10,
				'url' => '#current#',
			), $conf
		);
		$base = &Base::getInstance();
		$data = $base->gp('pagedata');
		if (!$data['rows'] OR $data['rows']<1 OR (!$data['ptotal'] AND !$data['size'])) return ;
		$ptotal = $data['ptotal'] ? $data['ptotal'] : ceil($data['rows']/$data['size']);
		$page = min($ptotal, max($data['cur'], 1));
		if ($ptotal > 0){
			$start = ($page<intval($conf['perpage']/2)) ? 1 : $page-intval($conf['perpage']/2)+1;
			$end = min($start + $conf['perpage'], $ptotal+1);
			if ($end-$start < $conf['perpage']-1) $start = $end-$conf['perpage'];
			$start = max(1, $start);
		}
		$file = $base->tpl->cache_file(dirname(__FILE__).DS.'pagestyle', CACHE_PATH.'views'.DS.'widget'.DS);//die($file);
		if ($file AND is_file($file)){
			global $config, $lang;
			include $file;
		}
	}
}
