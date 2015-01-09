<?php
namespace einherjar\modele;

/**
 *  Fichier du modèle définissant Thorparse_CombatPlayableCharacter_Modele
 *  
 *  @package Thorparse
 *  @subpackage Modele
 *  @author Swann Legras
 */

/**
 *  Classe modélisant Un personnage joueur de SWTOR dans un combat
 */
class Thorparse_CombatPlayableCharacter_Modele extends Thorparse_CombatEntity_Modele
{	
	protected $Personnage;
	
	public function setPersonnage($char)
	{
		$this->Personnage=$char;
	}
}

?>