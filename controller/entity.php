<?php

namespace einherjar\controller;

abstract class Entity
{
	protected  $Environement,$Langue,$Exceptions=array();
			
	public function __construct($args=array())
	{	
		$this->hydrate($args);
	}
	
	public function hydrate(array $donnees)
	  {
		foreach ($donnees as $key => $value)
		{
		  $method = 'set'.ucfirst($key);
		   
		  if (method_exists($this,$method))
		  {
			$this->$method($value);
		  }
		}
	  }
	  
	protected function setEnvironement($args)
	{
		$this->Environement=$args;
	}
	
	protected function setLangue($l)
	{
		$this->Langue= new \einherjar\modele\Langue();
	}
	  
	protected abstract function setPage();
	
	protected function tryAndCatch($actionFunctionName,$erreurHandlerFunctionName,$actionFunctionArgs=array())
	{
		try
		{
			return $this->{$actionFunctionName}($actionFunctionArgs);
		}
		catch (\einherjar\Exception $e)
		{
			$this->Exceptions[]=$e;
		
			return $this->{$erreurHandlerFunctionName}();
		}
	}
	
	protected function passeNoticeTo($vue)
	{
		foreach ($this->Exceptions as $k=>$e)
		{
			if (method_exists($vue,"addNotice"))
				$vue->addNotice($this->Langue->get(strval($e)),$e->NoticeType);
		}
	}
}

?>