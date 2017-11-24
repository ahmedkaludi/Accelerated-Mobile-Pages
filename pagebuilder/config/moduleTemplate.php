<?php
$moduleTemplate = array();
$dir = AMP_PAGE_BUILDER.'/modules/';
if (is_dir($dir)) {
    if ($dh = opendir($dir)) {

        while (($file = readdir($dh)) !== false) {
        	if(is_file($dir.$file)){
        		$moduleTemplate[str_replace(".php", "", $file)] = include $dir.$file;
        	}
        }
        closedir($dh);
        $moduleTemplate = array_filter($moduleTemplate);
    }
}

//Row Contents
$output = '<div class="amp_pb_module {{row_class}}">';
$outputEnd = '<div class="cb"></div> </div>';
$containerCommonSettings = array(
			'label' =>'Row Settings',
			'default_tab'=> 'customizer',
			'tabs' => array(
				'customizer'=>'Customizer',
				'container_css'=>'Container css'
				),
			'fields' => array(
							array(
								'type'		=>'text',
								'name'		=>"row_label",
								'label'		=>'Row label',
								'tab'		=>'customizer',
								'default'	=>'',
								),
					
							array(
								'type'		=>'text',
								'name'		=>"row_class",
								'tab'		=>'container_css',
								'label'		=>'Row class',
								'default'	=>'',
								)
							),
			'front_template_start'=>$output,
			'front_template_end'=>$outputEnd
			);
