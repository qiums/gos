<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
/*
* create by EditPlus v3.01
* author by Sam <wuyou192@163.com>.
*/
class channel_model extends model{
	private $cache_data = array();

	public function __construct(){
		parent::__construce();
		$this->config['data_table'] = 'channels';
		$this->config['sort_fields'] = 'id,asc';
		return parent::model();
	}
	public function get($channel_name=''){
		$key = 'channels.data';
		$channel = $this->cache_data[$key];
		if (TRUE === $channel_name OR !$channel){
			if (TRUE === $channel_name OR FALSE===($channel = cache::q($key))){
				$data = $this->findAll();
				$channel = array();
				foreach ($data as $one){
					if (!empty($one['other_setting']))
						$one = array_merge($one, parse_ini_string($one['other_setting'],TRUE));
					unset($one['other_setting']);
					$channel[$one['prefix']] = $one;
				}
				cache::q($key, $channel);
				unset($data);
			}
			$this->cache_data['channels.data'] = $channel;
		}
		if (!$channel) return array();
		if ($channel_name){
			if (is_numeric($channel_name)){
				foreach ($channel as $one)
					if ($one['id']==$channel_name) return $one;
			}
			if (isset($channel[$channel_name])) return $channel[$channel_name];
			return array();
		}
		return $channel;
	}
	public function sget(){
		$data = $this->get();
		$res = array();
		foreach ($data as $one){
			$res[$one['id']] = $one['channel_name'];
		}
		return $res;
	}
	public function cache_fields($mid){
		if (!$mid) return array();
		$cache_key = 'channels.field'.$mid;
		if (FALSE===($fields = cache::q($cache_key))){
			$data = $this->db()
				->where('mid', $mid)
				->order('sort', 'desc')
				->findAll('fields');
			$fields = array();
			foreach ($data as $one){
				if (!empty($one['settings']))
					$one = array_merge($one, parse_ini_string($one['settings'],TRUE));
				unset($one['settings']);
				$fields[$one['field_name']] = $one;
			}
			cache::q($cache_key, $fields);
		}
		return $fields;
	}
	/*
	 * $mode - 0 获取自定义字段 1 获取搜索字段 2 获取列表字段 3 添加/修改内容时的字段(含channel.common的字段)
	 * 4 1+3的组合 5 2+3的组合 大于5相当于0+3的组合
	 */
	public function get_fields($mid, $mode=0, $group=NULL){
		$fields = $this->cache_fields($mid);
		if (3<=$mode){
			$common_fields = $this->cache_data['common.fields'];
			if (!$common_fields){
				if (FALSE === ($common_fields = import('data.form.channel_common'))) return $fields;
				$this->cache_data['common.fields'] = $common_fields;
			}//dump($common_fields);dump($fields);
			$fields = $common_fields + (array)$fields;
		}
		if (!$fields) return array();
		$mode -= 3;
		$filter = array(NULL, 'search', 'islist');
		if ($filter[$mode]){
			foreach ($fields as $key=>&$one){
				if (!$one[$filter[$mode]]){
					unset($fields[$key]);
					continue;
				}
				if ('search'===$filter[$mode]){
					$one['attr'] = preg_replace('/(req-\w+|minlength\d+)/is', '', $one['attr']);
					/*unset($fields[$key]);
					$fields[basename(str_replace('.', '/', $key))] = $one;*/
				}
			}
		}
		if (!is_null($group)){
			foreach ($fields as $key=>$one){
				if (intval($group) !== intval($one['group'])) unset($fields[$key]);
			}
		}
		return $fields;
	}
	public function get_search($mid){
		$fields = is_numeric($mid) ? $this->get_fields($mid, 4) : $mid;
		if (!$fields) return array();
		$mode = array();
		foreach ($fields as $key=>$one){
			$search = FALSE!==strpos($one['search'], '|') ? '|' : (FALSE!==strpos($one['search'], '&') ? '&' : NULL);
			if (!$search) continue;
			$name = basename(str_replace('.', '/', $key));
			$tmp = explode($search, $one['search']);
			$mode[$tmp[0]]['option'][$tmp[1]][] = lang("form_label.{$name}");
			unset($fields[$key]);
		}
		if (!$mode) return $fields;
		foreach ($mode as $key => $one) {
			if (1<count($one['option'])) $fields["f{$key}"] = array('type' => 'select', 'option' => $one['option']);
			$fields[$key] = array('type' => 'input:text');
		}
		return $fields;
	}
}