<?php  if ( ! defined('ROOT')) exit('No direct script access allowed');
/*
* create by EditPlus v3.01
* author by Sam <wuyou192@163.com>.
*/
$filetype = array(
	'media' => array('title'=>'Media Files', 'extensions'=>'flv,mp3,mp4,swf'),
	'image' => array('title'=>'Image Files', 'extensions'=>'jpg,jpeg,gif,png'),
	'album' => array('title'=>'Image Files', 'extensions'=>'jpg,jpeg,gif,png'),
	'events' => array('title'=>'Image Files', 'extensions'=>'jpg,jpeg,gif,png'),
	'venue' => array('title'=>'Image Files', 'extensions'=>'jpg,jpeg,gif,png','path'=>'listings','subpath'=>'[Y][m]'. DS),
	'file'=> array('title'=>'Documents Files', 'extensions'=>'zip,rar,pdf,doc,docx'),
	'ads'=> array('title'=>'Allowed Files', 'extensions'=>'jpg,jpeg,gif,png,swf','path'=>'ads'),
	'docs' => array('title'=>'Allowed Files', 'extensions'=>'jpg,jpeg,gif,png,zip,rar,pdf'),
	'all' => array('title'=>'Allowed Files', 'extensions'=>'flv,mp3,mp4,swf,zip,rar,pdf'),
);
$type = req('type','all');
$filetype = $filetype[$type];
if ('manage'==$ACTION OR 'config'==$ACTION){
	if (!$user_data['id']) cprint_exit('login_timeout');
	$uid = (int)req('uid', $user_data['id']);
	$mid = req('mid', 0);
	$upload_options = array(
		'filters'=>array($filetype),
		'url'=>url('dir=&app=file&ac=upload&ajax=1'),
		'chunk_size'=>'1mb',
		'multipart_params'=>array(
			'type'=>$type,
			'file_types'=>$filetype['extensions'],
			'token'=>authcode($uid."\t".D::get('curtime'),'ENCODE'),
			'mid'=>$mid,
		),
	);
	if($filetype['path']) $upload_options['multipart_params']['path'] = $filetype['path'];
	if (0!==$uid){
		$user = import('member')->find($uid);
		$space = $user_perm['upload']['space'];
		$size = $user_perm['upload']['size'];
		if (!$size) $size = min((int)ini_get('upload_max_filesize'),(int)ini_get('post_max_size'));
		if($space=='-1'){
			$user['allsize_text'] = $lang['text']['nolimit'];
		}else{
			$user['allsize_text'] = formatSize($space*1024*1024);
		}
		$upload_options += array(
			'max_file_size'=>formatSize($size*1024*1024),
			'over_size'=>(-1==$space)?-1:intval($space*1024*1024-$user['attachsize']),
			'use_size'=>intval($user['attachsize']),
		);
	}
	/*加载语言包*/
	$lang_replace = array(
		'max-size'=>$upload_options['max_file_size'],
		'all-space'=>$user['allsize_text'],
		'url'=>url('dir=member&app=file'),
	);
	$lang_plupload = mxlang('lang_plupload', array(
		'Select files'=>array(
			'file-type'=>' ('.$filetype['extensions'].')',
		),
		'Add files to the upload queue and click the start button.'=> $lang_replace,
		));
	$data = array('options'=>$upload_options, 'lang'=>$lang_plupload);
	$template_file=$ACTION;
}elseif ('upload'==$ACTION){
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
	$path = req('path');
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
	$get = req('mid,page,size,thumb,cid,type');
	$cond = array_intersect_key($get, array('mid'=>'','cid'=>''));
	if(0===$id){
		$cond['uid'] = $user_data['id'];
		$cond['aid'] = 0;
	}elseif (-1===$id){
		$cond['uid'] = $user_data['id'];
	}else{
		$cond['aid'] = $id;
	}
	if($get['type'] AND $filetype) $cond['filetype'] = explode(',', $filetype['extensions']);
	$file_model = import('app.file');
	if($get['thumb'])
		$file_model->property('thumb', $get['thumb']);
	$loop = $file_model->page(max(1, (int)$get['page']),
		isset($get['size'])?(int)$get['size']:20)
		->order('id','desc')
		->callback()->findAll($cond);
	$pagedata = $file_model->get_page();
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
