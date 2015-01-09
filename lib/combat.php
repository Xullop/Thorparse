<?php
namespace einherjar\lib;

class Combat extends Entity
{	
	public function linkIt($text="")
	{	
		$html="<a href='?page=raidEncounter&id_combat=".$this->ObjetModel->getId()."'>".$text."</a>\n";

		return $html;
	}
}

?>