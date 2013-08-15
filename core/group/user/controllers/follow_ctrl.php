<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
class follow_controller extends common_controller{
	private $uid;

	public function _init_data(){
		$this->uid = $this->vars['user_data']['id'];
		if (!$this->uid) redirect('user/login');
		$this->load->model('user/follow');
	}
	public function add(){
		if (!$this->follow->add($this->uid, $this->post))
				return $this->output(0, 'not_request_data');
		$this->follow->db()->update('arcindex', array('follows'=>'[+]1'), $this->post);
		$this->output(1);
	}
	public function status(){
		$this->post['uid'] = $this->uid;
		$this->output(1, '', array('count' => $this->follow->where($this->post)->count()));
	}
}
