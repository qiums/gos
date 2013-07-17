<?php  if ( ! defined('ROOT')) exit('No direct script access allowed');
/*
* create by EditPlus v3.01
* author by Sam <wuyou192@163.com>.
*/
$file_types = array(
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
$path = req('path','all');
$filetype = $file_types[$path];
if ('upload'==$ACTION){
	$token = authcode(req('token','p'));
	if(!$token) cprint_exit('faild_token', 'message');
	$token = explode("\t", $token);
	if (''===$token[0]) cprint_exit('no_uid', 'message');
	$name = req('name');
	$in = (array)req('mid,aid,cid');
	$in['uid'] = (int)$token[0];
	$in['createtime']=D::get('curtime');
	$in['description']=substr($name,0,strrpos($name,'.'));
	$filename = req('filename');
	$upload = import('libs.mxupload');
	//$upload->custom_path = $config['upload']['savepath']. $type.DS;
	if (0!==$in['uid']){
		$user_model = import('member');
		$user = $user_model->find($in['uid']);
		$upload->oversize = max(0, intval($user_perm['upload']['space']*1024*1024-$user['attachsize']));
	}
	if ($path) $upload->custom_path = $config['upload']['savepath']. $path.DS;
	if ($filetype AND $filetype['path']){
		$upload->subpath = $filetype['subpath'];
	}
	//$chunk, $chunks, $name
	$rs = $upload->run(req('chunk',0), req('chunks',0),$name);
	if ($filename) df($filename);
	if (!isset($rs['filepath'])) cprint_exit($rs);
	if ($user_model){
		import('app.file')->insert($rs+$in);
		$user_model->setInc('attachsize', array('id'=>$in['uid']), $rs['filesize']);
		/*$user_model->update_events($uid, 'upload', req('mid',0), req('aid',0));*/
	}
	if ($in['mid']*$in['aid']>0){
		Db::update('arcdata', array('pictures'=>array('update',1)), array('mid'=>$in['mid'],'aid'=>$in['aid']));
	}
	cprint_exit($rs);
}elseif ('get'==$ACTION){
	$id = (int)req('id');
	$mid = (int)req('mid',3);
	$get = req('mid,page,size,thumb,cid');
	$cond = array_intersect_key($get, array('mid'=>'','cid'=>''));
	if(0===$id){
		$cond['uid'] = $user_data['id'];
		if (3==$mid) $cond['aid'] = 0;
	}else{
		$cond['aid'] = $id;
	}
	if($filetype) $cond['filetype'] = explode(',', $filetype['extensions']);
	$file_model = import('app.file');
	if($get['thumb'])
		$file_model->property('thumb', $get['thumb']);
	$loop = $file_model->page(max(1, (int)$get['page']),
		isset($get['size'])?(int)$get['size']:20)
		->order('id','desc')
		->callback()->findAll($cond);
	$pagedata = $file_model->get_page();
}elseif ('browse'==$ACTION){
	if (!$user_data['id']) cprint_exit('login_timeout');
	$path = req('path');
	$mid = req('mid', 0);
	$uid = (int)req('uid', $user_data['id']);
	$token = authcode($uid."\t".D::get('curtime'),'ENCODE');
	/*加载语言包*/
	$lang_replace = array(
		'max-size'=>$upload_options['max_file_size'],
		'all-space'=>$user['allsize_text'],
		'url'=>url('dir=member&app=file'),
	);
	$lang_plupload = mxlang('lang_plupload');
	$template_file = 'browse';
}elseif ('download'==$ACTION){
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
