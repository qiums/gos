<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
class follow_controller extends common_controller{

	public function _init_data(){
		if (!$this->uid) redirect('user/login');
		$this->load->model('user/follow');
	}
	public function add(){
		if (!$this->follow->add($this->tuid, $this->post))
				return $this->output(0, 'not_request_data');
		$this->follow->db()->update('arcindex', array('follows'=>'[+]1'), $this->post);
		$this->output(1);
	}
	public function status(){
		$this->post['uid'] = $this->tuid;
		$this->output(1, '', array('count' => $this->follow->where($this->post)->count()));
	}
	public function index(){
		$this->archives = $this->channel->get('3');
		$cond['follows.uid'] = $this->tuid;
		$cond['follows.mid'] = 3;
		$data = $this->archives
				->order('follows.ftime', 'desc')
				->page($this->gp('page'), $this->gp('limit'))
				->attr('jointype', 'inner')
				->join('arcindex.aid', '*', 'mid')
				->join('follows.aid', NULL)
				->where($cond)
				->callback()
				->findAll();
		/*$follows = $this->follow->order()
			->where(array('uid'=>$this->uid, 'mid'=>3))
			->page($this->gp('page'), $this->gp('limit'))
			->findAll();
		if ($_ENV['ajaxreq'] AND !$follows) return $this->output(0);
		$this->qdata['pagedata'] = $this->follow->pagedata;
		$ids = array();
		foreach ($follows as $one){
			$ids[] = $one['aid'];
		}
		$this->archives = $this->channel->get('3');
		$this->archives->where('id', $ids)->callback();
		if ('sample'!==$this->gp('style')){
			$this->attr('pk', 'aid')
				->apply_cond();
		}
		$data = $this->archives->findAll();*/
		if ($_ENV['ajaxreq']) return $this->output(1, '', array('data'=>$data, 'page'=>$this->archives->pagedata));
	}
}
