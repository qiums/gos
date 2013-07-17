<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/*
* create by EditPlus v3.01
* author by Sam <wuyou192@163.com>.
*/
class com_file_model extends model{
	public $thumb_fixed = 0;
	public $thumb_size = FALSE;
	private $thumb;
	private $picsize;

	function build(&$data){
		if ($data['filepath'] AND $this->thumb){
			if (function_exists('ct')){
				$thumb = explode('x', $this->thumb);
				array_unshift($thumb, $data['filepath']);
				$data['thumb'] = call_user_func_array('ct', $thumb);
				unset($thumb);
			}else{
				$data['thumb'] = fileurl($data['filepath']);
			}
		}
		if($data['filepath']){
			if($this->picsize AND function_exists('ct')){
				$thumb = explode('x', $this->picsize);
				array_unshift($thumb, $data['filepath']);
				$data['fileurl'] = call_user_func_array('ct', $thumb);
				unset($thumb);
			}else{
				$data['fileurl'] = fileurl($data['filepath']);
			}
		}
		if($data['filesize']) $data['formatsize'] = formatSize($data['filesize']);
	}
	function findAll($cond=array(), $table=''){
		$this->callback();
		return parent::findAll($cond, $table);
	}
	function delete($cond=''){
		//$data = Db::delete($this->get_maintbl(), $cond);
		$data = $this->findAll($cond);
		if (!$data) return true;
		parent::delete($cond);//
		$uids = array();
		foreach ($data as $f){
			if (!isset($uids[$f['uid']])) $uids[$f['uid']] = 0;
			$uids[$f['uid']] += io::delFile(THISPATH. $f['pathname']);
		}
		foreach ($uids as $id=>$size){
			if ($size>0) Db::update('members', array('attachsize'=>array('update',0-$size)), "id='$id'");
		}
		return $data;
	}
}
?>