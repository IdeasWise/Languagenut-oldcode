<?php
/**
 *
 * @author Andrew Whitfield.
 * @abstract
 */
abstract class text {
	/**
	 * All content from any source file should be available only through getters and setters of sub
	 * classes
	 */

	protected $tag_bot			= '{{ ';
	protected $tag_bot_regex	= '\{\{ ';
	protected $tag_eot			= ' }}';
	protected $tag_eot_regex	= ' \}\}';

	protected $fill_bot			= '[[ ';
	protected $fill_bot_regex	= '\[\[ ';
	protected $fill_eot			= ' ]]';
	protected $fill_eot_regex	= ' \]\]';

	protected $content			= '';

	/**
	 * Sub classes should define the base path to prevent loading files outside the permitted folders
	 */
	protected $path				= '';
	/**
	 * @todo - write description
	 * @abstract
	 * @return void.
	 */
	abstract public function load ($file='');

	/**
	 * Allow for dynamic replacement of placeholders within the source file from input such as other objects,
	 * arrays or strings.
	 * @return void.
	 */
	public function assign ($key_data = null, $value_data = null) {
		/**
		 * Check types and deal with them according to type
		 * @todo - make robust object checking for object type by checking if it is a class, is an instanceof or has
		 * parent class that is this abstract class
		 */
		if (is_string($key_data) && strlen($key_data) > 0 && is_string($value_data) && strlen($value_data) > 0) {
			$this->content = str_replace(
					$this->tag_bot . trim($key_data) . $this->tag_eot,
					$value_data,
					$this->content
			);
		} else if (is_string($key_data) && strlen($key_data) > 0 && is_object($value_data)) {
			$this->content = str_replace(
					$this->tag_bot . trim($key_data) . $this->tag_eot,
					$value_data->to_string(),
					$this->content
			);
		} else if (is_array($key_data) && count($key_data) > 0) {
			$keys = array_keys($key_data);
			foreach($key_data as $index=>$value) {
				if(is_object($value)) {
					$values[] = $value->get_content();
				} else {
					$values[] = $value;
				}
			}
			foreach($keys as $index=>$key) {
				$keys[$index] = $this->tag_bot . trim($key) . $this->tag_eot;
			}

			$this->content = str_replace(
					$keys,
					$values,
					$this->content
			);
		}
		return $this;
	}

	public function getPlugins () {

		$output     =   array();

		preg_match_all('/'.$this->tag_bot_regex.'(.*)'.$this->tag_eot_regex.'/',$this->content,$matches);
		if(isset($matches[1]) && count($matches[1]) > 0) {
			foreach($matches[1] as $uid => $data) {

				$pluginName = '';
				$pluginParams = array ();
				$pluginInternalParams = array ();
				preg_match('/plugin \[name="([^"]*)"( params="([^"]*)")?\]/',$data,$plugindata);
				if(isset($plugindata[1]) && count($plugindata[1]) > 0) {
					if(isset($plugindata[1])) {
						$pluginName = $plugindata[1];
					}
					if(isset($plugindata[3])) {
						$pluginParams = explode(';',$plugindata[3]);
						if(count($pluginParams) > 0) {
							foreach($pluginParams as $index=>$nvp) {
								$params = explode(':',$nvp);
								$pluginInternalParams[$params[0]] = $params[1];
							}
						}
					}
					$output[]           =    array("match_string" => $data,"plugin_name" => $pluginName,"plugin_params" => $pluginInternalParams);
				}
			}
		}
		return $output;
	}

	public function clean () {
		/**
		 * These can be applied globally to any template
		 */


		$replacement_text = array(
				'uri'			=> config::url(),
				'images'		=> config::images(),
				'scripts'		=> config::scripts(),
				'styles'		=> config::styles(),
				'base'			=> config::base(),
				'common_styles'	=> config::styles_common(),
				'common_images'	=> config::images_common(),
				'common_scripts'=> config::scripts_common(),
				'common_flash'	=> config::flash_common()
		);

                
                 if(@$_SESSION['user']['admin'] == 1)
                     $replacement_text['admin_uri'] = config::url().'admin/';
                 else
                     $replacement_text['admin_uri'] = config::url().'account/';



		foreach($replacement_text as $key=>$value) {
			$this->content = str_replace($this->tag_bot . trim($key) . $this->tag_eot, $value, $this->content);
		}

		//$this->content = preg_replace('/'.$this->tag_bot_regex.'[^'.$this->tag_eot_regex.']*'.$this->tag_eot_regex.'/','',$this->content);
		$this->content = preg_replace('/'.$this->tag_bot_regex.'(.*)'.$this->tag_eot_regex.'/','',$this->content);
		return $this;
	}

	public function get_source () {
		return $this->path;
	}

	public function get_content ($final = true) {
		$this->get_plugins();
		if ($final) {
			$this->clean();
		}        
		return (string) $this->content;
	}

	public function get_plugins() {
		$plugins                =   array();
		$plugins                =   $this->getPlugins();

		if(count($plugins) > 0) {
			foreach($plugins as $uid => $array) {
				$objPlugin      =   'plugin_'.$array['plugin_name'];
				if(class_exists($objPlugin)) {
					$plugin     =   new $objPlugin($array['plugin_params']);
					$output     =   $plugin->run();
					$this->assign($array['match_string'], $output);
				}
			}
		}
	}

	public function get_tags() {
		$tags   =   array();
		preg_match_all('/'.$this->tag_bot_regex.'[^'.$this->tag_eot_regex.']*'.$this->tag_eot_regex.'/',$this->content,$matches);
		if(!empty ($matches) && !empty ($matches[0])) {
			$tags   =   $matches[0];
		}
		return $tags;
	}
}
?>