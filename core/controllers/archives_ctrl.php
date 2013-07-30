<?php if ( ! defined('ROOT')) exit('No direct script access allowed');

class archives_controller extends common_controller{
	private $mc;

	function _init_data(){
		if ('detail' !== gc('env.action') AND !$this->gp('cid')) return $this->output(0, 'Undefined category Id');
		$cid = $this->gp('cid');
		if (!$cid) return $this->output(0, 'Undefined category alias');
		if (FALSE !== strpos($cid, '/page/')){
			preg_match('/\/page\/(\d+)/is', $cid, $m);
			$cid = preg_replace('/\/page\/\d+/is', '', $cid);
			$this->qdata['page'] = $m[1];
		}
		if (FALSE !== strpos($cid, '/tid/')){
			preg_match('/\/tid\/([\w\W]+)/is', $cid, $m);
			$this->qdata['tid'] = $m[1];
			$cid = preg_replace('/\/tid\/[\w\W]+/is', '', $cid);
		}
		$lc = $this->category->get($cid);
		if (!$lc) return $this->output(0, 'Undefined category Id');
		if ($lc['redirect']) redirect(url($lc['redirect']));
		$this->mc = $this->channel->get($lc['mid']);
		if (!$this->mc) return $this->output(0, 'Not found channel');
		gc('env.mid', $this->mc['prefix'], TRUE);
		if (!$this->qdata['tid'] AND $lc['typeid']) $this->qdata['tid'] = $lc['typeid'];
		if ($this->qdata['tid'] AND !is_numeric($this->qdata['tid'])){
			$types = $this->cp->tree->property('tid', $this->mc['prefix'])->get($this->qdata['tid'], 'alias');
			$this->qdata['tid'] = $types['id'];
		}
		$data = $this->category->db()
				->where(array('mid'=>'0', 'aid'=>'0', 'cid'=>$lc['id']))
				->attr('fields', 'content')
				->find('contents');
		if ($data['content']){
			$data['content'] = $this->ubb->replace(htmlspecialchars($data['content']));
		}
		/*if (isset($lc["typeid"])){
			$types = $this->cp->tree->property('tid', $this->mc['prefix'])->
		}*/
		$this->archives = $this->mc;
		$this->assign(array(
			'mid' => $this->mc['id'],
			'mc' => $this->mc,
			'rc' => $lc['id']==$lc['rootid'] ? $lc : $this->category->get($lc['rootid']),
			'lc' => $lc + (array)$data,
			'seokeywords' => $lc['keywords']. ','. $this->mc['keywords'],
			'seodesc' => $lc['description']. $this->mc['description'],
			'pagetit' => join('-', $this->category->title($lc['id'])),
		));
	}
	// Archives category Home
	function index(){
		$lc = $this->vars['lc'];
		if ($lc['tpltype'] !== 'index'){
			$ac = "_qc{$lc['tpltype']}";
			return $this->$ac();
		}
		$this->assign(array(
			'seokeywords' => $lc['keywords'],
			'seodesc' => $lc['description'],
			))
			->view(array("{$this->mc[prefix]}_index", 'archives_index'));
	}
	// Archives category list
	function _qclist(){
		//return $this->archives->block("list", "mid/venue/tid/118/limit/5");
		$mc = $this->mc;
		$lc = $this->vars['lc'];
		$position = $this->category->position('[fullalias]', $lc['id'], 1);
		$this->qdata['cid'] = $lc['id'];
		// 搜索字段
		$fields = $this->channel->get_fields($mc['id'], 4);
		$list = array_keys($this->channel->get_fields($mc['id'], 5));
		$cond = $this->archives->apply_cond($fields);
		$data = $this->archives->attr('fields', join(',', $list))
			->page($this->gp('page'), $this->gp('limit', $lc['pagesize']))->order()
			->callback()->where($cond)->findAll();
		$this->qdata['pagedata'] = $this->archives->pagedata;
		$this->assign(array(
			'position' => $position,
			'arrdata' => $data,
			))
			->view(array(
				"{$mc[prefix]}_{$lc[alias]}",
				"{$mc[prefix]}_{$this->vars['rc'][alias]}",
				"{$mc[prefix]}_list",
				'archives_list'
			));
	}
	// Archives single page
	public function _qcpage(){
		//$data = $this->category->db()->where(array('cid'=>$this->vars['lc']['id']))->find('contents');
		//if (!$data) return $this->output(0, 'Not found category content.');
		$this->view(array(
				"page_{$this->vars['lc'][alias]}",
				"page_{$this->vars['rc'][alias]}",
				'archives_page'
			));
	}
	// Archives detail
	function detail(){
		if (!($id = $this->gp('id'))) return $this->output(0, 'Undefined archive Id');
		$this->archives->apply_cond();
		$data = $this->archives->join('contents.aid', 'content','mid')->where('id', $id)->find();
		if(!$data) return $this->output(0, 'Not found archive detail');
		if(!$this->vars['user_perm']['admin'] AND !$data['published']) show_error('The archive is unpublished now');
		if($data['extlink']) redirect($data['extlink']);
		$mc = $this->mc;
		if($data['catedata']){
			$position = $this->category->position($mc['prefix'].'/[fullalias]', $data['catedata']['id'], 1);
			$this->assign('lc', $data['catedata']);
			$this->assign('rc', $this->category->get($data['catedata']['rootid']));
			unset($data['catedata']);
		}
		$data['content'] = htmlspecialchars($data['content']);
		if ($mc['enable_ubb']){
			$method = $_ENV['ajaxreq'] ? 'clear' : 'replace';
			$data = array_merge($data, $this->ubb->page($data['content'], $this->gp('page', 1)));
			$data['content'] = $this->ubb->$method($data['content']);
		}else{
			$data['content'] = nl2br($data['content']);
		}
		if ($data['pagetotal']){
			$this->qdata['pagedata'] = array('cur'=>$this->gp('page', 1), 'ptotal'=>$data['pagetotal']);
			unset($data['pagetotal']);
		}
		//$this->_update_views();
		$this->assign(array(
			'seokeywords' => trim($data['keywords']. ','. $this->vars['lc']['keywords']. ','. $mc['keywords'], ','),
			'seodesc' => $data['description']. $mc['description'],
			'pagetit' => $data['fulltitle']. "-{$mc['module_name']}",
			'position' => $position,
			'data' => $data,
			))
			->view(array(
				"{$mc[prefix]}_detail",
				'archives_detail'
			));
	}
}
