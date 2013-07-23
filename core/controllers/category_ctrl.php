<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
class category_controller extends common_controller{
	function __construct(){
		parent::__construct();
	}
	public function data(){
		$data = $this->category
				->fields("id,mid,pid,node,catename,childs,alias")
				->find($this->post);
		foreach ($data as $key=>$one){
			if ($one['redirect']) unset($data[$key]);
		}
		//$data = qcsort($data);
		return $this->output(1, '', qcsort($data));
	}
	public function json(){
		$do = $this->gp('do');
		if ($do AND method_exists($this->category, $do)){
			$this->category->fields("id,mid,pid,node,catename,childs,alias");
			$data = $this->category->$do($this->gp('id', 0));
			return $this->output(1, '', 'array'===$this->gp('res') ? array_values($data) : $data);
		}
		return $this->output(0, 'method_not_exists');
	}
	public function get(){
		if (!($id = $this->gp('id'))) return $this->output(0, 'not_request_data');
		return $this->output(1, '', $this->category->get($id));
	}
	public function content(){
		$id = $this->gp('id');
		if (!$id) return $this->output(0, 'not_request_data');
		$data = $this->category->db()
			->where(array('mid'=>'0', 'aid'=>'0', 'cid'=>$id))
			->attr('fields', 'content')
			->find('contents');
		if ($data['content']){
			$data['content'] = $this->ubb->replace(htmlspecialchars($data['content']));
		}
		if ($_ENV['ajaxreq'] AND 'html'===$_ENV['datatype']) exit($data['content']);
	}
}