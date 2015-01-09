<?php
namespace einherjar\modele;
/**
 *  Fichier du modèle définissant Thorparse_CombatNonPlayableCharacter_Modele
 *  
 *  @package Thorparse
 *  @subpackage Modele
 *  @author Swann Legras
 */

/**
 *  Classe modélisant Un personnage non joueur de SWTOR dans un combat
 */
class Thorparse_CombatNonPlayableCharacter_Modele extends Thorparse_CombatEntity_Modele
{	
	protected $NonPlayableCharacter;
	protected $SwtorId;

	public function __construct($data)
	{		
		parent::__construct($data);
	
		$res=array();
		
		if($this->Qui=="")
		{
			$this->setNonPlayableCharacter("");
			$this->setSwtorId(0);
		}
		else
		{
			if (!preg_match(parent::CombatNonPlayableCharacterPattern,$this->Qui,$res))
				throw new Thorparse_Exception_Modele('error_invalid_log',107);
		
			$this->setNonPlayableCharacter($res[1]);
			$this->setSwtorId($res[2]);
		}
	}

	public function setNonPlayableCharacter($nonPlayableCharacter)
	{
		$this->NonPlayableCharacter=new Thorparse_SwtorElement_Modele($nonPlayableCharacter);
	}
	
	public function setSwtorId($i)
	{
		$this->SwtorId=$i;
	}
}

?>