<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/*
* create by EditPlus v3.01
* author by Sam <wuyou192@163.com>.
*/
class user_model extends model{
	public $us; // User status

	public function __construct(){
		parent::__construct();
		$this->load->model('user/group');
		$this->us = $this->status(Base::getInstance()->gp('auth'));
	}
	// Member status
	private function status($auth=''){
		//return array('id'=>1,'name'=>'urbanatomy','groupid'=>4,'sysgid'=>1,'sucityid'=>'0','perm'=>$this->group()->permission(4,1));//测试会员
		$lastlogin = cookie::get('lastlogin');
		if (!$auth) $auth = cookie::get('#userauth');
		if ($auth) $auth = explode("\t", $auth);
		if (empty($lastlogin) AND !empty($auth[0])){
			$user = $this->login(array('id'=>$auth[0]));
			if (!is_array($user)) return array('id'=>0,'groupid'=>0);
		}
		if (($supe = session::get('supeflag'))) $ar['supe'] = $supe;
		if (!empty($auth)){
			$ar = array('name' => cookie::get('username'));
			list($ar['id'],$ar['email'],$ar['groupid'],$ar['sysgid'],$ar['specgid'],$ar['sucityid']) = $auth;
			$ar['perm'] = $this->group->permission($ar);
			return $ar;
		}
		return array('id'=>0,'groupid'=>0,'perm'=>$this->group->permission(0));
	}/**/
	// Login by administrator
	function supe_login($post=array()){
		if (!$post) $post = Base::getInstance()->post;
		$supe = $this->where('username', $post['username'])->find();
		if (empty($supe)) return $this->error('username_notfound', 'username');
		if ($post['passwd'] !== $supe['passwd']) return $this->error('passwd_notfound', 'passwd');
		return $post;
	}
	function get_user($uid){
		if (is_numeric($uid)){
			$cond['id'] = $uid;
		}else{
			$cond['username'] = $uid;
		}
		return $this->attr('fields', 'id,username,email')->find($cond);
	}
	// Login by user
	function login($post=array()){
		if (!$post) $post = Base::getInstance()->post;
		if (FALSE!==strpos($post['username'],'@')){
			$cond['email'] = $post['username'];
		}elseif (isset($post['id'])){
			$cond['id'] = $post['id'];
		}else{
			$cond['username'] = $post['username'];
		}
		if (!isset($post['tpasswd']) AND isset($post['passwd'])){
			$post['tpasswd'] = $post['passwd'];
			unset($post['passwd']);
		}
		$this->attr('fields', 'id,username,passwd,email,groupid,sysgid,specgid,sucityid');
		$user = $this->where($cond)->find();
		if (!$user){
			return $this->error('username_notfound', 'username');
		}elseif ($post['tpasswd'] !== $user['passwd']){
			return $this->error('passwd_notfound', 'passwd');
		}
		$perm = $this->group->permission($user);
		if (!$perm['base']['login']) return $this->error('disable_login');//禁止登录
		unset($perm, $cond);
		$this->update_login($user);
		$this->set_cookie($user, $post['remember'] ? 7*86400 : 0);
		return $user;
	}
	function update_login(&$user){
		$up = array('logintime'=>D::get('curtime'), 'loginip'=>response::ip());
		$user = array_merge($user, $up);
		parent::update($up,	array('id'=>$user['id']));
		$this->update_credit($user, 'login', 'login', "user-{$user['id']}");
	}
	// Update userinfos
	function update_info($data){
		$id = (int)$data['id'];
		if (!$id) return FALSE;
		unset($data['id'], $data['username'], $data['passwd'], $data['email']);
		parent::join($this->gc('addon_table'), '', array('uid' => $id));
		return parent::update($data, array('id'=>$id));
	}
	// Update score
	public function update_credit($uid, $credit=0, $do='', $items=''){
		$credit_data = $this->gc('credit');
		if (!$credit_data) return -2;
		if (!$uid) return -3;
		if (!is_numeric($credit)) $credit = $credit_data[$credit];
		if (!$credit) return -1;
		$today = D::add('d',0,1);
		if (!is_array($uid)){
			$user = parent::find($uid);
		}else{
			$user = $uid;
			$uid = $user['id'];
		}
		if (is_array($credit) AND isset($credit[1])){
			if ($this->db()->count($this->gc('log_table'), array('uid'=>$uid, 'action'=>$do, 'dotime'=>"> {$today}")) >= $credit[1]) return -4;
			$credit = $credit[0];
		}
		unset($credit_data);
		$time = D::get('curtime');
		$maxtoday = $this->us['perm'] ? (int)$this['perm']['maxtoday'] : 0;
		if ($user['updatetime']<$today) $user['todaycredit'] = 0;
		if ($maxtoday>0 AND $user['todaycredit']+$credit>$maxtoday) $credit = $maxtoday-$user['todaycredit'];
		$update = array(
			'credit'=> "[+]{$credit}",
			'experience'=> "[+]{$credit}",
			'todaycredit'=> $user['todaycredit']+$credit,
			'updatetime'=> $time,
		);
		$gid = $this->group->credit_to_gid($user['credit']+$credit);
		if ($gid != $user['groupid']){
			$user['groupid'] = $update['groupid'] = $gid;
			$this->set_cookie($user);
		}
		if (parent::update($update, array('id'=>$uid))){
			$log = array(
				'uid'=>$uid,
				'credit'=>$credit,
				'action'=>$do,
				'items' => $items,
				'dotime' => $time,
			);
			$this->db()->insert($this->gc('log_table'), $log);
		}
		return $gid;
	}
	// Save register
	function add($data){
		// New register
		if ($this->use_uc){
			$uid = call_user_func_array('uc_user_register', $data);
			if ($uid<0) return $uid;
		}
		if (!$this->use_uc OR $uid>0){
			if (!$this->use_uc){
				if ($this->count(array('username'=>$data['username']))>0) return -3;
				if ($this->count(array('email'=>$data['email']))>0) return -6;
			}
			$data['createtime'] = $data['updatetime'] = $data['logintime'] = D::get('curtime');
			$data['createip'] = $data['loginip'] = ip_address();
			$data['nickname'] = $data['username'];
			$data['uncheck'] = (int)$this->gc('check_type');
			$id = $this->insert($data);
			if ($id){
				Db::getInstance()->insert($this->gc('addon_table'), array('aid'=>$id));
				$data['groupid'] = $this->update_credit($id,'register','register', 1, $id);
			}
			$data['sysgid']=0;
			$data['id'] = $id;
		}
		return $data;
	}
	function save_security($data){
		if ($this->use_uc){
			$uc = call_user_func_array('uc_user_edit', $data);
			if ($uc<0) return $uc;
		}
		if (!$this->use_uc OR $uc>0){
			$cond = array('username'=>$data['username']);
			$user = $this->find($cond);
			if (isset($data['oldpasswd'])){
				if (md5($data['oldpasswd']) != $user['passwd']) return -1; //Valid error
				unset($data['oldpasswd']);
			}
			if (empty($data['passwd'])){
				unset($data['passwd']);
			}
			unset($data['id'],$data['repasswd']);
			foreach ($data as $key=>$val){
				if ($val==$user[$key]) unset($data[$key]);
			}
			if (empty($data)) return 0;
			if (isset($data['email']) AND $this->count(array('email'=>$data['email']))>0) return -6;
			if (parent::update($data,"id='$id'")) return 1;
		}
		return 0;
	}
	function set_cookie($user, $expire=0){
		extract($user);
		cookie::set('username',$username, $expire);
		cookie::set('#userauth',"{$id}\t{$email}\t{$groupid}\t{$sysgid}\t{$specgid}\t{$sucityid}", $expire);
		cookie::set('lastlogin',"{$logintime}\t{$loginip}", 0);
	}
	function del_cookie(){
		cookie::del('username');
		cookie::del('userauth');
		cookie::del('lastlogin');
		$this->del_supecookie();
	}
	function supe_cookie($post){
		cookie::set('supeflag', 1);
		//cookie::set('#city', $post['city'],7*86400);
		if ($post['language']) cookie::set("#{$GLOBALS['DIR']}language", $post['language'], 7*86400);
		session::set('supename', $post['username']);
	}
	function del_supecookie(){
		cookie::set('supeflag', NULL);
		cookie::set('city', NULL);
		session::set('supename', NULL);
	}
	public function apply_cond($fields=array(), $post=array()){
		$cond = parent::apply_cond($fields, $post);
		if (isset($get['page'])){
			$this->page($get['page'],
				max(20, (int)$this->conf['pagesize']));
		}
		$this->order($get['sort'], $get['way']);
		if($this->conf['join_addon']) $this->join_addon();
		return $cond;
	}
}

class uevents_model extends model{
	public $conf = array(
		'pagesize'=>20,
		'data_table'=>'userevents',
		'sort_fields' => array(
			'dotime' => array('dotime', 'desc'),
		),
	);
	function add($uid,$data){
		$data['uid']=$uid;
		$data['dotime'] = $in['dotime']=D::get('curtime');
		$in['content'] = $data['content'];
		return $this->insert($data, $in, 1);
	}
	function getme($uid, $get=array()){
		$size = (int)$get['size'];
		if (!$size) $size = (int)$this->conf['pagesize'];
		$limit = (max(1,(int)$get['page'])-1)*$size.','.$size;
		$sql = "SELECT DISTINCT `e`.* FROM `{$this->dbpre}{$this->conf['data_table']}` AS `e` LEFT JOIN `{$this->dbpre}follows` AS `f` ON ( `f`.`mid` = `e`.`mid` AND `f`.`aid` = `e`.`aid` OR `f`.`aid` = `e`.`uid` ) WHERE ( `e`.`uid` != `e`.`fuid` AND (`f`.`uid` = '{$uid}' OR `e`.`fuid` = '{$uid}') ) ORDER BY `e`.`dotime` DESC LIMIT {$limit}";
		return $this->build_data(Db::run($sql));
	}
	function getuid($uid, $get=array()){
		$page = max((int)$get['page'], 1);
		$size = (int)$get['size'];
		if (!$size) $size = 20;
		$data = $this->order('dotime','desc')
			->page($page, $size)
			->findAll(array('uid'=>$uid));
		return $this->build_data($data);
	}
	function build_data($data){
		$rs = array();
		global $lang, $module_model, $city_result;
		foreach ($data as $k=>$one){
			$module = $module_model->get($one['mid']);
			$a = array('module'=>$module['module_name']);
			list($a['username'],$a['action'],$a['subject'],$a['picture'],$a['content']) = explode('#$$#', $one['content']);
			$a['action'] = $lang['useraction']['do_'.$a['action']];
			$a['userlink'] = url('member/'.$one['uid']);
			if ($one['mid']>1){
				$a['link'] = url("{$module['prefix']}/view/{$one['aid']}",'',REAL_WEBROOT.$city_result[$one['sid']]['alias']);
			}else{
				$a['link'] = url('member/'.$one['aid']);
				$a['module'] = '';
			}
			$rs[] = array(
				'subject' => preg_replace('/\#(.[^\}\#]*?)\#/ies', '\$a[\'$1\']', $lang['useraction'][$one['action']]),
				'dotime' => $one['dotime'],
				'timestr'=>D::cdate($one['dotime'],'H:i, d M Y'),
				)+$a;
		}
		return $rs;
	}
}
class follow_model extends model{
	public $conf = array(
		'pagesize'=>20,
		'data_table'=>'follows',
		'sort_fields' => array(
			'ftime' => array('ftime', 'desc'),
		),
	);
	function exists($mid,$aid){
		return $this->count(array('uid'=>$GLOBALS['user_data']['id'],'mid'=>$mid,'aid'=>$aid));
	}
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
	function get($uid){
	}
}
