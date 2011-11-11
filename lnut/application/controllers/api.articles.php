<?php

/**
 * api.articles.php
 */

class API_Articles extends Controller {

	public function __construct () {
		parent::__construct();
		$arrPaths = config::get('paths');
		$method = 'getArticle';
		if(isset($arrPaths[2]) && isset($arrPaths[3]) && !empty($arrPaths[2]) && !empty($arrPaths[3])) {
			$newMethod = $arrPaths[2].ucfirst($arrPaths[3]);
			if(method_exists($this,$newMethod)) {
				$method = $newMethod;
			}
		}
		$this->$method();
	}

	// -> /api/articles/get/article/
	protected function getArticle($article_uid=null) {
		$query ="SELECT ";
		$query.="`uid`, ";
		$query.="`title`, ";
		$query.="`article_category_uid`, ";
		$query.="`unit_uid`, ";
		$query.="`locked`, ";
		$query.="`published` ";
		$query.="FROM ";
		$query.="`article` ";
		$query.="WHERE ";
		$query.="1=1 ";
		if(isset($_REQUEST['article_uid']) && is_numeric($_REQUEST['article_uid'])) {
			$query.="AND ";
			$query.="`uid`='".mysql_real_escape_string($_REQUEST['article_uid'])."' ";
		}
		if($article_uid!=null && is_numeric($article_uid)) {
			$query.="AND ";
			$query.="`uid`='".mysql_real_escape_string($article_uid)."' ";
		}
		if(isset($_REQUEST['unit_uid']) && is_numeric($_REQUEST['unit_uid'])) {
			$query.="AND ";
			$query.="`unit_uid`='".mysql_real_escape_string($_REQUEST['unit_uid'])."' ";
		}
		$query.="ORDER BY `uid`";
		$result = database::query($query);
		$arrArticle = array();
		if(mysql_error()=='' && mysql_num_rows($result)) {
			while($row=mysql_fetch_array($result)) {
				if(!isset($_REQUEST['article_uid']) || is_null($article_uid)) {
						$arrArticle[] = array(
						'uid'					=> $row['uid'],
						'title'					=> str_replace('\\','',$row['title']),
						'unit_uid'				=> $row['unit_uid'],
						'article_category_uid'	=> $row['article_category_uid'],
						'locked'				=> $row['locked'],
						'published'				=> $row['published']
					);
				} else {
					$arrArticle[] = array(
						'uid'					=> $row['uid'],
						'title'					=> str_replace('\\','',$row['title']),
						'unit_uid'				=> $row['unit_uid'],
						'article_category_uid'	=> $row['article_category_uid'],
						'locked'				=> $row['locked'],
						'published'				=> $row['published'],
						'translations'			=> $this->getArticleTranslationArray($row['uid']),
						'pages'			=> $this->getArticlePages($row['uid'])
					);
				}
			}
		}
		$arrJson = array(
			'article'	=> $arrArticle
		);
		echo json_encode($arrJson);
	}

	protected function getArticleTranslationArray($article_uid=null) {
		$arrTranslations = array();
		if($article_uid!=null) {
			$query ="SELECT ";
			$query.="`uid`, ";
			$query.="`language_uid`, ";
			$query.="`title`, ";
			$query.="`locked`, ";
			$query.="`published` ";
			$query.="FROM ";
			$query.="`article_translations`";
			$query.="WHERE ";
			$query.="`article_uid`='".$article_uid."' ";
			$arrTranslations = database::arrQuery($query);
		}
		return $arrTranslations;
	}

	// -> /api/articles/get/articleTranslation/
	protected function getArticleTranslation() {
		$arrTranslations = array();
		if(isset($_REQUEST['article_uid']) && is_numeric($_REQUEST['article_uid']) && isset($_REQUEST['language_uid']) && is_numeric($_REQUEST['language_uid'])) {
			$query ="SELECT ";
			$query.="`uid`, ";
			$query.="`language_uid`, ";
			$query.="`title`, ";
			$query.="`locked`, ";
			$query.="`published` ";
			$query.="FROM ";
			$query.="`article_translations`";
			$query.="WHERE ";
			$query.="`article_uid`='".$_REQUEST['article_uid']."' ";
			$query.="AND ";
			$query.="`language_uid`='".$_REQUEST['language_uid']."' ";
			$arrTranslations = database::arrQuery($query);
		}
		$arrJson = array(
			'article'	=> $arrTranslations
		);
		echo json_encode($arrJson);
		//return $arrTranslations;
	}

	// -> /api/articles/get/articleAndTranslations/
	protected function getArticleAndTranslations() {
		if(isset($_REQUEST['article_uid']) && is_numeric($_REQUEST['article_uid'])) {
			$query ="SELECT ";
			$query.="`uid`, ";
			$query.="`title`, ";
			$query.="`article_category_uid`, ";
			$query.="`unit_uid`, ";
			$query.="`locked`, ";
			$query.="`published` ";
			$query.="FROM ";
			$query.="`article` ";
			$query.="WHERE ";
			$query.="1=1 ";
			$query.="AND ";
			$query.="`uid`='".mysql_real_escape_string($_REQUEST['article_uid'])."' ";
			$query.="ORDER BY `uid`";
			$result = database::query($query);
			$arrArticle = array();
			if(mysql_error()=='' && mysql_num_rows($result)) {
				while($row=mysql_fetch_array($result)) {
					$arrArticle[] = array(
						'uid'					=> $row['uid'],
						'title'					=> str_replace('\\','',$row['title']),
						'unit_uid'				=> $row['unit_uid'],
						'article_category_uid'	=> $row['article_category_uid'],
						'locked'				=> $row['locked'],
						'published'				=> $row['published'],
						'translations'			=> $this->getArticleTranslationArray($row['uid']),
						'pages'					=> $this->getArticlePages($row['uid'])
					);
				}
			}
			$arrJson = array(
				'article'	=> $arrArticle
			);
			echo json_encode($arrJson);
		}
	}

	protected function getArticlePageGroups($article_uid=null,$article_page_uid=null) {
		$arrArticleGroups = array();
		if($article_uid!=null) {
			$query ="SELECT ";
			$query.="* ";
			$query.="FROM ";
			$query.="`article_group` ";
			$query.="WHERE ";
			$query.="`article_uid`='".$article_uid."' ";
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				while($row=mysql_fetch_array($result)) {
					$arrArticleGroups[]=array(
						'article_group_uid'=>$row['uid'],
						'name'				=>$row['name'],
						'article_uid'		=>$row['article_uid'],
						'content_item_list'	=> $this->getArticleGroupContent($row['uid'],$article_page_uid)
					);
				}
			}
		}
		return $arrArticleGroups;
	}

	private function getArticleGroupContent($article_group_uid=null,$article_page_uid=null) {
		$arrGroupContent = array();
		$query ="SELECT ";
		$query.="`article_page_uid`, ";
		$query.="`article_content_uid` ";
		$query.="FROM ";
		$query.="`article_group_content` ";
		$query.="WHERE ";
		$query.="`article_group_uid`='".$article_group_uid."' ";
		if($article_page_uid!=null && is_numeric($article_page_uid)) {
			$query.="AND ";
			$query.="`article_page_uid`='".$article_page_uid."' ";
		}
		$query.="ORDER BY ";
		$query.="`article_content_uid`";
		$result = database::query($query);
		if(mysql_error()=='' && mysql_num_rows($result)) {
			while($row=mysql_fetch_array($result)) {
				$arrGroupContent[] = array(
					'article_page_uid'		=>$row['article_page_uid'],
					'article_content_uid'	=>$row['article_content_uid']
				);
			}
		}
		return $arrGroupContent;
	}


	protected function getArticlePages($article_uid=null) {
		$arrArticlePages = array();
		if($article_uid!=null) {
			$query="SELECT ";
			$query.="* ";
			$query.="FROM ";
			$query.="`article_page` ";
			$query.="WHERE ";
			$query.="`article_uid`='".$article_uid."' ";
			$result=database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				while($row=mysql_fetch_array($result)) {
					$arrArticlePages[] = array(
						'article_page_uid'	=>$row['uid'],
						'article_uid'		=>$row['article_uid'],
						'template_uid'		=>$row['template_uid'],
						'page_order'		=>$row['page_order'],
						'width'				=>$row['width'],
						'height'			=>$row['height'],
						'page_content'		=>$this->getArticlePageContent($row['uid']),
						'page_groups'		=>$this->getArticlePageGroups($article_uid,$row['uid']),
						'page_translation'	=>$this->getArticlePageTranslation($row['uid'])
					);
				}
			}
		}
		return $arrArticlePages;
	}

	private function getArticlePageTranslation($article_page_uid=null) {
		$arrArticlePageTranslations = array();
		if($article_page_uid!=null) {
			$query ="SELECT ";
			$query.="* ";
			$query.="FROM ";
			$query.="`article_page_translation` ";
			$query.="WHERE ";
			$query.="`article_page_uid`='".$article_page_uid."'";
			$result = database::query($query);
			if(mysql_error()=='' && mysql_num_rows($result)) {
				while($row=mysql_fetch_array($result)) {
					$arrArticlePageTranslations[]=array(
						'article_page_translation_uid'	=>$row['uid'],
						'language_uid'					=>$row['language_uid'],
						'width'							=>$row['width'],
						'height'						=>$row['height'],
						'page_content_translation'		=>$this->ArticlePageContentTranslation($row['uid'])
					);
				}
			}
		}
		return $arrArticlePageTranslations;
	}

	protected function getArticlePageContent($article_page_uid=null) {
		$arrContent = array();
		if($article_page_uid!=null) {
			$query="SELECT ";
			$query.="* ";
			$query.="FROM ";
			$query.="`article_content` ";
			$query.="WHERE ";
			$query.="`article_page_uid`='".$article_page_uid."'";
			$result=database::query($query);
			if(mysql_error()=="" && mysql_num_rows($result)) {
				while($row=mysql_fetch_array($result)) {
					$arrContent[]=array(
						'article_content_uid'	=>$row['uid'],
						'article_uid'			=>$row['article_uid'],
						'article_page_uid'		=>$row['article_page_uid'],
						'item_type_uid'			=>$row['item_type_uid'],
						'content'				=>$row['content'],
						'rotation'				=>$row['rotation'],
						'width'					=>$row['width'],
						'height'				=>$row['height'],
						'fontfamily'			=>$row['fontfamily'],
						'fontsize'				=>$row['fontsize'],
						'textalignment'			=>$row['textalignment'],
						'textcolour'			=>$row['textcolour'],
						'positionx'				=>$row['positionx'],
						'positiony'				=>$row['positiony'],
						'stackingposition'		=>$row['stackingposition'],
						'accept_content'		=>$row['accept_content']
					);
				}
			}
		}
		return $arrContent;
	}

	protected function getArticlePageContentTranslation($article_content_uid=null) {
		$arrContent = array();
		if($article_content_uid!=null) {
			$query="SELECT ";
			$query.="* ";
			$query.="FROM ";
			$query.="`article_content_translations` ";
			$query.="WHERE ";
			$query.="`article_content_uid`='".$article_content_uid."'";
			$result=database::query($query);
			if(mysql_error()=="" && mysql_num_rows($result)) {
				while($row=mysql_fetch_array($result)) {
					$arrContent[]=array(
						'uid'					=>$row['uid'],
						'article_uid'			=>$row['article_uid'],
						'article_page_uid'		=>$row['article_page_uid'],
						'item_type_uid'			=>$row['item_type_uid'],
						'content'				=>$row['content'],
						'rotation'				=>$row['rotation'],
						'width'					=>$row['width'],
						'height'				=>$row['height'],
						'fontfamily'			=>$row['fontfamily'],
						'fontsize'				=>$row['fontsize'],
						'textalignment'			=>$row['textalignment'],
						'textcolour'			=>$row['textcolour'],
						'positionx'				=>$row['positionx'],
						'positiony'				=>$row['positiony'],
						'stackingposition'		=>$row['stackingposition'],
						'article_translation_uid'=>$row['article_translation_uid']
					);
				}
			}
		}
		return $arrContent;
	}

	protected function ArticlePageContentTranslation($article_page_translation_uid=null) {
		$arrContent = array();
		if($article_page_translation_uid!=null) {
			$query="SELECT ";
			$query.="* ";
			$query.="FROM ";
			$query.="`article_content_translations` ";
			$query.="WHERE ";
			$query.="`article_page_translation_uid`='".$article_page_translation_uid."'";
			$result=database::query($query);
			if(mysql_error()=="" && mysql_num_rows($result)) {
				while($row=mysql_fetch_array($result)) {
					$arrContent[]=array(
						'article_content_translations_uid'	=>$row['uid'],
						'article_uid'			=>$row['article_uid'],
						'article_page_uid'		=>$row['article_page_uid'],
						'article_content_uid'	=>$row['article_content_uid'],
						'item_type_uid'			=>$row['item_type_uid'],
						'content'				=>$row['content'],
						'rotation'				=>$row['rotation'],
						'width'					=>$row['width'],
						'height'				=>$row['height'],
						'fontfamily'			=>$row['fontfamily'],
						'fontsize'				=>$row['fontsize'],
						'textalignment'			=>$row['textalignment'],
						'textcolour'			=>$row['textcolour'],
						'positionx'				=>$row['positionx'],
						'positiony'				=>$row['positiony'],
						'stackingposition'		=>$row['stackingposition']
					);
				}
			}
		}
		return $arrContent;
	}

	private function getDirData($path='.') {
		$ignore = array('.','..');

		$dh = opendir($path);

		$arrFiles = array();

		while(false !== ($file = readdir($dh))) {
			if(!in_array($file,$ignore)) {
				if(is_dir($path.'/'.$file)) {
					$arrFiles[] = array(
						'name'	=> $file,
						'folder'=> '',
						'files'	=> $this->getDirData($path.'/'.$file)
					);
				} else {
					$arrFiles[] = array(
						'name'	=> $file,
						'folder'=> '',
						'files'	=> array()
					);
				}
			}
		}

		closedir($dh);

		return $arrFiles;
	}

	// -> /api/articles/get/contentListing/

	protected function getContentListing2() {
		echo '<pre>';
		print_r($this->scan_directory_recursively(config::get('site').'swf/article/'));
		echo '</pre>';
	}

	protected function getContentListing() {
		echo json_encode($this->scan_directory_recursively(config::get('site').'swf/article/'));
	}

	protected function scan_directory_recursively($directory='', $filter=false) {
		// if the path has a slash at the end we remove it here
		if(substr($directory,-1) == '/') {
			$directory = substr($directory,0,-1);
		}

		// if the path is not valid or is not a directory ...
		if(!file_exists($directory) || !is_dir($directory)) {
			echo 'DIR:'.$directory;
			// ... we return false and exit the function
			return FALSE;
		// ... else if the path is readable
		} else if(is_readable($directory)) {
			// initialize directory tree variable
			$directory_tree = array();
			// we open the directory
			$directory_list = opendir($directory);

			// and scan through the items inside
			while (FALSE !== ($file = readdir($directory_list))) {
				// if the filepointer is not the current directory
				// or the parent directory
				if($file != '.' && $file != '..') {
					// we build the new path to scan
					$path = $directory.'/'.$file;

					// if the path is readable
					if(is_readable($path)) {
						// we split the new path by directories
						$subdirectories = explode('/',$path);
						// if the new path is a directory
						if(is_dir($path)) {
							// add the directory details to the file list
							$directory_tree[] = array(
								//'path' => $path,
								'name' => end($subdirectories),
								'kind' => 'directory',
								// we scan the new path by calling this function
								'content' => $this->scan_directory_recursively($path, $filter),
								'url'	=>config::base(str_replace(config::get('site'),'',$path))
							);
							// if the new path is a file
						} else if(is_file($path)) {
							// get the file extension by taking everything after the last dot
							$extension = end(explode('.',end($subdirectories)));

							// if there is no filter set or the filter is set and matches
							if($filter === FALSE || $filter == $extension) {
							// add the file details to the file list
							$directory_tree[] = array(
								//'path'		=> $path,
								'name'		=> end($subdirectories),
								//'extension'	=> $extension,
								//'size'		=> filesize($path),
								'kind'		=> 'file',
								'url'	=>config::base(str_replace(config::get('site'),'',$path))
							);
						}
					}
				}
			}
		}
		// close the directory
		closedir($directory_list);

		// return file list
		return $directory_tree;
		// if the path is not readable ...
		} else {
		// ... we return false
			return false;
		}
	}

	protected function getContentListing_old() {

		$startPath = '/var/www/vhosts/languagenut.com/swf/article/';
		$arrFiles = $this->getDirData($startPath);

		foreach($arrFiles as $index=>$data) {
			$array[$data['name']] = array(
				'folder'	=> 'http://www.languagenut.com/swf/article/'.$data['name'].'/',
				'files'		=> array(),
				'folders'	=> array()
			);

			foreach($data['files'] as $index=>$file) {
				if(count($file['files']) < 1) {
					$array[$data['name']]['files'][] = $file['name'];
				} else {
					$subfiles = array();
					foreach($file['files'] as $index=>$subfile) {
						$subfiles[] = $subfile['name'];
					}
					$array[$data['name']]['folders'][] = array(
						'folder'	=> $file['name'],
						'files'		=> $subfiles
					);
				}
			}
		}

		echo json_encode($array);
	}

	// -> /api/get/articleTemplates/
	protected function getArticleTemplates() {
		$query ="SELECT ";
		$query.="`uid`, ";
		$query.="`name`, ";
		$query.="`width`, ";
		$query.="`height` ";
		$query.="FROM ";
		$query.="`template` ";
		$query.="WHERE ";
		$query.="`is_suitable_to_article` ='1' ";
		$query.="ORDER BY `uid`";
		$result = database::query($query);
		$arrTemplates = array();
		if(mysql_error()=='' && mysql_num_rows($result)) {
			while($row=mysql_fetch_array($result)) {
				$arrTemplates[] = array(
					'uid'					=> $row['uid'],
					'name'					=> str_replace('\\','',$row['name']),
					'width'					=> $row['width'],
					'height'				=> $row['height']
				);
			}
		}
		$arrJson = array(
			'template'	=> $arrTemplates
		);
		echo json_encode($arrJson);
	}

	// -> /api/articles/get/articleCategories/
	protected function getArticleCategories() {
		$arrCategories = article_category::getList(true);
		$arrJson = array(
			'categories'	=> $arrCategories
		);
		echo json_encode($arrJson);
	}

	// -> /api/articles/create/article/
	protected function createArticle() {
		$arrJson = array(
			'title'						=>'joshi and joshi',
			'article_template_type_uid'	=>1,
			'article_category_uid'		=>1,
			'unit_uid'					=>1,
			'template_uid'				=>9,

		);
		//echo json_encode($arrJson); exit;
		if(isset($_REQUEST['data']) && !empty($_REQUEST['data'])) {
			$objJson=json_decode($_REQUEST['data']);
			$objArticle = new article();
			$response = $objArticle->APICreateArticle($objJson);
			
			if(is_array($response)) {
				echo json_encode($response);
			} else if(is_numeric($response) && $response > 0) {
				echo json_encode(array('article_uid'=>$response));
				//$this->getArticle($response);
			}
		}
	}

	// -> /api/articles/update/article/
	protected function updateArticle() {
		$arrJson = array(
			'title'						=>'joshi and joshi',
			'article_category_uid'		=>1,
			'unit_uid'					=>1,
			'article_uid'				=>1
		);
		if(isset($_REQUEST['data']) && !empty($_REQUEST['data'])) {
			$objJson=json_decode($_REQUEST['data']);
			$objArticle = new article();
			$response = $objArticle->APIUpdateArticle($objJson);
			
			if(is_array($response)) {
				echo json_encode($response);
			} else if(is_numeric($response) && $response > 0) {
				echo json_encode(array('article_uid'=>$response));
				//$this->getArticle($response);
			}
		}
	}

	// -> /api/articles/copy/translations/?article_uid=[VALUE]
	protected function copyTranslations() {
		if(isset($_REQUEST['article_uid']) && is_numeric($_REQUEST['article_uid']) && $_REQUEST['article_uid'] > 0) {

			// PEFORM COPY TRNASLATION TO article_translation
			$objArticleTranslations = new article_translations();
			$response = $objArticleTranslations->APICopyArticleTranslations($_REQUEST['article_uid']);

			// PERFORM COPY TRANSLATION TO article_page_translation and article_contnt_translations
			$objArticlePageTranslation = new article_page_translation();
			$objArticlePageTranslation->APICopyArticlePageTranslations($_REQUEST['article_uid']);

			echo json_encode($response);
		}
	}

	// -> /api/articles/create/ArticlePageContent/?data={json}
	protected function createArticlePageContent() {
		$arrJson = array(
			'item_type_uid'			=>1,
			'content'				=>'mystream',
			'rotation'				=>1,
			'width'					=>500,
			'height'				=>500,
			'fontfamily'			=>'arial',
			'fontsize'				=>10,
			'textalignment'			=>'left',
			'textcolour'			=>'000',
			'positionx'				=>1,
			'positiony'				=>1,
			'stackingposition'		=>1,
			'accept_content'		=>1,
			'article_page_uid'		=>6,
			'article_uid'			=>1
		);
		//echo json_encode($arrJson);
		if(isset($_REQUEST['data']) && !empty($_REQUEST['data'])) {
			$objJson=json_decode($_REQUEST['data']);
			$objArticleContent = new article_content();
			$response = $objArticleContent->APICreateArticleContent($objJson);
			if(is_array($response)) {
				echo json_encode($response);
			}
		}
	}

	// -> /api/articles/update/ArticlePageContent/?data={json}
	protected function updateArticlePageContent() {
		$arrJson = array(
			'item_type_uid'			=>1,
			'content'				=>'SJoshi',
			'rotation'				=>1,
			'width'					=>500,
			'height'				=>500,
			'fontfamily'			=>'arial',
			'fontsize'				=>10,
			'textalignment'			=>'left',
			'textcolour'			=>'000',
			'positionx'				=>1,
			'positiony'				=>1,
			'stackingposition'		=>1,
			'accept_content'		=>1,
			'article_content_uid'	=>3
		);
		//echo json_encode($arrJson);
		if(isset($_REQUEST['data']) && !empty($_REQUEST['data'])) {
			$objJson=json_decode($_REQUEST['data']);
			$objArticleContent = new article_content();
			$response = $objArticleContent->APIupdateArticleContent($objJson);
			if(is_array($response)) {
				echo json_encode($response);
			}
		}
	}

	// -> /api/articles/copy/articlePageContent/?article_page_content_uid=XXX
	private function copyArticlePageContent() {
		if(isset($_REQUEST['article_page_content_uid']) && is_numeric($_REQUEST['article_page_content_uid'])) {
			$objArticleContentTranslation = new article_content_translations();
			$response = $objArticleContentTranslation->APICopyArticlePageContent($_REQUEST['article_page_content_uid']);
			echo json_encode($response);
		}
	}

	// -> /api/articles/create/ArticlePageContentTranslation/?data={json}
	protected function createArticlePageContentTranslation() {
		$arrJson = array(
			'item_type_uid'			=>1,
			'content'				=>'mystream',
			'rotation'				=>1,
			'width'					=>500,
			'height'				=>500,
			'fontfamily'			=>'arial',
			'fontsize'				=>10,
			'textalignment'			=>'left',
			'textcolour'			=>'000',
			'positionx'				=>1,
			'positiony'				=>1,
			'stackingposition'		=>1,
			'article_page_uid'		=>6,
			'article_page_translation_uid'	=>1,
			'article_page_content_uid'	=>1,
			'article_uid'			=>1
		);
		//echo json_encode($arrJson);
		if(isset($_REQUEST['data']) && !empty($_REQUEST['data'])) {
			$objJson=json_decode($_REQUEST['data']);
			$objArticleContent = new article_content_translations();
			$response = $objArticleContent->APICreateArticleContentTranslation($objJson);
			if(is_array($response)) {
				echo json_encode($response);
			}
		}
	}

	// -> /api/articles/update/ArticlePageContentTranslation/?data={json}
	protected function updateArticlePageContentTranslation() {
		$arrJson = array(
			'item_type_uid'			=>1,
			'content'				=>'VSJoshi',
			'rotation'				=>1,
			'width'					=>500,
			'height'				=>500,
			'fontfamily'			=>'arial',
			'fontsize'				=>10,
			'textalignment'			=>'left',
			'textcolour'			=>'000',
			'positionx'				=>1,
			'positiony'				=>1,
			'stackingposition'		=>1,
			'article_content_translation_uid'	=>43
		);
		// echo json_encode($arrJson);
		if(isset($_REQUEST['data']) && !empty($_REQUEST['data'])) {
			$objJson=json_decode($_REQUEST['data']);
			$objArticleContent = new article_content_translations();
			$response = $objArticleContent->APIupdateArticleContentTranslation($objJson);
			if(is_array($response)) {
				echo json_encode($response);
			}
		}
	}

	// -> /api/articles/publish/Article/?article_uid=XXX
	protected function publishArticle() {
		if(isset($_REQUEST['article_uid']) && is_numeric($_REQUEST['article_uid']) && $_REQUEST['article_uid'] > 0) {
			$objArticle = new article($_REQUEST['article_uid']);
			if($objArticle->get_valid()) {
				$objArticle->load();
				$objArticle->set_published(1);
				$objArticle->save();
				echo json_encode(
					array(
						'status'		=>'success',
						'article_uid'	=>$_REQUEST['article_uid']
					)
				);
			}
		}
	}
	// -> /api/articles/unpublish/Article/?article_uid=XXX
	protected function unpublishArticle() {
		if(isset($_REQUEST['article_uid']) && is_numeric($_REQUEST['article_uid']) && $_REQUEST['article_uid'] > 0) {
			$objArticle = new article($_REQUEST['article_uid']);
			if($objArticle->get_valid()) {
				$objArticle->load();
				$objArticle->set_published(0);
				$objArticle->save();
				echo json_encode(
					array(
						'status'		=>'success',
						'article_uid'	=>$_REQUEST['article_uid']
					)
				);
			}
		}
	}
	// -> /api/articles/locked/Article/?article_uid=XXX
	protected function lockedArticle() {
		if(isset($_REQUEST['article_uid']) && is_numeric($_REQUEST['article_uid']) && $_REQUEST['article_uid'] > 0) {
			$objArticle = new article($_REQUEST['article_uid']);
			if($objArticle->get_valid()) {
				$objArticle->load();
				$objArticle->set_locked(1);
				$objArticle->save();
				echo json_encode(
					array(
						'status'		=>'success',
						'article_uid'	=>$_REQUEST['article_uid']
					)
				);
			}
		}
	}
	// -> /api/articles/unlocked/Article/?article_uid=XXX
	protected function unlockedArticle() {
		if(isset($_REQUEST['article_uid']) && is_numeric($_REQUEST['article_uid']) && $_REQUEST['article_uid'] > 0) {
			$objArticle = new article($_REQUEST['article_uid']);
			if($objArticle->get_valid()) {
				$objArticle->load();
				$objArticle->set_locked(0);
				$objArticle->save();
				echo json_encode(
					array(
						'status'		=>'success',
						'article_uid'	=>$_REQUEST['article_uid']
					)
				);
			}
		}
	}
	// -> /api/articles/save/StudentArticleInput/data=JSON
	protected function saveStudentArticleInput() {
		$arrJson = array(
			'unit_uid'					=>1,
			'article_uid'				=>1,
			'language_uid'				=>14,
			'article_page_content_uid'	=>1,
			'value_submitted'			=>'Hi!!!'
		);
		//echo json_encode($arrJson);
		if(isset($_REQUEST['data']) && !empty($_REQUEST['data'])) {
			$objJson=json_decode($_REQUEST['data']);
			$objArticleData = new article_data();
			$response = $objArticleData->APISaveStudentArticleInput($objJson);
			if(is_array($response)) {
				echo json_encode($response);
			}
		}
	}

	// -> /api/articles/create/articlePage/?template_uid=VALUE&article_uid=VALUE
	protected function createArticlePage() {
		$arrResult = array();
		$template_uid	= null;
		$article_uid	= null;
		if(isset($_REQUEST['template_uid']) && is_numeric($_REQUEST['template_uid'])) {
			$template_uid=$_REQUEST['template_uid'];
		}
		if(isset($_REQUEST['article_uid']) && is_numeric($_REQUEST['article_uid'])) {
			$article_uid=$_REQUEST['article_uid'];
		}
		$objArticlePage = new article_page();
		$arrResult=$objArticlePage->APICreateArticlePage($template_uid,$article_uid);
		echo json_encode($arrResult);
	}

	// -> /api/articles/update/articlePage/?data=JSON
	protected function updateArticlePage() {
		$arrResult = array();
		$arrJson = array(
			'article_page_uid'	=>1,
			'width'				=>500,
			'height'			=>500
		);
		if(isset($_REQUEST['data']) && !empty($_REQUEST['data'])) {
			$objJson=json_decode($_REQUEST['data']);
			$objArticlePage = new article_page();
			$arrResult=$objArticlePage->APIUpdateArticlePage($objJson);
			echo json_encode($arrResult);
		}
	}

	// -> /api/articles/copy/articlePage/?article_page_uid=XXX
	private function copyArticlePage() {
		if(isset($_REQUEST['article_page_uid']) && is_numeric($_REQUEST['article_page_uid'])) {
			$objArticlePageTranslation = new article_page_translation();
			$response = $objArticlePageTranslation->APICopyPageTranslation($_REQUEST['article_page_uid']);
			echo json_encode($response);
		}
	}

	// -> /api/articles/create/group/
	protected function createGroup() {
		$arrJson = array(
			'name'				=>'group1',
			'article_uid'		=>1,
			'content_uid_list'	=>array(10,12,15)
		);
		//echo json_encode($arrJson);
		if(isset($_REQUEST['data']) && !empty($_REQUEST['data'])) {
			$objJson=json_decode($_REQUEST['data']);
			$objArticleGroup = new article_group();
			$response = $objArticleGroup->APICreateGroup($objJson);
			if(is_array($response)) {
				echo json_encode($response);
			}
		}
	}

	// -> /api/articles/update/group/
	protected function updateGroup() {
		$arrJson = array(
			'article_group_uid'=>3,
			'name'				=>'group2',
			'article_uid'		=>1,
			'content_uid_list'	=>array(10,12,15)
		);
		//echo json_encode($arrJson);

		if(isset($_REQUEST['data']) && !empty($_REQUEST['data'])) {
			$objJson=json_decode($_REQUEST['data']);
			$objArticleGroup = new article_group();
			$response = $objArticleGroup->APIUpdateGroup($objJson);
			if(is_array($response)) {
				echo json_encode($response);
			}
		}
	}

	// -> /api/articles/delete/group/
	protected function deleteGroup() {
		if(isset($_REQUEST['article_content_uid']) && is_numeric($_REQUEST['article_content_uid'])) {
			$objArticleGroup = new article_group($_REQUEST['article_content_uid']);
			if($objArticleGroup->get_valid()) {
				$objArticleGroup->APIDelete($_REQUEST['article_content_uid']);
				echo json_encode(array(
					'status'=>'sucess'
				));
			} else {
				echo json_encode(array(
					'status'	=>'fail',
					'message'	=>'article_content_uid is not valid.'
				));
			}
		}
	}
	// -> /api/articles/delete/article/?article_uid=XXX
	public function deleteArticle() {
		if(isset($_GET['article_uid']) && is_numeric($_GET['article_uid'])) {
			$objArticle = new article();
			$response = $objArticle->deleteArticle($_GET['article_uid']);
			echo json_encode($response);
		} else {
			echo json_encode(array(
				'status'	=>'false',
				'message'	=>'article_uid is not valid.'
			));
		}
	}

	// -> /api/articles/delete/ArticlePageContent/?article_content_uid=XXX
	public function deleteArticlePageContent() {
		if(isset($_REQUEST['article_content_uid']) && is_numeric($_REQUEST['article_content_uid'])) {
			$objArticleContent = new article_content();
			$response = $objArticleContent->APIdeleteArticleContent($_REQUEST['article_content_uid']);
		}
		echo json_encode($response);
	}


	// -> /api/articles/delete/ArticlePage/?article_uid=XXX&article_page_uid=XXX
	public function deleteArticlePage() {
		if(isset($_REQUEST['article_uid']) && is_numeric($_REQUEST['article_uid']) && isset($_REQUEST['article_page_uid']) && is_numeric($_REQUEST['article_page_uid'])) {
			$objArticlePage = new article_page();
			$response = $objArticlePage->APIdeleteArticlePage($_REQUEST['article_page_uid'],$_REQUEST['article_uid']);
		}
		echo json_encode($response);
	}
}
?>