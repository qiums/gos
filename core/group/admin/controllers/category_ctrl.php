<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
class category_controller extends common_controller{

	public function index(){
		$group = array('common', 'extend', 'catetxt');
		$form = array();
		foreach ($group as $k=>$one){
			$form[$k] = $this->form->group($k)->render();
		}
		$this->assign('form_group', $group);
		$this->assign('form', $form);
		$this->view('category');
	}
	public function edit(){
		$id = $this->qdata['id'];
		if ($id){
			$data = $this->channel->find(array('id'=>$id));
			$this->vars['tabletit'] .= " / {$data['channel_name']}";
			if ('copy' === gc('env.action')){
				unset($data['prefix'], $data['channel_name']);
			}
			$this->assign('data', $data);
		}
		$this->assign('form', $this->form->render('', $data));
		$this->assign('channels', $this->channel->get());
		$this->view('channel');
	}
	public function save(){
		$id = $this->post['id'];
		$uptxt = (int)$this->post['uptxt'];
		unset($this->post['id'], $this->post['uptxt']);
		if (!$this->form->validate()){
			exit($this->form->error());
		}
		$ac = $id ? 'edit' : 'add';
		unset($this->post['coverpic']);
		$this->post['alias'] = replace_alias($this->post['catename']);
		if (!$id){
			$pid = (int)$this->post['pid'];
			if (!$pid){
				$this->post['depth'] = '1';
			}else{
				$parent = $this->category->get($pid);
				$this->post['rootid'] = $parent['rootid'];
				$this->post['node'] = $parent['node']. ','. $pid;
				$this->post['depth'] = $parent['depth']+1;
			}
			$id = $this->category->insert($this->post);
			if (!$pid){
				$this->category->where('id', $id)->update(array('rootid'=>$id, 'node'=>$id));
			}else{
				$this->category->where('id', $pid)->setInc('childs');
			}
		}else{
			$this->category->where('id', $id)->update($this->post);
		}
		if ('add' === $ac OR $uptxt){
			$this->category->db()->insert('contents', array('mid'=>'0', 'aid'=>'0', 'cid'=>$id), array('content'=>$this->post['sd_content']));
		}
		$this->category->find(TRUE);
		return $this->output(1, 'supe_success', $this->category->get($id));
	}
	public function move(){
		
	}
	public function upload(){
		$id = $this->post['id'];
		if (!$id) return $this->output(1);
		$res = $this->upload->run($this->post['chunk'], $this->post['chunks'], $this->post['name']);
		if (isset ( $res ['fileurl'] )){
			if ($this->post['oldfile']){
				$res['del'] = del_file($this->post['oldfile']);
			}
			if (!isset($res['del']) OR $res['del']>0){
				$this->category->where('id', $id)->update(array('coverpic'=>$res['filepath']));
				$this->category->find(TRUE);
			}
		}
		return $this->output(1, 'upload_complete', $res);
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