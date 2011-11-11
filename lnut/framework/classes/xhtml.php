<?php

class xhtml extends text {

    public function __construct ($file='') {
        $this->content = '';

        // backward compatible
        if($file!='') {
            $this->load_from_file(config::get('application').'templates/'.$file.'.xhtml');
        }
    }

    public function load ($file='',$content=false) {
        if(!$content) {
            if(file_exists($this->path)) {
                $this->content = file_get_contents($this->path);
            } else {
                $this->content = '';
            }
        } else if(strlen($file) > 0) {
            $this->content = $file;
        } else {
            $this->content = '';
        }
        return $this;
    }

    public function load_from_string ($xhtml='') {
        if(strlen($xhtml) > 0) {
            $this->content = $xhtml;
        }
    }

    public function load_from_file ($path='') {
        $this->path = $path;
        if(file_exists($this->path)) {
            $this->content = file_get_contents($this->path);
        }
    }
}

?>