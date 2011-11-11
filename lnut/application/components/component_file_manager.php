<?php

class component_file_manager {

    public static function generate_store_file($file = "") {

        $filetype           =   $file['type'];
        $filesize           =   $file['size'];

        $filetitle          =   $file['name'];
        $filetitlearray     =   explode(".",$file['name']);
        if($filetitlearray > 2) {
            for($i=0;$i<count($filetitlearray)-1;$i++) {
                $filetitle = ".".$filetitlearray[$i];
            }
            $filetitle      =   trim($filetitle,".");
        }
        else {
            $filetitle      = $filetitlearray[0];
        }

        list($filepath,$filename)   =   component_file_upload::generate_non_access_file_upload($file);
        if($filename != false) {
            $theme_file             = new theme_file();
            $theme_file->set_title($filetitle);
            $theme_file->set_filename($filename);
            $theme_file->set_filepath($filepath);
            $theme_file->set_filetype($filetype);
            $theme_file->set_filesize($filesize);

            $fileid         =   $theme_file->insert_file();
            return $fileid;
        }
        return false;
    }
    public static function generate_update_file_output($file_uid = 0) {

        $skeleton = new xhtml ('xml.file_get_data');
        $skeleton->load();
        //$skeleton->assign(array('error'         => "1"));
        if(is_numeric($file_uid) && $file_uid > 0) {
            $theme_file         = new theme_file($file_uid);
            if($theme_file->get_valid()) {
                $theme_file->load();
                $skeleton->assign(
                        array(
                        'error'         => "0",
                        'uid'           => $theme_file->get_uid(),
                        'filename'      => $theme_file->get_filename(),
                        'filepath'      => $theme_file->get_filepath(),
                        'filetype'      => $theme_file->get_filetype(),
                        'filesize'      => $theme_file->get_filesize(),
                        'description'   => $theme_file->get_description(),
                        'alt'           => $theme_file->get_alt(),
                        'dimensions'    => $theme_file->get_dimensions(),
                        'title'         => $theme_file->get_title()
                        )
                );
            }
            else {
                $skeleton->assign(array('error' => "1"));
            }
        }
        else {
            $skeleton->assign(array('error' => "1"));
        }
        output::as_xml($skeleton,true);
    }

    public static function move_file(theme_file $theme_file,$file_path = '') {
        $filename = $theme_file->get_filename();
        $filepath = $theme_file->get_filepath();

        $copyfilepath = config::get("site")."images";
        if($file_path != "") {
            $copyfilepath .= $file_path;
            if(!is_dir($copyfilepath)) {
                if (!mkdir($copyfilepath, 0, true)) {
                    die('Failed to create folders...');
                }
            }
        }
        $source         =   $filepath."/".$filename;
        $destination    =   $copyfilepath."/".$filename;
        if(!file_exists($destination)) {
            $copy       =   copy($source,$destination);
        }
        if($copy) {
            unlink($source);
            if(is_dir($filepath) && (strpos($filepath,config::get("uploads")) !== false)) {
                rmdir($filepath);
            }
        }
        return $copyfilepath;
    }

    public static function generate_delete_file_manager($file_uid = "") {
        $status = 0;
        if(is_numeric($file_uid) && $file_uid > 0) {
            $theme_file         = new theme_file($file_uid);
            if($theme_file->get_valid()) {
                $theme_file->load();
                $filepath       =   $theme_file->get_filepath();
                $filename       =   $theme_file->get_filename();
                if(file_exists($filepath.$filename)) {
                    unlink($filepath.$filename);
                }
                $theme_file->delete();
                $status = 1;
            }
        }
        $skeleton = new xhtml ('xml.file_update_delete');
        $skeleton->load();
        $skeleton->assign(
                array(
                'file_uid'  => $file_uid,
                'status'    => $status
                )
        );
        output::as_xml($skeleton,true);
    }
}
?>