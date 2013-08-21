<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
class home_controller extends common_controller{
	private $regform;
	
	public function _init_data(){
		$this->regform = array(
			'username'	=>	array('type'=>'input:text', 'attr'=>'class="req-any minlength4" maxlength="16"'),
			'passwd'	=>	array('type'=>'input:password', 'attr'=>'class="req-any minlength4 rule-passwd" maxlength="20"', 'function'=>'md5'),
			'repasswd'	=>	array('type'=>'input:password', 'attr'=>'class="req-any minlength4 rule-passwd" maxlength="20"', 'function'=>'md5'),
			'email'		=>	array('type'=>'input:text', 'attr'=>'class="req-email minlength4" maxlength="50"'),
			//'captcha'	=>	array('type'=>'input:text', 'attr'=>'class="req-any minlength4" maxlength="4" size="4"'),
		);
	}
	public function index(){
		$do = $this->gp('do');
		if (!$this->uid){
			if ('check'===$do) return $this->output(0);
			return $this->login();
		}
		if ('check'===$do){
			return $this->output(1, '', array(
				'command' => (isset($this->vars['user_perm']['admin']) ? '<a href="'.url('admin').'"><b>Admin Panel</b></a>|' : ''). '<a href="'.url('user/home/logout').'">'. lang('button.logout'). '</a>',
				'username' => '<a href="'. url('user'). '"><b>'. $this->vars['user_data']['name']. '</b></a>',
			));
		}
		$count = array(
			'myfav' => $this->follow->where('uid', $this->uid)->count(),
			'history' => 0,
		);
		$this->assign('count', $count);
		$this->assign('user_data', $this->user->find($this->uid));
		$this->view('home');
	}
	public function login(){
		if (($black_ip = $this->user->gc('black_ip_list'))){
			if (preg_match('/'. str_replace(array('.', '*'), array('\.', '.+?'), $black_ip). '/is', response::ip()))
				return $this->output('disable_login_ip');
		}
		// Login Form
		$form = array(
			'username'	=>	array('type'=>'input:text', 'attr'=>'class="req-any minlength4" maxlength="16"'),
			'passwd'	=>	array('type'=>'input:password', 'attr'=>'class="req-any minlength4" maxlength="20"', 'function'=>'md5'),
		);
		$form['remember'] = array('type'=>'input:checkbox', 'option'=>'1=Remember me?', 'value'=>'1');
		if ($this->post){
			if (!$this->form->validate($form)){
				exit($this->form->geterror());
			}
			$id = $this->user->login();
			if (FALSE === $id) return $this->user->error();
			if (!$this->qdata['url']) $this->qdata['url'] = url('user');
			if ($_ENV['ajaxreq']){
				$btn[lang('button.continue')] = 'close';//$this->qdata['url'];
				return $this->output(1,
						lang('login_success', $this->post),
						$btn);
			}
			return redirect($this->qdata['url'], FALSE);
		}
		$this->assign('loginform', $this->form->render($form));
		$this->form->idpre = 'reg';
		$this->assign('regform', $this->form->render($this->regform));
		$this->view('login');
	}
	public function logout(){
		$this->user->del_cookie();
		redirect('#back#');
	}
	public function register(){
		// Register Form
		$form = array(
			'username'	=>	array('type'=>'input:text', 'attr'=>'class="req-any minlength4" maxlength="16"'),
			'passwd'	=>	array('type'=>'input:password', 'attr'=>'class="req-any minlength4 rule-pwd" maxlength="20"'),
			'repasswd'	=>	array('type'=>'input:password', 'attr'=>'class="req-any minlength4 rule-pwd" maxlength="20"'),
			'email'		=>	array('type'=>'input:text', 'attr'=>'class="req-email minlength4" maxlength="50"'),
			'captcha'	=>	array('type'=>'input:text', 'attr'=>'class="req-any minlength4" maxlength="4" size="4"'),
		);
		$this->assign('regform', $this->form->render($form));
	}
}