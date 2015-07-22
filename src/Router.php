<?php
	
namespace MuscaKit;

class Router
{
    protected $db;
    protected $i18n;

	protected $arguments;
	private $uri;
    public $ar_folder_web;
    protected $controllersPath;
    protected $modulesPath;

    protected $controllerNamespace = 'Controllers';
    protected $controllerSuffix = 'Controller';
    protected $actionSuffix = '';
    protected $modulesNamespace = 'Modules';
	
	function __construct($db=null, $i18n=null)
	{
		$this->db = $db;
		$this->i18n = $i18n;

		define('DS', DIRECTORY_SEPARATOR);

		$this->arguments = func_get_args();
		$this->controllersPath = PROTECTED_PATH . CONTROLLERS_DIR;
		$this->modulesPath = MODULES_PATH . DS;
	}
	
	function ignite($url)
	{
		$url = rtrim($url, '/');
		$this->uri = array_slice(explode('/', $url),1);

		$i = 0;

		$doc_root = $_SERVER['DOCUMENT_ROOT'];
		$folder = str_replace('/index.php', '', $_SERVER['SCRIPT_FILENAME']);
		$doc_root_len = strlen($doc_root);
		$folder_len = strlen($folder);
		$folder_real = substr($folder,$doc_root_len,$folder_len);
		$this->ar_folder_web = explode('/', $folder_real);

		// IDIOMAS
		if ($this->i18n && method_exists($this->i18n, 'getLangs'))
		{
			$langs = $this->i18n->getLangs();
			$nFolder = count($this->ar_folder_web);
			$nFolder = $nFolder > 1 ? $nFolder - 1 : 0;

			if(@in_array(@$this->uri[$nFolder], $langs))
			{
                $this->i18n->selectLang($this->uri[$nFolder]);
				unset($this->uri[$nFolder]);
				$this->uri = array_values($this->uri);
			}
		}
		// ----------------- >>

		foreach($this->ar_folder_web as $folder)
		{
			if(!empty($this->uri[$i]) && (in_array('/'.$this->uri[$i], $this->ar_folder_web) || in_array($this->uri[$i], $this->ar_folder_web)))
			{
                array_shift($this->uri);
				if(empty($this->uri)) $this->uri[$i] = '';
			}
		}

		if(empty($this->uri)) $this->uri[$i] = '';
		// print_r($this->uri);

		// Destraducción de la URL
		if (defined('HOST') && $i18n) $this->routes();

		// averiguar si el primer parametro es un directorio existente
		$dir = DS;
		if(is_dir($this->controllersPath.DS.$this->slugToNamespace($this->uri[$i]))) $dir .= $this->slugToNamespace(array_shift($this->uri)).DS;

		// averiguar si el segundo parametro es un directorio existente
		if(isset($this->uri[$i]) && is_dir($this->controllersPath.$dir.$this->slugToNamespace($this->uri[$i]))) $dir .= $this->slugToNamespace(array_shift($this->uri)).DS;

		// averiguar si el tercer parametro es un directorio existente
		if(isset($this->uri[$i]) && is_dir($this->controllersPath.$dir.$this->slugToNamespace($this->uri[$i]))) $dir .= $this->slugToNamespace(array_shift($this->uri)).DS;

		// check if module exists
		$module = '';
		if(isset($this->uri[$i]))
		{
			if(is_dir($this->modulesPath.$this->slugToNamespace($this->uri[$i])))
			{
				$module = array_shift($this->uri);
			}	
		}
		DEFINE('MODULE', $module);


		// averiguar si el controlador existe
		$controller = 'index';
		// print_r($this->uri);
		// print_r($dir);
			// echo $this->modulesPath.$module.CONTROLLERS_DIR.DS.$this->uri[$i].'.php';
		if(isset($this->uri[$i]) && $this->uri[$i])
		{
			if ($module && is_file($this->modulesPath.$this->slugToNamespace($module).DS.$this->slugToNamespace($this->uri[$i]).$this->controllerSuffix.'.php')) $controller = array_shift($this->uri);
			elseif ($module && is_file($this->modulesPath.$this->slugToNamespace($module).CONTROLLERS_DIR.DS.$this->slugToNamespace($this->uri[$i]).$this->controllerSuffix.'.php')) $controller = array_shift($this->uri);
			elseif(is_file($this->controllersPath.$dir.$this->slugToNamespace($this->uri[$i]).$this->controllerSuffix.'.php')) $controller = array_shift($this->uri);
		}

		// cargar el controlador
		$controller = $this->slugToNamespace($controller).$this->controllerSuffix;
		if ($module && is_file($this->modulesPath.$this->slugToNamespace($module).DS.$this->slugToNamespace($controller).'.php')) {
			$namespace = $this->modulesNamespace.'\\'.$this->slugToNamespace($module).str_replace(DS, '\\', rtrim($dir, DS)); 
			$controller = $namespace.'\\'.$controller;
		}		
		elseif ($module && is_file($this->modulesPath.$this->slugToNamespace($module).CONTROLLERS_DIR.DS.$this->slugToNamespace($controller).'.php')) {
			$namespace = $this->modulesNamespace.'\\'.$this->slugToNamespace($module).'\\'.$this->controllerNamespace.str_replace(DS, '\\', rtrim($dir, DS)); 
			$controller = $namespace.'\\'.$controller;
		}
		else {
			$namespace = $this->controllerNamespace.str_replace(DS, '\\', rtrim($dir, DS)); 
			$controller = $namespace.'\\'.$controller;
		}

		// echo $controller; exit;
		//$dispatcher = new $controller($this->db, $this->i18n);
		$class = new \ReflectionClass($controller);
		$dispatcher = $class->newInstanceArgs($this->arguments);

		// include module view path
		if ($module && $dispatcher->template) {
			$dispatcher->template->addTemplateDir($this->modulesPath . $this->slugToNamespace($module) . TEMPLATES_DIR . DS);
		}

		// cargar el metodo
		$action = 'index';
		if (isset($this->uri[$i]))
		{
			if (method_exists($controller, $this->uri[$i])) $action = array_shift($this->uri);
			
			// if controler does not exist but exists view then display view
			// elseif (($controller == 'index') && file_exists(PROTECTED_PATH . TEMPLATES_DIR . DS . $this->uri[$i].'.tpl'))
			// {
			// 	$action = 'output';
			// 	$this->uri[$i] .= '.tpl';
			// }
		}

		// echo 'module:'.$module.', controller:'.$controller.', action:'.$action; exit;

		// if there is more parameters then method acceps then display 404 error
		$method = new \ReflectionMethod($controller, $action);
		if (count($this->uri) > count($method->getParameters())) {
			$action = 'error';
			$this->uri = array();
		}
		$this->uri = array_map('urldecode', $this->uri);
		call_user_func_array(array($dispatcher, $action), $this->uri);
	}

	protected function routes()
	{
		$db = new Musca_DB(HOST, USER, PASSWORD, DB_NAME);
		$arRoutes = $db->getAssoc("SELECT value, value_tpl FROM ".PRE."musca_i18n WHERE section='routes'");

		foreach($this->uri as $k => $v)
			if(!empty($arRoutes[$v])) $this->uri[$k] = $arRoutes[$v];
	}


	protected function slugToNamespace($slug)
	{
		$slug = str_replace('-', '_', $slug);
		$parts = explode('_', $slug);
		$parts = array_map('ucfirst', $parts);
		return implode('', $parts);
	}
}
