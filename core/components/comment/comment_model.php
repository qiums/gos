<?php if ( ! defined('SYSPATH')) exit('No direct script access allowed');

class com_comment_model extends model{
	function treelist($cond){
		$data = $this->order('updatetime','desc')
			->join('users.id~uid', 'username,nickname,avatar')
			->attr('datatype', 3)->where($cond + array('pid'=>0))
			->findAll();
		if (!$data) return array();
		//$rows = $this->db()->count;
		$cond = array(
			'rootid' => array_keys($data),
			'published' => 1,
			'depth'=>'> 1',
		);
		$data = array_merge($data,
			$this->join('users.id~uid', 'username,nickname,avatar')
				->attr('datatype', 0)
				->order('createtime','asc')
				->findAll($cond));
		$data = qcarray_sort(qcsort($data));
		//Db::set('rows', $rows);
		return $data;
	}
	function insert($p, $parent=array(), $a=0){
		$pid = (int)$p['pid'];
		if ($parent){
			$p['aid'] = $parent['aid'];
			$p['mid'] = $parent['mid'];
			$p['depth'] = $parent['depth']+1;
			$p['rootid'] = $p['pid'];
		}
		//$p['uid'] = Base::getInstance()->vars['user_data']['id'];
		$p['createtime'] = $p['updatetime'] = D::get('curtime');
		$p['published'] = $this->gc('default_pulished', 1);
		$id = parent::insert($p);
		if ($parent){
			$update['rootid'] = $parent['rootid'];
			$update['fuid']=$parent['uid'];
			$update['node']=($parent['node']?$parent['node']:$pid).','.$id;
			parent::update(array('updatetime'=> $p['createtime']), array('id'=>explode(',', $update['node'])));
		}else{
			$update['rootid']=$id;
			$update['fuid']=$p['uid'];
		}
		parent::update($update, "id='$id'");
		return intval($id);
	}
	function getone($cond){
		return $this->join('users.id~uid', 'username,nickname,avatar')->where($cond)->find();
	}
}
