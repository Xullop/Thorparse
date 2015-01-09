<?php

//
//Entity
//
abstract class Thorparse_Entity_Helper
{
	protected static $Langue;

	public function hydrate(array $donnees)
	  {
		foreach ($donnees as $key => $value)
		{
		  $method = 'set'.ucfirst($key);
		   
		  if (method_exists($this, $method))
		  {
			$this->$method($value);
		  }
		}
	  }
	
	public function __construct(array $donnees=array())
	{
		$this->hydrate($donnees);
	}

	public function setLangue($i)
	{
		$this->_Langue = $i;
	}
	
	public function getLangue()
	{
		return $this->_Langue;
	}	
	
	protected function protect($var,$type="string")
	{
		if ($type=="int")
		{
			return intval($var);
		}
		else
		{
			return htmlspecialchars($var);
		}
	}
}

?>