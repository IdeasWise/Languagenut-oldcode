<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

/**
 * Description of component_template
 *
 * @author Ritesh
 */
class component_template {
	//put your code here
	public static function generate ($template_type_uid = '') {
		$template_type	= new template_type($template_type_uid);
		$template_type->load();
		$template		= new template();
		$templates		= array();
		$template_html          = array();
		$templates		= $template->get_templates($template_type_uid);
		$options		= array(); // for the select options

		// list all template files
		foreach($templates as $uid2=>$templateData) {
			$template_panel	 =   new xhtml('body.admin.templates.file');
			$template_panel->load();
			$template_panel->assign(
				$templateData
			);
			$template_html[] = $template_panel->get_content();
			$options[$templateData['uid']]  =   $templateData['name'];
		}
		return array($template_html,$template_type->get_uid(),$template_type->get_name(),$options);
	}

	public static function generate_templateType () {
		$template_type              =   new template_type();                 
		$template_types             =   array ();
		$template_types             =   $template_type->get_template_types();
		$page_rows                  =   array();

		if(!empty($template_types)) {
			// list of all template types
			foreach($template_types as $uid=>$data) {
				$template_list_panel	=   new xhtml('body.admin.templates.list');
				$template_list_panel->load();

				// call component method to generate template files
				list($template_html, $template_type_uid, $template_type_name,$options) = component_template::generate($data['uid']);

				$template_list_panel->assign(
						array(
						"template.uid" => $template_type_uid,
						'template.name' => $template_type_name,
						"template.select" => format::to_select(array("name" => "copy_template_".$template_type_uid,"options_only" => false),$options),
						"template.files" => @implode("",$template_html)
					)
				);
				$page_rows[] = $template_list_panel->get_content();
			}
		}
		return $page_rows;
	}
}
?>
