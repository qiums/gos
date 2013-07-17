<?php if ( ! defined('ROOT')) exit('No direct script access allowed');
return array(
    'app_name' => 'Polls',
    'data_table' => 'polls',
	'access_level'=>'1,7,13',
	'data_pk'=>'id',
	'option_limit'=>10,
	'default_published'=>1,
	/*'formele'=>array(
		//'cids' => array('eletype'=>'choose','attr'=>'catename','url'=>url('category/article'),'title'=>Lang('form>choose_category')),
		'subject'=>array('attr'=>'class/txt','eletype'=>'input','type'=>'text','exp'=>$lang['exp']['subject']),
		'coverpic'=>array('attr'=>'class/txt','eletype'=>'input','type'=>'text'),
		'description'=>array('attr'=>'class/txt/rows/2','eletype'=>'textarea'),
		'bedate'=>array(
			'eletype'=>'input',
			'type'=>'text',
			'label'=>'Start &amp; End Time',
			'sub'=>array(
				'bdate'=>array('name'=>'bdate','attr'=>'class/shtxt/empty','exp'=>'datetime||Please input correct datetime'),
				'edate'=>array('name'=>'edate','attr'=>'class/shtxt/empty','exp'=>'datetime||Please input correct datetime'),
			),
		),
		//'polltype'=>array('eletype'=>'select','dval'='radio','option'=>array('radio'=>'Single','checkbox'=>'Multi')),
		'maxcheck'=>array('label'=>'Select limit','eletype'=>'input','type'=>'text','dval'=>1),
	),*/
);
?>
