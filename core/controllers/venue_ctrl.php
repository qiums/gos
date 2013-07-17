<?php if ( ! defined('SYSPATH')) exit('No direct script access allowed');

class venue_controller extends controller{
	function _init_data(){
		$this->mc = $this->module->get('venue');
		if (!$this->mc) show_error('Not found module');
		$this->archives = $this->mc;
		$this->category->block(array('mid'=>$this->mc['id']));
		$this->assign(array(
			'mid' => $this->mc['id'],
			'mc' => $this->mc
				));
		if ($this->gp('cid')){
			$this->_parse_category($this->gp('cid'));
		}
	}
	// Archives module Home
	function index(){
		if ($this->vars['lc']){
			$lc = $this->vars['lc'];
			if ($lc['redirect']) redirect(url($lc['redirect']));
			if ($lc['tpltype']){
				$ac = "_qc{$lc['tpltype']}";
				return $this->$ac();
			}
		}
		$mc = $this->mc;
		$this->assign(array(
			'seokeywords' => $mc['keywords'],
			'seodesc' => $mc['description'],
			'pagetitle' => $mc['module_name'],
			'sc' => $this->category->root(),
			))
			->view(array("{$mc[prefix]}_index", 'archives_index'));
	}
	// Archives category Home
	function _qcindex(){
		$mc = $this->mc;
		$lc = $this->vars['lc'];
		$this->assign(array(
			'seokeywords' => $lc['keywords']. ','. $mc['keywords'],
			'seodesc' => $lc['description']. $mc['description'],
			'pagetitle' => trim(join(' / ', $this->category->title($lc['id'])). " / {$mc['module_name']}",'/ '),
			'sc' => $this->category->child($lc['id']),
			))
			->view(array(
				"{$mc[prefix]}_{$lc[alias]}",
				"{$mc[prefix]}_home",
				'archives_home'
			));
	}
	// Archives category list
	function _qclist(){
		$mc = $this->mc;
		$lc = $this->vars['lc'];
		$position = $this->category->position('|MODULE|/[fullalias]', $lc['id'], 1);
		$this->qdata['cid'] = $lc['fullnode'];
		$fields = $this->module->search_fields($mc['id']);
		$cond = $this->archives->apply_cond($fields);
		$data = $this->archives
			->page((int)$this->gp('page'), $this->gp('size'))
			->order($this->gp('sort'), $this->gp('way', 'DESC'))
			->callback()->findAll($cond);
		$this->qdata['pagedata'] = $this->archives->pagedata;
		$this->assign(array(
			'seokeywords' => $lc['keywords']. ','. $mc['keywords'],
			'seodesc' => $lc['description']. $mc['description'],
			'pagetitle' => trim(join(' / ', $this->category->title($lc['id'])). " / {$mc['module_name']}",'/ '),
			'position' => $position,
			'loopdata' => $data,
			))
			->view(array(
				"{$mc[prefix]}_{$lc[alias]}",
				"{$mc[prefix]}_{$this->vars['rc'][alias]}",
				"{$mc[prefix]}_list",
				'archives_list'
			));
	}
	// Archives detail
	function detail(){
		if (!($id = $this->gp('id'))) show_error('Undefined archive Id');
		$this->archives->content()->apply_cond();
		$data = $this->archives
			->callback()
            ->content()
			->find($id);
		if(!$data) show_error('Not found archive detail');
		if(!$this->qdata['user']['supe'] AND !$data['published']) show_error('The archive is unpublished now');
		if($data['extlink']) redirect($data['extlink']);
		$mc = $this->mc;
		/*if ($data['cid']){
			$this->_parse_category(get_from_string($data['cid'],',','end'));
			$lc = $this->vars['lc'];
		}*/
		if($data['catedata']){
			$position = $this->category->position('|MODULE|/[fullalias]', $data['catedata']['id'], 1);
			$this->assign('lc', $data['catedata']);
			$this->assign('rc', $this->category->get($data['catedata']['rootid']));
			unset($data['catedata']);
		}
		$data['content'] = htmlspecialchars($data['content']);
		if ($this->mc['enable_ubb']){
			$method = $_ENV['ajaxreq'] ? 'clear' : 'replace';//dump($this->ubb);die;
			$data['content'] = $this->ubb->$method($data['content']);
		}else{
			$data['content'] = nl2br($data['content']);
		}
		//$this->_update_views();
		$this->assign(array(
			'seokeywords' => trim($data['keywords']. ','. $this->vars['lc']['keywords']. ','. $this->mc['keywords'], ','),
			'seodesc' => $data['description']. $this->mc['description'],
			'pagetitle' => trim(join(' / ', $this->category->title($this->vars['lc']['id'])). " / {$this->mc['module_name']}",'/ '),
			'position' => $position,
			'data' => $data,
			))
			->view(array(
				"{$this->mc[prefix]}_detail",
				'archives_detail'
			));
	}
}