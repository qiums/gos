<?php  if ( ! defined('ROOT')) exit('No direct script access allowed');
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
return array(
    'supe_menu' => array(
        'dir=admin' => array(
            'Home',
        ),
        'mod=setting' => array('Settings'),
		'mod=archives' => array('Archives'),
    	'mod=category' => array(
			'Categories',
			array(
				'mod=category' => 'Categories',
				'mod=group' => 'Navigation',
			),
		),
		'app=manage' => array('Apps',array()),
        'mod=member' => array(
			'Users',
			array(
				'mod=member' => 'User List',
				'mod=group' => 'Groups',
			),
        ),
    ),
	'side_menu' => array(
		'common_operations'=>array(
			'Other Stuff',
			array(
				'mod=archives&ac=check'=>array('To be confirmed','(<strong>0</strong>)'),
				'mod=archives&ac=recycle'=>array('Trash'),
				'mod=category'=>array('Categories'),
				'mod=setting'=>array('Settings'),
				'mod=module'=>array('Modules'),
			),
		),
	),
	'insert_item'=>array('getimage'=>'Album/Pictures','getfile'=>'File','getmedia'=>'Audio/Video','getpoll'=>'Poll'),
	'wait_check_docs'=>'Docs to be confirmed',
	'app_list'=>'Apps',
	'my_home'=>'My Home',
	'click_view' => 'View',
	'statistics_info'=>'Stats',
    'server_info' => 'Server info',
    'server_ipaddr' => 'Server IP',
    'phpos_mode' => 'System',
    'server_time' => 'Server Time',
    'database_size' => 'Database Size',
    'sqlserver_version' => 'MySQL Version',
    'upload_perimission' => 'Upload perimission',
    'not_allow_upload' => 'Disable',
    'remote_url_access' => 'Remote Access',
    'web_server' => 'WEB Server',
    'module_text' => 'Module',
	'moveto_newcid'=>'Choose new categories:',
	'button' => array(
		'add_category' => 'New Category',
		'newcid'=>'New Category',
		'edit_category'=>'Edit Category',
		'new_module' => 'New Module',
		'add_group' => 'Add Group',
		'new_member' => 'New User',
		'updateuser'=>'Update',
		'set_coverpic'=>'Set Cover picture',
	),
	'text' => array(
		'choose_group' => 'Choose Group',
		'usergroup' => 'Group',
		'groupname' => 'Group name',
		'reginfo' => 'Register Time&amp;IP',
		'lastinfo' => 'Login Time&amp;IP',
		'minscore' => 'Lower limit',
		'maxscore' => 'Upper limit',
		'clickpv'=>'View',
		'prefix'=>'Prefix',
		'data_table'=>'Data table',
		'addon_table' => 'Addon table',
		'category_table'=>'Category table',
		'totalrows'=>'Quantity',
		'module_name'=>'Module name',
		'append_title'=>'Append title',
		'seo_keyword' => 'Keywords',
		'seo_description' => 'Description',
		'data_pk' => 'PK',
		'access_level' => 'Access level',
		'group_table' => 'Group table',
		'scorelog_table' => 'Credit log table',
		'event_table' => 'User event table',
		'max_login_fail' => '允许最大登录错误次数',
		'other_setting' => 'Other setting',
		'data_fields' => 'Data fields',
		'sort_fields' => 'Sort fields'
	),
	'form_alt'=>array(
		'set_begindate'=>'Start Time',
		'set_enddate'=>'End Time',
	),
	'form_label'=>array(
		'position'=>'Position',
		'pages'=>'Pages',
		'modules'=>'Modules',
		'width_height'=>'Width/Height',
		'options'=>'Options',
		'sucityid'=>'Perimission',
	),
	'form_source'=>array(
		'position'=>array(
			'top'=>'Top',
			'slide'=>'Slide',
			'middle'=>'Middle',
			'left'=>'Left',
			'right'=>'Right',
			'bottom'=>'Bottom',
		),
		'pages' => array(
			'main' => 'Web Home',
			'index' => 'Category Home',
			'list' => 'List',
			'detail' => 'Detail',
		),
	),
	'grouptype' => array('Group','System Group'),
	'before_choose_module'=>'Choose module first',
);
?>
