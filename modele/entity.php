<?php
namespace einherjar\modele;
//
//Entity
//
abstract class Entity
{
	public $Id=0;

	public function hydrate($donnees)
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
	
	public function __construct($donnees=array())
	{
		$this->hydrate($donnees);
	}

	public function setId($i)
	{
		$this->Id= intval($i);
	}
	
	public function getId()
	{
		return $this->Id;
	}
	
}

?>