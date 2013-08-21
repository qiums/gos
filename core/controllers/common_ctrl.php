<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
class common_controller extends controller{
	protected $uid = 0;
	protected $username = '';

	public function __construct(){
		parent::__construct();
		lang('common');
		$this->load->helper('extend');
		//$this->load->model('archives/channel');
		$this->load->model('user');
		$us = $this->user->us;
		if ('user' === gc('env.group')){
			if (!$us['id'] AND 'home'!==gc('env.controller') AND !in_array(gc('env.action'), array('index','login','logout'))){
				redirect('user/home/login');
			}
		}
		$this->assign('user_perm', $us['perm']);
		unset($us['perm']);
		$this->assign('user_data', $us);
		$this->uid = $us['id'];
		$this->username = $us['name'];
		if ($this->post){
			unset($this->post['token']);
		}
		gc('env.static_path', gc('env.webroot').'static/', TRUE);
		// 导航
		$this->assign('topnav', $this->category->root());
	}
	/*public function __get($name){
		//if ('category' === $name) return $this->load->model('archives/category');
		$channel = $this->channel->get($name);
		if ($channel){
			$o = $this->load->model("archives/{$name}", $channel);
			if ($o) return $o;
			return $this->load->model("archives", $channel);
		}
		return parent::__get($name);
	}*/
}