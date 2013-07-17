<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
class home_controller extends common_controller{
	function __construct(){
		parent::__construct();
	}
	function index(){
		//return dump($this->category->block("list", "do/get/mid/3/id/0/use/recommend/1"));
		$this->view('home');
	}
    function history(){
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
                $tmp = array_merge($tmp, $this->archives->callback()->findAll(array('id' => $id)));
            }
            foreach ($history as $index => $one) {
                if ($index >= 8)
                    break;
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
        }//dump($_ENV);
        $this->output($loop);
    }
	function captcha(){
		$this->widget->captcha;
		//cookie::set('captcha', $code);
		exit;
	}
	function _export(){
		$page = max(1, (int)$this->gp('page'));
		$size = 50;
		$this->load->model('user');
		$this->user->conf = array('data_table'=>'users');
		$data = $this->user->page($page, $size)->findAll();
		if (!$data) exit('Finish!');
		$in = array();
		$db = Db::getInstance('uc');
		foreach ($data as $one){
			$salt = substr(uniqid(rand()), -6);
			$in = array('username' => $one['username'],
				'password' => md5($one['passwd']. $salt),
				'email' => $one['email'],
				'regip' => $one['createip'],
				'regdate' => $one['createtime'],
				'lastloginip' => ip2long($one['loginip']),
				'lastlogintime' => $one['logintime'],
				'salt' => $salt,
			);
			$id = $db->insert('members', $in);
			if ($id>0) $db->insert('memberfields', array('uid'=>$id));
		}
		// home/export/page/2
		echo 'Success in page '. $page;
		sleep(2);
		//echo '<script type="text/javascript">window.location.href = "'.(gc('env.webpath'). 'home/export/page/'. ($page+1)).'";</script>';
	}
	function update(){
		$db = Db::getInstance();
		$data = $db->findAll('venues');
	}
}