<?php

/**
 *  Fichier du modèle définissant Thorparse_CombatEntity_Modele
 *  
 *  @package Thorparse
 *  @subpackage Modele
 *  @author Swann Legras
 */

/**
 *  Classe modélisant Un personnage de SWTOR
 *
 *	Les personnages peuvent être joueur ou non joueur la classe personnage définis leur propriétés communes
 *	
 *	@abstract
 */
abstract class Thorparse_CombatEntity_Modele extends Thorparse_Entity_Modele
{
	/** @const CombatEntityPatern Patern Regex d'un participant combat swtor tel que modélisé dans un log*/
	const CombatEntityPattern="/^(?:)|(?:@\S*)|(?:[^\{]*\{[0-9]+\}:?[0-9]*)$/";

	/** @const CombatPlayableCharacter Patern Regex d'un personnage joueur*/
	const CombatPlayableCharacterPattern="/^@\S*$/";
	
	/** @const CombatPlayableCharacter Patern Regex d'un personnage joueur*/
	const CombatNonPlayableCharacterPattern="/^([^\{]*\{[0-9]+\}):?([0-9]*)$/";
	
	/** @var string chaine issue du log swtor */
	protected $Qui="";
	
	/** @var string Thorparse_Log_Modele[] ligne de log de l'entité concernée */
	protected $Logs=array();
	
	
	/**
	 *  Initialise l'objet
	 *  
	 *  @param mixed[] $data tableau associatif des propriétés de la classes
	 *  @throws Thorparse_Exception_Modele si $data ne contient pas de clefs "qui"
	 *  @return void
	 */
	public function __construct($data)
	{
		if (!isset($data["qui"]))
			throw new Thorparse_Exception_Modele('error_invalid_log',104);
	
		parent::__construct($data);
	}
	
	public function __toString()
	{
		return $this->Qui;
	}

	public function setQui($string)
	{
		if (!is_string($string) or !preg_match(self::CombatEntityPattern,$string))
			throw new Thorparse_Exception_Modele('error_invalid_log',105);
		
		$this->Qui=$string;
	}
	
	public static function TuringTest($data)
	{
		if (!isset($data["qui"]))
			throw new Thorparse_Exception_Modele('error_invalid_log',104);
			
		if (strlen($data["qui"])==0)
			return new Thorparse_CombatVoidEntity_Modele();
			
		return preg_match(self::CombatNonPlayableCharacterPattern,$data["qui"]) ? new Thorparse_CombatNonPlayableCharacter_Modele($data) : new Thorparse_CombatPlayableCharacter_Modele($data);
	}
}

?>