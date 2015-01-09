<?php

class autoloader {

    public static $loader;

    public static function init()
    {
        if (self::$loader == NULL)
            self::$loader = new self();

        return self::$loader;
    }

    public function __construct()
    {
		
		set_include_path(get_include_path().PATH_SEPARATOR.'c:\\wamp\\www\\Thorparse\\');
		spl_autoload_extensions('.php');
        spl_autoload_register(array($this,'autoload'));
    }

    public function autoload($class)
    {
		$tab=explode("\\",$class);
		$class=lcfirst(array_pop($tab));
		$path= count($tab)>1 ? array_pop($tab)."/" : "./";
		require_once($path.$class.".php");
    }
}

?>