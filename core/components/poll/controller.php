<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/*
* create by EditPlus v3.01
* author by Sam <wuyou192@163.com>.
*/
class app_poll_class extends Base{
	function _admin_index(){
		//$perm = $this->GUSER['perm'];
		//$cond['nojoin'] = 1;
		//$cond['cids'] = $get['cids'];
		$data = Model('app.poll')->page($this->_vars('page','',1), 20)->findAll($get);
		$this->_assign('loopdata', $data);
		$this->_view('list');
	}
	function _member_index(){
		$data = Model('app.poll')->page($this->_vars('page','',1), 20)->findAll($get);
		$this->_assign('loopdata', $data);
		$this->_view('memberlist');
	}
	function supe(){
		$id=(int)key($this->segments);
		$poll=Model('app.poll');
		$data = $id>0?$poll->find(array('id'=>$id)):array();
		!empty($data['bdate']) AND $data['bdate'] = D::cdate($data['bdate'],'Y-m-d H:i:s');
		!empty($data['edate']) AND $data['edate'] = D::cdate($data['edate'],'Y-m-d H:i:s');
		$this->_assign(Libs('icform')->run($poll->conf['formele'],$data));
		$this->_assign('data',$data);
		$this->_view('supe');
	}
	function view($id=0){
		if (!$id) $id = key($this->segments);
		$data = Model('app.poll')->find(array('id'=>$id));
		$this->_view('layout',array('data'=>$data));
	}
	function _post_manage(){
		$id = $_POST['id'];
		if ($id AND !is_array($id)) $id = explode(',',$id);
		unset($_POST['id']);
		Model('app.poll')->update($_POST, array('id'=>$id));
		putdata('Y');
	}
	function _post_update(){
		if (!is_null(cookie::get('poll_'.$_POST['pollid']))) return putdata('S');//$this->view($_POST['pollid']);
		$model=Model('app.poll');
		$model->setInc('result',array('id'=>$_POST['option']),1);
		$count = is_array($_POST['option'])?count($_POST['option']):1;
		$model->setInc('result',array('id'=>$_POST['pollid']),$count);
		cookie::set('poll_'.$_POST['pollid'],1);
		return putdata('Y');
	}
	function _post_save(){
		$update=$_POST['update'];
		$add=$_POST['add'];
		$id=(int)$_POST['id'];
		unset($_POST['add'],$_POST['update'],$_POST['id']);
		$poll=Model('app.poll');
		$pollrs=0;
		if ($add){
			foreach ($add as $key=>$one){
				$subject = trim($one['subject']);
				if (empty($subject)){
					unset($add[$key]);
					continue;
				}
				$pollrs+=$one['result'];
			}
			if ($add) $_POST['pollnum'] = count($add);
		}
		if ($_POST['bdate']) $_POST['bdate'] = D::totime($_POST['bdate']);
		if ($_POST['edate']) $_POST['edate'] = D::totime($_POST['edate']);
		if ($id){
			if ($update){
				$upids=array();
				foreach ($update as $optid=>$one){
					if (isset($one['subject'])){
						$one['subject'] = trim($one['subject']);
						if (empty($one['subject'])){
							unset($update[$optid]);
							continue;
						}
					}
					if (isset($one['result'])) $upids[]=$optid;
					$pollrs += (int)$one['result'];
					$poll->update($one,"id='$optid'");
				}
				$poll->fields='SUM(result)|result';
				$rs=$poll->find(array('id'=>$ids));
				$pollrs-=$rs['result'];
				$_POST['result']=array('update',$rs['result']);
			}
			if ($add) $_POST['pollnum']=array('update',count($add));//dump($_POST);exit;
			$_POST['polltype']=(int)$_POST['maxcheck']===1?'radio':'checkbox';
			$poll->update($_POST,"id='$id'");
		}else{
			$_POST['uid']=$this->GUSER['id'];
			$_POST['published']=$poll->conf['default_published'];
			$_POST['result']=$pollrs;
			$_POST['createtime']=D::get('curtime');
			$_POST['polltype']=(int)$_POST['maxcheck']===1?'radio':'checkbox';
			$id=$poll->insert($_POST);
		}
		if ($add){
			foreach ($add as $one){
				$one['pollid'] = $id;
				$one['uid'] = $this->GUSER['id'];
				$poll->insert($one);
			}
		}
		if (INAJAX) return putdata(array('id'=>$id));
		//if ($_POST['event']=='apply') return redirect('app/poll/supe/'.$id);
		redirect('app/poll');
	}
}
?>