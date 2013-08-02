<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
class common_controller extends controller{
	public function __construct(){
		parent::__construct();
		import('config.config'); // 尝试加载分组配置文件
		$lang = cookie::get('supe_language');
		if (!$lang) $lang = gc('site.language');
		$GLOBALS['config']['site']['language'] = $lang;
		lang('common');
		lang('admin'); // 尝试加载后台语言包
		$this->assign('language', $lang);
		$this->load->helper('extend');
		$this->load->model('user');
		$us = $this->user->us;
		if (!$us['id']) redirect(url('user/home/login', array('url' => url('admin'))), FALSE);
		$this->assign('user_perm', $us['perm']);
		unset($us['perm']);
		$this->assign('user_data', $us);
		if (!session::get('supe')){
			if ('home'===gc('env.controller') AND in_array(gc('env.action'), array('index','login'))){
				return ;
			}
			redirect('admin/home/login');
		}
		if ($this->post){
			if ($this->post['token'] !== formhash()){
				$this->output(0, 'token_fail');
				exit;
			}
			unset($this->post['token']);
		}
		$this->load->model('user/group');
		$this->build_menu();
		unset($us, $lang);
	}
	private function build_menu(){
		$supe_menu = lang('supe_menu');
		$this->group->supemenu($supe_menu, $this->vars['user_perm']);
		$this->assign($this->get_pagetit($supe_menu));
		$this->assign('supe_menu', $supe_menu);
		unset($supe_menu);
	}
	private function get_pagetit($menu){
		$as = array('tabletit' => lang('form_label.'. gc('env.controller').'_'.gc('env.action')));
		$c = gc('env.controller');$a=gc('env.action');
		if ($_ENV['iscp']) $c = 'cp';
		if (isset($menu[$c])){
			$as['pagetit'] = $menu[$c][0];
			$as['menu_active'] = $c;
			$as['sub_menu'] = $menu[$c][1];
			if (is_array($menu[$c][1])){
				if (isset($menu[$c][1][$c]) OR isset($menu[$c][1]["{$c}/{$a}"])){
					$as['sub_pagetit'] = isset($menu[$c][1][$c]) ? $menu[$c][1][$c] : $menu[$c][1]["{$c}/{$a}"];
				}
			}
		}else{
			foreach ($menu as $key=>$one){
				if ($one[1] AND (isset($one[1][$c]) OR isset($one[1]["{$c}/{$a}"]))){
					$as['sub_pagetit'] = isset($one[1][$c]) ? $one[1][$c] : $one[1]["{$c}/{$a}"];
					$as['pagetit'] = $one[0];
					$as['menu_active'] = $key;
					$as['sub_menu'] = $one[1];
					break;
				}
			}
		}
		return $as;
	}
}