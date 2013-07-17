<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
class category_controller extends common_controller{
	function __construct(){
		parent::__construct();
	}
	public function data(){
		if (!$this->post['mid']) return $this->output(0, 'not_request_data');
		$id = $this->post['id'];
		$this->category->block($this->post);
		$data = $this->category->fields("id,pid,node,catename,childs,alias")->find();
		foreach ($data as $key=>$one){
			if ($one['redirect']) unset($data[$key]);
		}
		$data = qcsort($data);
		return $this->output(1, '', $data);
	}
	public function json(){
		if (!$this->post['mid']) return $this->output(0, 'not_request_data');
		$ac = $this->gp('ac');
		if ($ac AND method_exists($this->category, $ac)){
			$this->category->block($this->post)->property('fields', "id,pid,node,catename,childs,alias");
			return $this->output(1, '', $this->category->$ac($this->gp('id', 0)));
		}
		return $this->output(0, 'method_not_exists');
	}
}