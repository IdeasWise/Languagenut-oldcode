<?php

class component_file_upload {

    public static function generate_file_upload($file = "") {
        if($file['size'] > 0) {

            $theme_uid          =   config::getSetting("current_theme_uid");
            $theme              =   new theme($theme_uid);
            $theme_folder       =   "/";
            if($theme->get_valid()) {
                $theme->load();
                $theme_folder   =   $theme->get_folder_name()."/";
            }

            $image_default      =   md5($file['name']).strrchr($file['name'], '.');
            $image_path         =   config::get('site').'images/'.$theme_folder.$image_default;
            $move               =   move_uploaded_file($file['tmp_name'],$image_path);
            if(!$move || !is_readable($image_path)) {
                return false;
            }
            else {
                return $image_default;
            }
        }
    }

    public static function generate_non_access_file_upload($file = "") {
        $folder_name        =   md5(time());
        $image_path         =   config::get("uploads").$folder_name;
        if(!is_dir($image_path)) {
            if (!mkdir($image_path, 0, true)) {
                die('Failed to create folders...');
            }
        }
        if(!is_writable($image_path)) {
            chmod($image_path , 0755);
        }
        $image_default      =   md5($file['name']).strrchr($file['name'], '.');
        $move               =   move_uploaded_file($file['tmp_name'],$image_path."/".$image_default);
        if(!$move || !is_readable($image_path."/".$image_default)) {
            return array(false,false);
        }
        else {
            return array($image_path,$image_default);
        }
    }
}
?>
