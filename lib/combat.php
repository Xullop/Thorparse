<?php
namespace einherjar\lib;

class Combat
{	
	public static function linkIt($combat,$text="")
	{	
		$html="<a href='?page=raidEncounter&id_combat=".$combat->getId()."'>".$text."</a>\n";

		return $html;
	}
}

?>