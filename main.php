<?php

namespace einherjar;

class Main
{
	public static $loader,$environement;

    public static function init()
    {
        if (self::$loader == NULL)
            self::$loader = new self();

        return self::$loader;
    }

    public function __construct()
    {
		try 
		{
			session_start();

			$this->setEnvironement();
		
			$controller= "\\einherjar\\controller\\";
			$vue="einherjar\\lib\\";
			
			$page= isset($this->Environement["_GET"]["page"]) ? $this->Environement["_GET"]["page"] : "index";
			$vue.=$page;
			$controller.=$page;
			$init = array();
			$init["environement"]=$this->Environement;
			$init["langue"]="fr";
			
			$this->Page = class_exists($controller) ? new $controller($init) : new \einherjar\controller\ErreurAOA($init);
			
			echo $this->Page->SetPage();
		}
		catch (Exception $e)
		{

		}
    }

	private function setEnvironement()
	{
		$this->Environement =
		array(
			'_SERVER' => $_SERVER,
			'_ENV' => $_ENV,
			'_REQUEST' => $_REQUEST,
			'_GET' => $_GET,
			'_POST' => $_POST,
			'_COOKIE' => $_COOKIE,
			'_FILES' => $_FILES,
			'_SESSION' => $_SESSION
		);
	}
}

?>