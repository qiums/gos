<?php if ( ! defined('ROOT')) exit('No direct script access allowed');

class follow_model extends model{
	public $config = array(
		'pagesize'=>20,
		'data_table'=>'follows',
		'sort_fields' => 'ftime,desc',
	);
	function add($uid, $mid, $aid=NULL){
		if (!is_array($mid)){
			$mid = array('mid'=>$mid, 'aid'=>$aid);
		}
		if (!$mid['mid'] OR !$mid['aid']) return 0;
		$mid['ftime'] = D::get('curtime');
		$mid['uid'] = $uid;
		return $this->insert($mid);
	}
	function remove($uid, $mid, $aid=NULL){
		if (!is_array($mid)){
			$mid = array('mid'=>$mid, 'aid'=>$aid);
		}
		if (!$mid['mid'] OR !$mid['aid']) return 0;
		$mid['uid'] = $uid;
		return $this->delete($mid);
	}
}
