<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
class group_model extends model{
	public $config = array(
		'data_table'=>'usergroups',
	);
	private $cache;
	function get($id=0, $type=-1, $key='*'){
		$grp = $this->cache_group();
		if ($id){
			if (is_scalar($id) AND FALSE!==strpos($id,',')) $id = explode(',', $id);
			if (is_array($id)){
				$rs = array();
				foreach ($id as $tid){
					if (!$tid) continue;
					$rs[$tid] = self::get($tid, $type, $key);
				}
				return $rs;
			}
			if (!isset($grp[$id])) return array();
			if ('*' !== $key AND isset($grp[$id][$key])) return $grp[$id][$key];
			return $grp[$id];
		}
		if ($type != -1){
			foreach ($grp as $k=>$value){
				if ($value['type']!=$type) unset($grp[$k]);
			}
		}
		return $grp;
	}
	function permission($groupid, $sysgid=0, $specgid=0){
		if (is_array($groupid)) extract($groupid);
		$perm = $this->get(array($groupid, $sysgid, $specgid), -1, 'permissions');
		$first = array_shift($perm);
		foreach ($perm as $pm){
			$first = array_merge($first, $pm);
		}
		return $first;
	}
	function cache_group(){
		if ($this->cache) return $this->cache;
		$cachekey = 'user_groups';
		if (FALSE === ($group = cache::q($cachekey))){
			$data = $this->attr('pk','id')
				->order(array('type'=>'asc','minscore'=>'asc'))
				->findAll();
			$group = array();
			foreach ($data as $k=>$one){
				if($one['permissions']) $one['permissions'] = parse_ini_string($one['permissions'],TRUE);
				$group[$one['id']] = $one;
			}
			unset($data);
			cache::q($cachekey, $group);
		}
		$this->cache = $group;
		return $group;
	}
	function save_group($data){
		$id = $this->find('','MAX(id)|mxid');//Db::find($this->conf['group_table'],'','MAX(id)|mxid');
		if (!$id){
			$id = 0;
		}elseif (is_array($id)){
			$id = (int)$id['mxid'];
		}
		if (is_array($data['add'])){
			foreach ($data['add'] as $type=>$group){
				$insert = array('type'=>$type);
				foreach ($group['groupname'] as $key=>$name){
					if (empty($name)) continue;
					$id++;
					$insert['id'] = $id;
					$insert['groupname'] = $name;
					isset($group['minscore'][$key]) AND $insert['minscore'] = (int)$group['minscore'][$key];
					isset($group['maxscore'][$key]) AND $insert['maxscore'] = (int)$group['maxscore'][$key];
					$this->insert($insert);
				}
			}
		}
		if (is_array($data['update'])){
			foreach ($data['update'] as $id=>$group){
				$this->update($group, "id='$id'");
			}
		}
		$this->cache_group();
		return TRUE;
	}
	// Get groupid by score
	function credit_to_gid($score=0){
		$grp = $this->cache_group();
		foreach ($grp as $id=>$g){
			if ($g['type']!=0) continue;
			if ($score>=$g['minscore'] AND $score<$g['maxscore']) return $id;
		}
		return -1;
	}
	function supemenu(&$menu, $perm, $r=FALSE){
		if ($perm['modules']){
			foreach ($menu as $key=>$row){
				if (FALSE===cstrpos($perm['modules'],$key)){
					unset($menu[$key]);
				}
			}
			if ($r) return ;
		}
		if (isset($menu['archives']) AND (!$perm['modules'] OR FALSE!==cstrpos($perm['modules'],'archives'))){
			$arc = $cat = array();
			$channels = $this->channel->get();
			$perm_channel = $perm['archives']['module'];
			foreach ($channels as $key=>$row){
				if (!$perm['admin']['supe']){
					if (!$perm_channel OR ('*'!==$perm_channel AND FALSE===cstrpos($perm_channel,$row['prefix']))) continue;
				}
				$arc['archives/'.$row['prefix']] = $row['channel_name'];
			}
			$menu['archives'][1] = $arc;
			unset($arc, $cat);
		}
		if (isset($menu['cp']) AND (!$perm['modules'] OR FALSE!==cstrpos($perm['modules'],'components'))){
			$com = $this->cp->manage->get_coms();
			foreach ($com as $key=>$row){
				if ($perm['app'] && FALSE===cstrpos($perm['app'], $key)) continue;
				$menu['cp'][1]["cp/{$key}"] = $row['app_name'];
			}
		}
		return $menu;
	}
}