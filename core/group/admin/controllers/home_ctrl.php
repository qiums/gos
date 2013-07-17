<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
class home_controller extends common_controller{
	function index(){
		if (!session::get('supe')) return $this->login();
		$this->load->database();
		$db = Db::getInstance();
		$db->connect();
		$info = array(
			'server_ipaddr'		=>	request::server('SERVER_NAME').'('.request::server('SERVER_ADDR').'):'. request::server('SERVER_PORT'),
			'phpos_mode'		=>	PHP_OS.'/PHP v'.PHP_VERSION. (@ini_get('safe_mode') ? ' Safe Mode' : NULL),
			'web_server'		=>	request::server('SERVER_SOFTWARE'),
			'sqlserver_version'	=>	$db->dbinfo['version'],
			'database_size'		=>	'-',
			'upload_permission'	=>	@ini_get('file_uploads') ? 'FILE:'.ini_get('upload_max_filesize').' / FORM:'.ini_get('post_max_size') : lang('not_allow_upload'),
			'remote_url_access'	=>	lang('text.'. (ini_get("allow_url_fopen")? 'allowed' : 'unallowed')),
			'server_time'		=>	D::cdate().' ('.D::get('time_zone').')'
		);
		unset($db);
		$this->assign('info', $info);
		$this->view('home');
	}
	function login(){
		$us = $this->vars['user_data'];
		// Login Form
		if ($this->post){
			if (!$this->form->validate()){
				exit($this->form->error());
			}
			$this->user->supe_login();
			if ($this->user->error()) return ;
			session::set('supe', 1);
			cookie::set('#city', $this->post['subsite'],7*86400);
			if ($this->post['language']) cookie::set('supe_language', $this->post['language']);
			return $this->output(1, 'supe_login_success');
		}
		$this->assign('form', $this->form->data('home','login')->render('', array('username'=>$us['name'])));
		$this->view('login');
	}
	public function logout(){
		session::del('supe');
		redirect('admin');
	}
	public function lang(){
		$lang = key($this->qdata);
		if (isset($GLOBALS['config']['site']['lang_package'][$lang])){
			cookie::set('supe_language', $lang);
		}
		redirect('#back#');
	}
}