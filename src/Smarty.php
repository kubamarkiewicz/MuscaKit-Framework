<?php
	
namespace MuscaKit;

class Smarty extends \Smarty
{
	public $i18n;

	public function __construct($i18n=null)
	{
		$this->i18n = $i18n;

		parent::__construct();

		// SMARTY CONFIG --------------------------- >>
// 				$this->caching = false;
// 				$this->force_compile = false;
// 				$this->compile_check = false;
// 				$this->compile_id = $this->lang;
            $this->error_reporting = DEBUG_MODE;

			$this->template_dir 	= TEMPLATES_PATH.'/';
			$this->compile_dir 		= STORAGE_PATH.'/compile/';
			$this->cache_dir 		= STORAGE_PATH.'/cache/';
			$this->addPluginsDir(dirname(__DIR__).'/smarty_plugins');
		// ------------------------------------------- >>


		// DEFINE PATHS --------- >>
			$this->assign('musca_url', MUSCA_URL);
			$this->assign('uploads_dir', UPLOADS_DIR);
		// ------------------------------------ >>

		// i18n	
		if ($this->i18n) {
			$this->assign('langs', $this->i18n->getLangs());
			$this->assign('lang', $this->i18n->getLang());
		}

	}

}
