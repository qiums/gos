<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/**
 * Description of pagination
 *
 * @author QiuMS
 */
class Widget_page{
	public function __construct($data=''){
		$config = array_merge(
			gc('widget.page', array()),
			array(
				'perpage' => 10,
			)
		);
		$base = &Base::getInstance();
		if (!$data) $data = $base->gp('pagedata');
		if (!$data['rows'] OR $data['rows']<1 OR !$data['size']) return ;
		$ptotal = ceil($data['rows']/$data['size']);
		$page = min($ptotal, max($data['cur'], 1));
		if ($ptotal > 0){
			$start = ($page<intval($config['perpage']/2)) ? 1 : $page-intval($config['perpage']/2)+1;
			$end = min($start + $config['perpage'], $ptotal+1);
			if ($end-$start < $config['perpage']-1) $start = $end-$config['perpage'];
			$start = max(1, $start);
		}
		$file = $base->tpl->cache_file(dirname(__FILE__).DS.'pagestyle', CACHE_PATH.'views'.DS.'widget'.DS);//die($file);
		if ($file AND is_file($file)){
			global $config, $lang;
			include $file;
		}
	}
}
