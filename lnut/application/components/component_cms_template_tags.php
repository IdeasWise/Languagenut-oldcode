<?php

class component_cms_template_tags {

    // create input object
    public static function generate_input($tag_uid = 0,$tag_value = "") {
        $str    =   '<input type="text" name="template-input-tag-'.$tag_uid.'" id="template-input-tag-'.$tag_uid.'" value="'.$tag_value.'" class="cms-template-input" />';
        return $str;
    }

    // create file object
    public static function generate_file($tag_uid = 0,$tag_value = "") {

        $theme_uid          =   config::getSetting("current_theme_uid");
        $theme              =   new theme($theme_uid);
        $theme_folder       =   "/";
        if($theme->get_valid()) {
            $theme->load();
            $theme_folder   =   $theme->get_folder_name()."/";
        }

        $str    =   '<input type="file" name="template-file-tag-'.$tag_uid.'" id="template-file-tag-'.$tag_uid.'" class="cms-template-file" />';
        if($tag_value != "") {
            $str .= '<a href="'.config::images($theme_folder).$tag_value.'" target="_blank">View</a>';
        }
        $str    .=  '<input type="hidden" name="template-input-tag-'.$tag_uid.'" id="template-input-tag-'.$tag_uid.'" value="'.$tag_value.'" class="cms-template-input" />';
        $str    .=  '<ol class="files"></ol>';
        return $str;
    }

    // create submit object
    public static function generate_submit() {
        $str    =   '<input type="button" class="cms-template-tags-submit" value="Save" name="cms-template-tags-submit" id="cms-template-tags-submit" />';
        return $str;
    }
}
?>