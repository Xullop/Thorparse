<?php
namespace einherjar\lib;

class Log extends Entity
{
	public function displayTime()
	{
		return $this->ObjetModel->getTime();
	}
	
	public function displaySource()
	{
		return $this->ObjetModel->getSource();
	}
	
	public function displayCible()
	{
		return $this->ObjetModel->getCible();
	}
	
	public function displayAction()
	{		
		return \einherjar\lib\SwtorElement::SwtorElementToString($this->ObjetModel->getAction());
	}
	
	public function displayEffetType()
	{
		return \einherjar\lib\SwtorElement::SwtorElementToString($this->ObjetModel->getEffetType());
	}
	
	public function displayEffet()
	{
		return \einherjar\lib\SwtorElement::SwtorElementToString($this->ObjetModel->getEffet());
	}
	
	public function displayDegatType()
	{
		return \einherjar\lib\SwtorElement::SwtorElementToString($this->ObjetModel->getDegatType());

	}
	
	public function displayMenace()
	{
		return $this->ObjetModel->getMenace();
	}
	
	public function displayAbsorb()
	{
		return $this->ObjetModel->getAbsorb();
	}
	
	public function displayOutput()
	{
		return $this->ObjetModel->getOutput();
	}
	
	public function displayDef()
	{
		return \einherjar\lib\SwtorElement::SwtorElementToString($this->ObjetModel->getDef());
	}
	
	public function displayIsShield()
	{
		return $this->ObjetModel->getIsShield();
	}
	
	public function displayIsCrit()
	{
		return $this->ObjetModel->getIsCrit();
	}
	

}

?>