<?php  if ( ! defined('ROOT')) exit('No direct script access allowed');
/*
* create by EditPlus v3.01
* author by Sam <wuyou192@163.com>.
*/
class com_ads_controller extends controller{
	public function admin_index(){
		$head_title = $lang['supe_menu']['app=manage'][0].' / '. $app_conf['app_name'];
		$lib_form = import('libs.form');
		$form_data = $app_conf['form_data'];
		$filter_data = $lib_form->get_fields($form_data, TRUE);
		$get = req('word,datatype,sort,way,page');
		$cond = $app_model->apply_cond($filter_data, $get);
		$loop = $app_model->order($get['sort'],$get['way'])
			->page(max(1, (int)$get['page']))
			->callback()->findAll($cond);
		$first_sort = $app_model->first_sort;
		$pagedata = $app_model->get_page();
		$filter = $lib_form->run($filter_data, $get+$cond, 0);
		$form = $lib_form->run($form_data, 1);
		//$template_file = 'ads_index';
	}
	public function get(){
		$data = $this->cp->ads->filter('all',$this->gp('pages','all'),$this->gp('mid','all'),$this->gp('cid','all'),'subject,clickurl,fileurl,position');
		return $this->output(1,'',$data);
	}
	public function click(){
		$id = (int)$this->gp('id');
		$ad = $this->cp->ads->get($id);
		if (!$ad) return $this->output(0, 'no_data');
		$cname = 'click_ads_'.$id;
		if (!cookie::get($cname)){
			//$app_model->insert_click($id);
			$this->cp->ads->where('id', $id)->setInc('clicks');
			cookie::set($cname, 1);
		}/**/
		response::header(301);
		header('Location: '. $ad['linkurl']);
	}
	public function visit(){
		$id = (int)req('id');
		if (!$id) cprint_exit('no_id');
		$lib_form = import('libs.form');
		$data = $app_model->find($id);
		$data = $lib_form->run($app_conf['form_data'], $data, 1);
	}
	public function update(){
		submitcheck('formhash');
		$do = req('do');
		$ids = req('ids');
		$updata = req('updata');
		if ('delete'==$do){
			if (!$ids) cprint_exit('no_data');
			$rs = $app_model->delete(array('id'=>$ids));
		}else{
			if (!$updata) cprint_exit('no_data');
			foreach ($updata as $id=>$up){
				if(isset($up['published'])){
					$up['publock'] = (int)(!$up['published']);
				}
				$app_model->update($up, array('id'=>$id));
			}
		}
		$app_model->cache(TRUE);
		cprint_exit('update_success','ok');
	}
	public function save(){
		$id = (int)req('id','p');
		$lib_form = import('libs.form');
		$form_data = $app_conf['form_data'];
		$post = $lib_form->verification($_POST, $form_data);
		$post['id'] = $id;
		$post['sid'] = (int)$city_data['id'];
		unset($form_data, $lib_form);
		if (0<($id = $app_model->save($post))){
			cprint_exit($lang['update_success'],'ok');
		}
		cprint_exit($lang['update_error']);
	}
}
