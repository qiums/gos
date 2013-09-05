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
		$this->assign('user_data', $this->user->where('id',$this->uid)->find());
		$this->view('home');
	}
	public function login(){
		if ($this->uid) return redirect('user');
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
			$logintype = $this->post['logintype'];
			if (!$this->form->validate($form)){
				exit($this->form->geterror());
			}
			$id = $this->user->login();
			if (FALSE === $id) return $this->user->error();
			if (!$this->qdata['url']) $this->qdata['url'] = url('user');
			if ($_ENV['ajaxreq']){
				$btn[lang('button.continue')] = 'standard'===$logintype ? 'reload' : 'close';//$this->qdata['url'];
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
		if (!$this->uid) return redirect('user/home/login');
		$this->user->del_cookie();
		redirect('#back#');
	}
	public function signup(){
		// Register Form
		if ($this->uid) return redirect('user');
		if ($this->post){
			if (!$this->form->validate($this->regform)){
				exit($this->form->geterror());
			}
			$res = $this->user->add();
			if (!is_array($res)) return $this->user->error();
			if ($res['uncheck'] == 2){
				if (!$this->send($res)){
					$this->user->where('id', $res['id'])->update(array('uncheck'=>1));
					$res['uncheck'] = 1;
				}
			}
			if ($_ENV['ajaxreq']){
				$btn[lang('button.continue')] = 'close';
				return $this->output(1,
						lang("register_success_t{$res['uncheck']}", $res),
						$btn);
			}
			return redirect('user/home/tips/do/signup');//$this->view('tips');
		}
	}
	private function send($res, $tpl='register'){
		$this->assign(array(
			'validurl' => rtrim(gc('env.domain')). url('user/home/valid', array('k'=>authcode("{$res['id']}\t{$res['createtime']}", 'ENCODE'))),
			'user' => $res,
		))->view("public/mail_{$tpl}", TRUE);
		return $this->email->subject(lang('mail_register_subject'))
			->from(gc('email.from'), lang('site_name'))
			->to($res['email'])
			->message($this->html_content)
			->send();
	}
	public function resend(){
		if (!$this->uid) return $this->output(0);
		$user = $this->user->where('id', $this->uid)->find();
		if ($this->send($user)){
			return $this->output(1, 'resend_success');
		}
		return $this->output(0);
	}
	public function findpass(){
		if ($this->post){
			$email = $this->post['email'];
			if (!$email) return $this->output(0);
			$user = $this->user->where('email', $email)->find();
			if (!$user) return $this->output(0, 'form-element-error', array('id'=>'#find-email', 'error'=>lang('tips.email_notfound')));
			$time = D::get('curtime');
			$this->assign(array(
				'validurl' => rtrim(gc('env.domain')). url('user/home/findpass', array('k'=>authcode("{$user['id']}\t{$time}", 'ENCODE'))),
				'user' => $user,
			))->view("public/mail_findpass", TRUE);
			$send = $this->email->subject(lang('mail_findpass_subject'))
				->from(gc('email.from'), lang('site_name'))
				->to($user['email'])
				->message($this->html_content)
				->send();
			$btn[lang('button.continue')] = 'close';
			if ($send) return $this->output(1, 'findpass_tips', $btn);
			return $this->output(0, $this->email->print_debugger());
		}
		$code = explode("\t", authcode($this->gp('k')));
		print_r($code);
	}
	public function valid(){
		$code = explode("\t", authcode($this->gp('k')));
		if (!$code || !$code[0]) return $this->output(0, 'code_invalid');
		if ($code[1]+86400 < D::get('curtime')) return $this->output(0, 'code_expired');
		$this->user->where('id', $code[0])->update(array('uncheck'=>0));
		$this->view('tips');
	}
	public function tips(){
		return $this->view('tips');
	}
}