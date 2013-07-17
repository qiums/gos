<?php if ( ! defined('SYSPATH')) exit('No direct script access allowed');

class com_comment_controller extends controller {
	public function qreply(){
		$mc = $this->module->get($this->gp('mid'));
		$this->assign('conf', $this->cp->comment->config);
		$this->assign('mc', $mc);
		$this->view('reply');
	}
	public function tree(){
		$cond = array('published' => 1) + $this->gp('mid,aid');
		$data = $this->cp->comment
				->page((int)$this->gp('page'), $this->gp('size'))
				->treelist($cond);
		$this->qdata['pagedata'] = $this->cp->comment->pagedata;
		$this->assign('loopdata', $data);
		$this->view('tree');
	}
}
/*$app_model = import('app.comment');
if (!$app_model) cprint_exit('mod_notfound');
$app_conf=$app_model->conf;
if ('save'==$ACTION){
	$uid = $user_data['id'];
	$p = array_map(create_function('$a', 'return is_scalar($a) ? trim($a) : $a;'), $_POST);
	$p['depth'] = 1;
	if ($p['pid']>0){
		$parent = $app_model->find("id='{$p['pid']}'");
		if ($app_conf['reply_depth']>0 AND count(explode(',',$parent['node']))>=$app_conf['reply_depth']) cprint_exit('noallow_reply');
	}
	$id = $app_model->insert($p, $parent);
	unset($parent);
	if (!is_int($id)) return cprint_exit($id);
	$arc_conf = $module_model->get($p['mid']);
	$arc_model = import('archives',$arc_conf);
	$rs = $arc_model->callback()->find($p['aid']);
	Db::update('arcdata', array('comments'=>array('update',1)), array('mid'=>$rs['mid'],'aid'=>$rs['id']));
	if ($uid){
		// Update Member score & event
		$user = import('member');
		$user->update_credit($uid,'comment','add',$p['mid'],$p['aid']);
		$do = 'comment';
		$e = array(
			'sid'=>$rs['sid'],
			'mid'=>$rs['mid'],
			'aid'=>$p['aid'],
			'fuid'=>$rs['uid'],
			'action'=>$do,
			'content'=>join('#$$#', array($user_data['name'],$do,$rs['fulltitle'],$rs['coverpic'],csubstr($p['context'],255))),
		);
		$user->uevents()->add($uid,$e);
		if($rs['uid']!=$uid) $user->pm()->send($rs['uid'],array('uid'=>$uid),$rs,'comment');
	}
	cprint_exit($id,'ok');//$data = $forum->limit(1)->treelist(array('id'=>$id));
}elseif ('getone'==$ACTION){
	$id = req('id');
	$cond = array('published'=>1, 'id'=>$id);
	$data = $app_model->getone($cond);
	$template_file = 'tree';
}elseif ('tree'==$ACTION){
	$get = req('size,page,mid,aid');
	$cond = array('published'=>1)+array_intersect_key($get, array('mid'=>'','aid'=>''));
	$loopdata = $app_model->page(max(1,(int)$get['page']),$get['size'])->treelist($cond);
	$pagedata = $app_model->get_page();
	$template_file = 'tree';
}elseif ('qreply'==$ACTION){
	$aid = req('id');
	if (!$aid){
		$mid = req('mid');
		$aid = req('aid');
	}
	$template_file = 'reply';
}*/