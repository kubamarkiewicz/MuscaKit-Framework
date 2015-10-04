<?php

namespace MuscaKit;
	
/**
 * Languages avaliable are defined in LANGS variable in config.php file
 */

class I18n
{
	// array - languages activated in the app
	protected $langs; 
	// DB connection
	protected $db;
	protected $isAdmin;


	/**
	 * PARAMS
	 * $langs (string) - languages activated in the app, ISO values seperated by coma eg. 'en,es,pl'. first language is the default.
	 * $db - DB connection
	 */
	function __construct($langs, $db=null, $isAdmin=false)
	{
		$this->langs = explode(',', str_replace(' ', '', $langs));
		$this->db = $db;
		$this->isAdmin = $isAdmin;
	}



	/* return avaliable languages */
	function getLangs()
	{
		return $this->langs;
	}


	/* select language & save to session */
	function selectLang($iso)
	{
		if (in_array($iso, $this->getLangs())) return $_SESSION['lang'] = $iso;
	}


	/* return current language */
	function getLang()
	{
		// read from session
		if ($_SESSION['lang']) return $_SESSION['lang'];

		// detect browser language
		if ($_SERVER['HTTP_ACCEPT_LANGUAGE'])
		{
			$browserIso = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
			if (in_array($browserIso, $this->getLangs()))
				return $this->selectLang($browserIso);
		} 
		
		// default language
		return current($this->getLangs());
	}


	
	/**
	 * translate text
	 * 
	 * @param string $tag 		- text to translate
	 * @param string $section
	 * @param string $label 	- will be displayed in translations panel
	 * @param bool html 		- do not escape output & show HTML editor in translatation panel	
	 * @param bool file 		- file upload
	 * @param bool image 		- image upload
	 * @param int width 		- image width
	 * @param int height 		- image height
	 * @param int crop 			- image crop
	 * @param bool admin 		- is it traslation of admin panel
	 */
	function t($tag, $section, $label=NULL, $html=NULL, $file=NULL, $image=NULL, $width=NULL, $height=NULL, $crop=FALSE, $admin=NULL)
	{
		if (!$this->db) return 'no DB connection ';

		if (!isset($admin)) $admin = $this->isAdmin;
		if ($admin)	{
			$table = DB_PRE.'musca_i18n_admin';
		}
		else {
			$table = DB_PRE.'musca_i18n';
		}
		
		$lang = $this->getLang();

        $tag = $tag_tpl = trim($tag);
		$tag_md5 = md5($tag.$label);
		$str_tag = $tag_md5 ? " AND tag='$tag_md5' " : '';
		$str_section = !empty($section) ? " AND section='$section' " : '';
		$sqlRow = "SELECT id, value FROM $table WHERE lang='$lang' $str_tag $str_section";
		$return = $this->db->getRow($sqlRow);
		if(empty($return))
		{
		    if(!empty($tag))
		    {
		    	$langs = $this->getLangs();
		    	foreach($langs as $ilang)
		    	{
				    $insert['section'] = $section;
				    $insert['label'] = $label;
				    $insert['tag'] = $tag_md5;
				    $insert['lang'] = $ilang;
				    $insert['value'] = $tag;
				    $insert['value_tpl'] = $tag_tpl;
				    $insert['html'] = (int)$html;
				    $insert['file'] = (int)$file;
				    $insert['image'] = (int)$image;
				    $insert['width'] = (int)$width;
				    $insert['height'] = (int)$height;
				    $insert['crop'] = (int)$crop;

				    @$this->db->insert($table,$insert);
				}
			    $return = $this->db->getRow($sqlRow);
			}
		}
		return stripslashes($return['value']);
	}
	
}