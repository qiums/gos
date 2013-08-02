<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/*
* create by EditPlus v3.01
* author by Sam <wuyou192@163.com>.
*/
class com_file_model extends model{

	public function build(&$data){
		$data['thumb'] = $data['fileurl'] = fileurl($data['filepath']);
		$data['formatsize'] = formatSize($data['filesize']);
		$thumb = Base::getInstance()->gp('thumb');
		if ($data['filepath'] AND $thumb){
			if (is_scalar($thumb) AND function_exists('ct')){
				$thumb_size = gc('image.thumb_size');
				$thumb = $thumb_size[$thumb];
				if ($thumb){
					$thumb = explode('x', $thumb);
					array_unshift($thumb, $data['filepath']);
					$data['thumb'] = call_user_func_array('ct', $thumb);
				}
			}
		}
	}
	public function hash($hash='add'){
		$user = Base::getInstance()->vars['user_data'];
		if ('add'!==$hash){
			if (!is_scalar($hash)) return FALSE;
			$hash = explode("\t", authcode($hash));
			if (!$hash OR !$hash[0]) return FALSE;
			return $hash;
		}
		return authcode("{$user['id']}\t{$user['name']}\t{$hash}", 'ENCODE');
	}
	public function delete($cond=''){
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