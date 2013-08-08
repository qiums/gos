<?php  if ( ! defined('ROOT')) exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
return array(
    'supe_menu' => array(
        'home' => array(
            '首页',
        ),
        'setting' => array(
			'系统',
			array(
				'setting' => '全局设置',
				'channel' => '频道管理',
				'category' => '栏目管理',
			)
		),
		'archives' => array('内容'),
		'cp' => array('应用',array()),
        'user' => array(
			'用户',
			array(
				'user' => '用户列表',
				'user/group' => '用户组',
				'user/admin' => '管理员',
			),
        ),
    ),
	'side_menu' => array(
		'common_operations'=>array(
			'常用功能',
			array(
				'archives/add'=>array('添加内容'),
				'archives/recycle'=>array('内容回收站'),
				'category'=>array('分类管理'),
				'setting'=>array('全局设置'),
				'channel'=>array('频道管理'),
			),
		),
	),
	'insert_item'=>array('getimage'=>'相册/图片','getfile'=>'附件','getmedia'=>'音频/视频','getpoll'=>'投票'),
	'wait_check_docs'=>'等待审核的文档',
	'app_list'=>'应用列表',
	'my_home'=>'我的主页',
	'click_view' => '点击查看',
	'statistics_info'=>'统计信息',
    'server_info' => '服务器信息',
    'server_ipaddr' => '服务器名称及IP地址',
    'phpos_mode' => '系统及PHP版本',
    'server_time' => '服务器时间',
    'database_size' => '数据库大小',
    'sqlserver_version' => 'MySQL版本',
    'upload_permission' => '上传权限及大小',
    'not_allow_upload' => '不允许上传',
    'remote_url_access' => '远程地址访问许可',
    'web_server' => 'WEB服务器',
    'module_text' => '模块',
	'moveto_newcid'=>'请为选中的记录选择新分类:',
	'channel_field_type'=>array(
		'input:text' => '文本字段',
		'input:file' => '文件',
		'input:number' => '整型数字',
		'input:decimal' => '货币数字',
		'input:date' => '日期格式',
		'input:checkbox' => '复选框',
		'input:radio' => '单选框',
		'textarea' => '文本域(大型文本)',
		'html' => 'HTML编辑器',
		'select' => '单选列表',
		'select:multi' => '多选列表',
		'select:union' => '无限级联列表',
	),
	'button' => array(
		'add_category' => '新栏目',
		'newcid'=>'新分类',
		'edit_category'=>'修改栏目',
		'new_module' => '新模块',
		'add_group' => '增加组',
		'new_member' => '新用户',
		'set_coverpic'=>'设置封面图片',
		'updateuser'=>'更新用户信息',
		'field' => '字段'
	),
	'form_label' => array(
		'choose_group' => '选择用户组',
		'usergroup' => '用户组',
		'groupname' => '组名称',
		'reginfo' => '注册时间及IP',
		'lastinfo' => '登录时间及IP',
		'minscore' => '积分下限',
		'maxscore' => '积分上限',
		'clickpv'=>'点击/浏览',
		'prefix'=>'唯一标识',
		'data_table'=>'主数据表',
		'addon_table' => '附加表',
		'category_table'=>'分类表',
		'totalrows'=>'数据数量',
		'channel_title'=>'频道名称',
		'channel_name'=>'频道名称',
		'append_title'=>'附加标题',
		'seo_keyword' => '关键字',
		'seo_description' => '描述',
		'category_table' => '分类数据表',
		'data_table' => '主数据表',
		'addon_table' => '附加表',
		'data_pk' => '主键字段',
		'access_level' => '访问等级',
		'group_table' => '用户组表',
		'scorelog_table' => '积分记录表',
		'event_table' => '用户事件表',
		'max_login_fail' => '允许最大登录错误次数',
		'other_setting' => '其它设置',
		'data_fields' => '模块字段',
		'sort_fields' => '排序字段',
		'channel_copy' => '复制频道',
		'channel_edit' => '修改频道',
		'channel_add' => '添加频道',
		'channel_index' => '频道列表',
		'channel_field' => '字段管理',
		'channel_field_add' => '添加字段',
		'category_index' => '栏目列表',
		'position'=>'位置',
		'pages'=>'页面',
		'mid'=>'频道',
		'width_height'=>'宽度/高度',
		'options'=>'设置选项',
		'sucityid'=>'分站点权限',
		'adtype'=>'类型',
		'label' => '字段文字',
		'field_name' => '字段标识',
		'type' => '类型',
		'value' => '值',
		'function' => '回调函数',
		'sort' => '排序数字',
		'option' => '选项',
		'attr' => '属性',
		'arcsupe' => '操作选项',
		'tid' => '信息类型',
	),
	'form_alt'=>array(
		'set_begindate'=>'请设置开始时间',
		'set_enddate'=>'请设置结束时间',
	),
	'placeholder'=>array(
		'catename' => '填写分类名称',
	),
	'form_source'=>array(
		'position'=>array(
			'top'=>'页首',
			'slide'=>'幻灯',
			'middle'=>'页中',
			'left'=>'页左',
			'right'=>'页右',
			'bottom'=>'页底',
		),
		'pages' => array(
			'main' => '网站首页',
			'index' => '分类首页',
			'list' => '列表页',
			'detail' => '内容页',
		),
		'adtype'=>array('标准','弹出','背景','对联','双图'),
	),
	'form_group' => array(
		'common' => '基础信息',
		'extend' => '扩展信息',
		'other' => '其它信息',
		'catetxt' => '栏目介绍',
		'picpanel' => '图片管理',
	),
	'supe_option'=>array('remove'=>'批量删除','move'=>'移动到..','attrib'=>'设置属性','published'=>'改变状态','sortby'=>'保存排序数字'),
	'grouptype' => array('用户组','系统组'),
	'before_choose_module'=>'请先从左边选择模块。',
	'addimage'=>'上传图片','addmedia'=>'上传视频',
	'addfile'=>'上传文件','addpoll'=>'添加投票',
	'myalbum'=>'我的相册','mymedia'=>'我的视频','myfile'=>'我的文件',
	'default_album'=>'默认相册','listing_album'=>'Listing相册','events_album'=>'Events相册',
	'arcpost'=>array(
		'addimage'=>'上传图片','addmedia'=>'上传视频',
		'addfile'=>'上传文件','addpoll'=>'添加投票',
		'myalbum'=>'我的相册','mymedia'=>'我的视频','myfile'=>'我的文件',
		'default_album'=>'默认相册','listing_album'=>'Listing相册',
		'remoteimage'=>'远程图片','remotemedia'=>'网络视频',
		'insertdp'=>'同时插入描述','continue_upload'=>'继续上传',
		'insert_button_myalbum'=>'插入图片','insert_button_mymedia'=>'插入视频',
		'insert_button_myfile'=>'插入附件','insert_button_mypoll'=>'插入投票',
		'upload_success'=>'恭喜，文件上传成功。',
		'all_upload_success'=>'恭喜，所有队列的文件都成功上传。',
		'stopupload_text'=>'由于以下原因，上传过程被中止。',
		'insert_tip'=>'待插入的图片在这里显示之后，再点击[插入图片]。',
		'detect_title'=>'检测名称/标题','google_map_marker'=>'在谷歌地图中标记',
		'txt'=>array(
			'dgetcover'=>array('点击[继续]将图片设置为封面图片。','进入[我的相册]来选中图片以[设置封面图片]。'),
			'dgetpic'=>array('点击[继续]直接插入图片。','进入[我的相册]来选中图片以[插入图片]。'),
			'dgetmedia'=>array('点击[继续]直接插入视频。','进入[我的视频]中选中视频以[插入视频]。'),
			'dgetfile'=>array('点击[继续]直接插入文件。','进入[我的文件]中选择以[插入文件]。'),
		),
	),
);
?>
