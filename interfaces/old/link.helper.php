<?php

class Thorparse_Link_helper extends Thorparse_Entity_Helper
{
	protected $Objet,
			  $Page,
			  $Action,
			  $Texte;
	
	public function __construct($objet,$text="",$action="view",$args=array())
	{
		if (!is_object($objet) or substr(get_class($objet), -7)!="_Modele" or !in_array($action,array("view","edit")) or !is_string($text))
			throw new Thorparse_Exception_Modele("error_invalid_link",500);
			
		$this->Objet =$objet;
		$split		 =explode("_",get_class($objet));
		$this->Page  =$split[1];
		$this->Action=$action;
		$this->Texte =$this->Langue->get($text);
	}
	
	public function __toString()
	{
		return "<a href=\"?page=".$this->Page."&action=".$this->Action."\">".$this->Texte."</a>";
	}
}

?>