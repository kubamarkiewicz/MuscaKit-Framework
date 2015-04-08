<?php
namespace MuscaKit;

class Controller
{

	public $db;
	public $i18n;
	public $template;
	public $config;


	function __construct($db=null, $i18n=null, $template, $config=null)
	{
    	$this->db = $db;
		$this->i18n = $i18n;
    	$this->template = $template;
		$this->config = $config;

		// Set default section = controller name
		$this->template->assign('section', strtolower(get_class($this)));
	}
	

	

	protected function redirect($location='/')
	{
		header('location: '.MUSCA_URL.$location);
		die();
	}



	/* 
	 * Send HTTP error header & display error page & die 
	 * Find more errors codes here: http://krisjordan.com/php-class-for-http-response-status-codes
	 */ 
	protected function error($message='Page not found.', $title='404', $code='404 Not Found')
	{
		// send HTTP error header
		if ($code) header('HTTP/1.1 '.$code);

		if (!$title) $title = $code;
		if (!file_exists(MUSCA_PATH.APP_DIR.TEMPLATES_DIR.'/error.tpl')) die("<pre>$title\n$message");

		// display error page
		$this->template->assign('message', $message);
		$this->template->assign('title', $title);
		$this->template->assign('section', 'error');
		$this->template->display('error.tpl');
		die();
	}

}
