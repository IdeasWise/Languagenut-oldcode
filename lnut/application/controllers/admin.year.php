<?php

class admin_year extends Controller {

    private $token	= 'list';

    private $arrTokens	= array (
            'list',
            'edit',
            'add',
            'delete',
            'deletetranslation'            
            
    );
  
   private $parts = array();
	
    public function __construct () {
        parent::__construct();
	$this->parts = config::get('paths');          
        if(isset($this->parts[2]) && in_array($this->parts[2], $this->arrTokens)) {
             $this->token =  $this->parts[2];
        }
        
         if(in_array($this->token,$this->arrTokens)) {
		$method = 'do' . ucfirst($this->token); 
		$this->$method();
            }
    
        
        
    }

    
  
  
    protected function doAdd()
    {
        $skeleton   = new xhtml ('skeleton.admin');
        $skeleton->load();

        $body       = new xhtml('body.admin.year.add');
        $body->load();

        

        $arrBody = array();

        $arrBody['title'] = 'Add Language';
        $arrBody['btnval'] = 'Add';
        if( isset($_POST['form_submit_button'])){
            $language       = new years();
            if( $language->doSave() ){
                 $language->redirectTo('admin/year/list'); // redirect to invoice list if all does well;
            }
            else{
                $body->assign( $language->arrForm );
            }
        }
        
    
        
        $body->assign( $arrBody );
        
        $skeleton->assign (
                    array (
                    'body' => $body
                    )
            );
            output::as_html($skeleton,true);
    }

    protected function doEdit()
    {       

         $skeleton   = new xhtml ('skeleton.admin');
        $skeleton->load();

        $body       = new xhtml('body.admin.year.edit');
        $body->load();

        $arrBody = array();
        $arrBody['uid'] =  @$this->parts[3];
        
        $arrBody['title'] = 'Update Language';
        $arrBody['btnval'] = 'Update';
        if( isset($_POST['form_submit_button'])){
            $language       = new years();
            if( $language->doSave() ){
                 $language->redirectTo('admin/year/list'); // redirect to invoice list if all does well;
            }
            else{
                $body->assign( $language->arrForm );
            }
        }
        else
        {
            if($this->parts[3] > 0){
                $language = new years($this->parts[3]);
                $language->load();
                foreach( $language->TableData as $idx => $val ){
                         $arrBody[$idx] = $val['Value'];
            }
            
            if($arrBody['active'] == 0 )
            {
               $arrBody['active'] =  'checked="checked"';
            }            
        }
        }


        $arrBody['tab.translations'] = $this->doYearTranslationsList($arrBody['uid']);
        $body->assign( $arrBody );

        $skeleton->assign (
                    array (
                    'body' => $body
                    )
            );
            output::as_html($skeleton,true);

    }

    protected function doYearTranslationsList( $year_id )
    {
        

        $body       = new xhtml('body.admin.year.translation.list');
        $body->load();

        $bodyArr = array();
        $langObject = new language();
        $bodyArr['language_id'] =  $langObject->LanguageSelectBox('language_id');

        if(isset($_POST['add_translation'])){
            $years_translations = new years_translations();
            if( $years_translations->doSave() ){

            }
            else{

                if($years_translations->arrForm['uid'] > 0 ){
            $years_translations->arrForm['uid'] = $_POST['uid'];
            $years_translations->arrForm['button_lable'] = 'Update';
            $years_translations->arrForm['cancel_button'] = 'display:block;';
        }
               $years_translations->arrForm['language_id'] =  $langObject->LanguageSelectBox('language_id', $years_translations->arrForm['language_id']);
               $body->assign( $years_translations->arrForm );
            }

        }




        $yearObject = new years();
        $rows = array();
        $page_rows  = array();
        $rows      = $yearObject->yearTranslationsList($year_id);
        if(!empty($rows)) {
            foreach($rows as $uid=>$data) {

                $panel = new xhtml('body.admin.year.translation.row');
                $panel->load();
                $panel->assign($data);
                $page_rows[]    = $panel->get_content();
            }
        }
        else
           $page_rows[] = 'Translation not available with this year.';

       

        
            $bodyArr['uid'] =  0;
            $bodyArr['button_lable'] = 'Add';
            $bodyArr['cancel_button'] = 'display:none;';
       

        
        $bodyArr['year_id'] =  $year_id;
        $bodyArr['frm_action'] =  $_SERVER['REQUEST_URI'].'#tab-2';


        $body->assign('list.rows'          ,   implode('',$page_rows));
        $body->assign($bodyArr);

         return $body->get_content();
    }
    protected function doDelete()
    {
       
        if( $this->parts[3] > 0){
            $invoice = new years($this->parts[3]);
            $invoice->delete();
            //$invoice->redirectTo('admin/language/list/'); // redirect to invoice list if all does well;
            @ob_start();
            header('location:'.$_SERVER['HTTP_REFERER']);
            exit;
        }
        
    }

    protected function doDeletetranslation()
    {

        if( $this->parts[4] > 0){

             $yearTranslationsList = new years_translations($this->parts[4]);
            $yearTranslationsList->delete();
            $yearTranslationsList->redirectTo('admin/year/edit/'.$this->parts[3].'#tab-2'); // redirect to invoice list if all does well;

            
        }
    }

    protected function doList () {

        $skeleton   = new xhtml ('skeleton.admin');
        $skeleton->load();

        $body       = new xhtml('body.admin.year.list');
        $body->load();

        $users      = array();
        $user       = new years();
        $users      = $user->getList();
        $page_rows  = array();
        if(!empty($users)) {
            foreach($users as $uid=>$data) {               
                
                $panel = new xhtml('body.admin.year.list.row');
                $panel->load();
                $panel->assign($data);
                $page_rows[]    = $panel->get_content();
            }
        }

        $page_display_title     =   $user->get_page_title('Page {CURRENT} of {MAX}<br />Displaying results {FROM} to {TO} of {TOTAL}');
        $page_navigation        =   $user->get_prev('<a href="{LINK_HREF}">{LINK_LINK}</a>').$user->get_range('<a href="{LINK_HREF}">{LINK_LINK}</a>',' &raquo ').$user->get_next('<a href="{LINK_HREF}">{LINK_LINK}</a>');

        $body->assign('page.display.title'  ,   $page_display_title);
        $body->assign('page.navigation'     ,   $page_navigation);
        $body->assign('list.rows'          ,   implode('',$page_rows));
       
        $skeleton->assign (
                array (
                'body'          => $body
                )
        );
        output::as_html($skeleton,true);
    }    
    
}

?>