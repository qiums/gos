<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
class home_controller extends common_controller{
	public function index(){
		if (!$this->user->us['id']) return $this->login();
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
		// Captcha
		if (1 === gc('site.captcha')){
			 $form['captcha'] = array('type'=>'input:text', 'attr'=>'class="req-any minlength4" maxlength="4" size="4"');
		}
		$form['remember'] = array('type'=>'input:checkbox', 'option'=>'1=Remember me?', 'value'=>'1');
		if ($this->post){
			if (!$this->form->validate($form)){
				exit($this->form->geterror());
			}
			$id = $this->user->login();
			if (FALSE === $id) return $this->user->geterror();
			if (!$this->qdata['url']) $this->qdata['url'] = url('user');
			return redirect($this->qdata['url'], FALSE);
		}
		$this->assign('loginform', $this->form->render($form));
		$this->view('login');
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
	public function history(){
        $history = cookie::get('view_history');
        $loop = $ht = array();
        if ($history) {
            $history = explode(',', $history);
            foreach ($history as $one) {
                $tmp = explode('-', $one);
                $ht[$tmp[0]][] = $tmp[1];
            }
            $tmp = array();
            $this->load->model('archives,channel');
            foreach ($ht as $mid => $id) {
                $this->archives->config = $this->channel->get($mid);
				$data = $this->archives
					->page($this->gp('page'))
					->join('arcindex.aid', 'description,extlink', 'mid')
					->callback()->where('id',$id)->findAll();
                $tmp = array_merge($tmp, $data);
            }
            foreach ($history as $index => $one) {
                foreach ($tmp as $row) {
                    if ($one == "{$row['mid']}-{$row['id']}") {
                        if (!$row['description']) {
                            $row['description'] = "{$row[enaddress]}<br />" . make_phone($row['phone']);
                        }
                        $loop[] = $row;
                    }
                }
            }
            unset($tmp, $ht, $history);
        }
		$this->assign('arrdata', $loop);
		$this->view('home');
	}
}