<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/*
* create by EditPlus v3.01
* author by Sam <wuyou192@163.com>.
*/
//$app_conf = include dirname(__FILE__). DS. 'conf.php';
class com_tree_controller extends controller {
	private $tid;

	public function __call($name, $args){
		$this->tid = $name;
		$this->get_data();
	}
	public function json(){
		$ac = $this->gp('ac');
		$tid = $this->gp('tid');
		unset($this->qdata['tid'], $this->qdata['ac']);//dump($this->qdata);die;
		if (!$tid) return $this->output(0, 'not_define_tid');
		if (method_exists($this->cp->tree, $ac)){
			$this->cp->tree->property('tid', $tid)->property('fields', "id,pid,node,dataname,childs,alias");
			return $this->output(1, '', call_user_func_array(array($this->cp->tree, $ac), $this->qdata));
		}
	}
	private function get_data(){
		$this->load->helper('extend');
		$loop = $this->cp->tree->data($this->tid);//dump($loop);return ;
		return $this->output(1, '', QCS($loop));
	}
}
