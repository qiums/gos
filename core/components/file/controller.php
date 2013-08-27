<?php  if ( ! defined('ROOT')) exit('No direct script access allowed');
/*
* create by EditPlus v3.01
* author by Sam <wuyou192@163.com>.
*/
class com_file_controller extends common_controller{
	public $file_types = array(
		'media' => array('title'=>'Media Files', 'extensions'=>'flv,mp3,mp4,swf'),
		'image' => array('title'=>'Image Files', 'extensions'=>'jpg,jpeg,gif,png'),
		'album' => array('title'=>'Image Files', 'extensions'=>'jpg,jpeg,gif,png','mid'=>3),
		'events' => array('title'=>'Image Files', 'extensions'=>'jpg,jpeg,gif,png','mid'=>5),
		'venue' => array('title'=>'Image Files', 'extensions'=>'jpg,jpeg,gif,png','mid'=>4),
		'file'=> array('title'=>'Documents Files', 'extensions'=>'zip,rar,pdf,doc,docx'),
		'ads'=> array('title'=>'Allowed Files', 'extensions'=>'jpg,jpeg,gif,png,swf'),
		'docs' => array('title'=>'Allowed Files', 'extensions'=>'jpg,jpeg,gif,png,zip,rar,pdf'),
		'all' => array('title'=>'Allowed Files', 'extensions'=>'flv,mp3,mp4,swf,zip,rar,pdf'),
	);

	public function browse(){
		if (!$this->vars['user_data']) return $this->output(0, 'need_login');
	}
	public function get(){
		$search = array(
			'mid' => array('search'=>'1'),
			'aid' => array('search'=>'1'),
			'cid' => array('search'=>'1'),
		);
		$cond = $this->cp->file->apply_cond($search);
		if (!$cond['aid']){
			$cond['uid'] = $this->vars['user_data']['id'];
			$cond['mid'] = 2;
			unset($cond['aid']);
		}
		$data = $this->cp->file
			->page($this->gp('page'), $this->gp('limit'))
			->order($this->gp('order'), $this->gp('way','desc'))
			->callback()->where($cond)->findAll();
		$this->qdata['pagedata'] = $this->cp->file->pagedata;
		if ($_ENV['ajaxreq']) return $this->output(1, '', array('data'=>$data, 'page'=>$this->qdata['pagedata']));
		$this->assign('arrdata', $data);
	}
	public function upload(){
		$hash = $this->cp->file->hash($this->gp('hash'));
		if (!$hash) return $this->output(0, 'need login');
		$oldname = $this->gp('name');
		$in = (array)$this->gp('mid,aid,cid');
		$in['uid'] = (int)$hash[0];
		$in['createtime'] = D::get('curtime');
		$in['description'] = qstrstr($oldname, '.', TRUE);
		$path = $this->gp('path');
		$this->load->libs('upload');
		if (0<$in['uid']){
			$user = $this->user->where('id', $in['uid'])->find();
			$this->upload->oversize = max(0, intval($this->vars['user_perm']['upload']['space']*1024*1024-$user['attachsize']));
		}
		if ($path) $this->upload->custom_path = gc('upload.savepath'). $path.DS;
		//$chunk, $chunks, $name
		$res = $this->upload->run($this->post['chunk'], $this->post['chunks'], $oldname);
		if ($this->post['oldfile'] AND function_exists('df')) df($this->post['oldfile']);
		if (!isset($res['filepath'])) return $this->output(0, '', $res);
		if ($in['uid']>0){
			$this->cp->file->insert($res + $in);
			$this->user->where('id',$in['uid'])->setInc('attachsize', $res['filesize']);
		}
		if ($in['mid']*$in['aid']>0){
			$this->cp->file->db()->where(array('mid'=>$in['mid'],'aid'=>$in['aid']))->update('arcindex', array('pictures'=>'[+]1'));
		}
		return $this->output(1, '', $res);
	}
	public function download(){
		$get = req('id,aid');
		if ($get['id']>0){
			$data = import('app.file')->find($get['id']);
		}elseif ($get['aid']>0){
			$module_conf = import('module')->get(7);
			$cond = array('aid'=>$get['aid']);
			$data = Db::find($module_conf['addon_table'], $cond);
			$cname = 'down_file_'.$data['aid'];
			if (!cookie::get($cname)){
				Db::update($module_conf['addon_table'], array('downs'=>array('update',1)), $cond);
				cookie::set($cname, 1);
			}
		}
		import('util.http');
		http::download(filepath($data['filepath']));
		exit;
	}
}