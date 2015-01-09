<?php
namespace einherjar\modele;
/**
 *  Fichier du modèle définissant Thorparse_SwtorElement_Modele
 *  
 *  @package Thorparse
 *  @subpackage Modele
 *  @author Swann Legras
 */

/**
 *  Classe modélisant Un element de log de SWTOR
 *
 *	La plupart des éléments de swtor ont un nom et et un id, leur format dans un fichier log est Nom{id}
 */
class SwtorElement extends Entity
{
	/** @const SwtorElementPatern format regex d'un élément de log Swtor Nom{id} */
	const SwtorElementPattern="/^([^\{]*)\{([0-9]+)\}$/";
	
	/** @var string Nom de l'élément de log */
	protected $Nom="void";
	
	/** @var string Id de l'élément de log */
	protected $SwtorId=0;
	
	public static function stringToArray($string)
	{
		$res=array();
		$regex=array();

		if (is_string($string))
			if(strlen($string)==0)
			{
				$res["nom"]="void";
				$res["nom"]=0;
			}
			else if (!preg_match(self::SwtorElementPattern,$string,$regex))
				throw new Exception('erreur_invalid_log',103);
			else
			{
				$res["nom"]=$regex[1];
				$res["swtorId"]=$regex[2];
			}

		return $res;
	}
	
	public function __toString() 
	{
		return $this->Nom."{".$this->Id."}";
	}

	public function setNom($nom)
	{
		$this->Nom=trim($nom);
	}

	public function setSwtorId($id)
	{
		if (!preg_match("/^[0-9]*$/",$id))
			throw new Exception('erreur_invalid_log',103);
		
		$this->SwtorId=$id;
	}

	public function getNom()
	{
		return $this->Nom;
	}

	public function getSwtorId()
	{
		return $this->SwtorId;
	}
	
	/**
	 *  Compare $this avec un autre élément selon son id
	 * 	 
	 *  @access public
	 *  @return bool vrai si identique faux sinon
	 */
	public function compare($test)
	{
		if (is_object($test) && get_class($test)==__CLASS__)
		{
			return $this->SwtorId==$test->SwtorId;
		}
		else if (is_array($test))
		{
			return empty($test) ? false : array_pop($test)==$this->SwtorId || $this->compare($test);
		}
		else
		{
			return $test==$this->SwtorId;
		}
	}
}

?>