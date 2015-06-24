<?php
namespace MuscaKit;

class Controller
{

	public $db;
	public $i18n;
	public $view;
	public $config;

	function __construct($db=null, $i18n=null, $view, $config=null)
	{
    	$this->db = $db;
		$this->i18n = $i18n;
    	$this->view = $view;
		$this->config = $config;

		// Set default section = controller name
		$this->view->assign('section', strtolower(get_class($this)));
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
	public function error($message="Sorry, we can't find the page you're looking for.", $title='404', $code='404 Not Found')
	{
		// send HTTP error header
		if ($code) header('HTTP/1.1 '.$code);

		if (!$title) $title = $code;
		if (!file_exists(PROTECTED_PATH.TEMPLATES_DIR.'/error.tpl')) die("<pre>$title\n$message");

		// display error page
		$this->view->assign('message', $message);
		$this->view->assign('title', $title);
		$this->view->assign('section', 'error');
		$this->view->display('error.tpl');
		die();
	}

}
