<?php
namespace einherjar\modele;


/**
 *  Fichier du modèle définissant Thorparse_Log_Modele
 *  
 *  @package Thorparse
 *  @subpackage Modele
 *  @author Swann Legras
 */

/**
 *  Classe modélisant une ligne de log
 *
 *	Les Logs SWTOR sont de la forme [time][source][cible][action][effet](output)<menace>
 *	Cette classe stocke chacune de ses informations
 *
 */
class Log extends Entity
{
	/** @const LogPattern pattern REGEX d'une ligne de log */
	const LogPattern = "/^\[([^\]]*)\]\s\[([^\]]*)\]\s\[([^\]]*)\]\s\[([^\]]*)\]\s\[([^:]*):([^\]]*)\]\s\(([^\(]*(?:\([^\)]*\))*)\)(?:\s\<([^\>]*)\>)?$/";	
	const OutputPattern ="/^(\d*\*?)\s*([^\-\{]*\{\d+\})?\s*(?:-([^\{]*\{\d+\})?)?\s*(?:\((\d*)\s*([^\{]*\{\d+\})\))?$/";
	const TimePattern = "/^(\d{2}):(\d{2}):(\d{2})\.(\d{3})$/";
	
	/**#@+
	 * @access protected
	 */
	
	/**  @var string timestamp du log en secondes (3 décimales) */
	protected $Time;
	
	/**  @var Thorparse_CombatEntity_Modele Cible de l'action */
	protected $Source;
			
	/**  @var Thorparse_CombatEntity_Modele Cible de l'action */
	protected $Cible;
			
	/**  @var Thorparse_SwtorElement_Modele Action du log */
	protected $Action;
			
	/**  @var Thorparse_SwtorElement_Modele Effet de l'action */
	protected $Effet;
			
	/**  @var Thorparse_SwtorElement_Modele Catégorie de l'effet */
	protected $EffetType;

	/**  @var Thorparse_SwtorElement_Modele Le type de dégât infligé */
	protected $DegatType;
	
	/**  @var string La quantité de dégât/soin */
	protected $Output="0";
			
	/**  @var string La quantité de menace généré */
	protected $Menace="0";
	
	/**  @var string La quantité de dégât absorbé */
	protected $Absorb="0";
		
	/**  @var Thorparse_SwtorElement_Modele type de def si cas échéant null sinon */
	protected $Def=null;

	/**  @var bool Vrai si le coup est un critique */
	protected $IsCrit=false;
			
	/**  @var bool Vrai si le bouclier s'est activé sur l'attaque */
	protected $IsShield=false;
	
	/**  @var string la ligne de Log */
	protected $Log="";
	
	protected static $Population=array();

	/**#@-*/
	 
	/**
	 *  Initialise l'objet a partir d'une chaine de caractere
	 *  
	 *  @param string  $log 	la ligne du log
	 *  @param bool    $partiel faux si on ne cherche pas a parser la ligne mais juste a manipuler heure + participants
	 *  @param array   $donnees tableau de donnees externe à la ligne de log ( date par ex )
	 *  @throws Thorparse_Exception_Modele Si la ligne de log n'est pas au format attendu
	 *  @return array formater pour l'hydrate de l'objet
	 */
	public static function strToLogArray($log="",$donnees=array())
	{
		$result = array();

		if (!preg_match(self::LogPattern, $log,$result))
			throw new \einherjar\Exception('erreur_invalid_log'.$log,109);

		$menace = isset($result[8]) ? $result[8] : 0 ;

		$donnees = array_merge($donnees,array("log"=>$log,"time"=>$result[1],"source"=>$result[2],"cible"=>$result[3],"action"=>$result[4],"effetType"=>$result[5],"effet"=>$result[6],"output"=>$result[7],"menace"=>$menace));

		return $donnees;
	}
	
	/**
	 * Définis la propriété time de l'objet sous forme d'un string en secondes
	 *  
	 *  @param string $time le timestamp de la ligne de log de la forme hh:mm:ss.ddd ( heures, minutes, secondes, decimales)
	 *  @return void	 
	 */
	public function setTime($time)
	{
		$this->Time=intval(self::time_to_seconds($time)*1000);
	}

	public function setSource($source)
	{
		$this->Source = $source;
	}

	public function setCible($cible)
	{
		$this->Cible = $cible;
	}
	
	public function setAction($action)
	{
		$this->Action = new SwtorElement(SwtorElement::stringToArray($action));
	}
	
	public function setEffet($effet)
	{
		$this->Effet = new SwtorElement(SwtorElement::stringToArray($effet));
	}
	
	public function setEffetType($effetType)
	{
		$this->EffetType = new SwtorElement(SwtorElement::stringToArray($effetType));
	}
	
	public function setOutput($outputString)
	{
		$result=array();
	
		if($outputString=="")
			return;
	
		if (!is_string($outputString) or !preg_match(self::OutputPattern,$outputString,$result))
			throw new \einherjar\Exception('erreur_invalid_log',109);
		
		$output = $result[1];
		$degatType = isset($result[2]) ? $result[2] : '' ;
		$shieldOrDef = isset($result[3]) ? $result[3] : '' ;
		$absorb = isset($result[4]) ? $result[4] : '' ;

		if (substr($output,-1)=="*")
		{
			$this->IsCrit=true;
			$output=substr($output,0,strlen($output)-1);
		}
		$this->Output=$output;
		
		if(strlen($degatType)>0)
		{
			$this->DegatType = new SwtorElement(SwtorElement::stringToArray($degatType));
		}
		
		if (strlen($shieldOrDef)>0)
		{
			if (strlen($absorb)>0)
				$this->IsShield = true;
			else
				$this->Def = new SwtorElement(SwtorElement::stringToArray($shieldOrDef));
		}
		
		if (strlen($absorb)>0)
		{
			$this->Absorb=$absorb;
		}
	}
	
	public function setMenace($menace)
	{
		$this->Menace=$menace;
	}
	
	public function setLog($l)
	{
		$this->Log=$l;
	}
	
	/**
	 *  Convertis une chaine de caractère de la forme hh:mm:ss.ddd ( heure, minutes, secondes, décimales ) en secondes
	 *  
	 *  @static
	 *  @param $c chaine de caractère au format hh:mm:ss.ddd
	 *  @return int 0 si la chaine est mal formatée
	 */
	public static function time_to_seconds($c)
	{
		$time=array();
		if (preg_match(self::TimePattern,$c,$time))
		{
			return ($time[1]*3600 + $time[2]*60 + $time[3] + $time[4]/1000);
		}
		else
		{
			return 0;
		}
	}
	
	/**
	 *  Convertis une chaine de caractère de la forme hh:mm:ss.ddd ( heure, minutes, secondes, décimales ) en secondes
	 *  
	 *  @static
	 *  @param string $string chaine de caractère représentant l'heure
	 *  @param string $string chaine de caractère représentant l'heure
	 */
	public static function formatTime($string,$heure_minute_seconde)
	{
		if ($heure_minute_seconde===0)
		{
			return self::time_to_seconds($string);
		}
		else if (is_int($heure_minute_seconde) && $heure_minute_seconde>=1 && $heure_minute_seconde<=3)
		{
			$time=array();
			if (!preg_match(self::TimePattern,$string,$time))
				throw new \einherjar\Exception('error_invalid_log_cible',108);
			return sprintf("%02d",$time[$heure_minute_seconde]);
		}
		
		return "0";
	}
	
	/**
	* Renvois la ligne de Log
	*
	*  @return string De la forme [time][source][cible][action][effet](output)<menace> 
	*/
	public function getLog()
	{
		return $this->Log;
	}
	
	public function getTime()
	{
		return $this->Time;
	}
	
	public function getSource()
	{
		return $this->Source;
	}
	
	public function getCible()
	{
		return $this->Cible;
	}
	
	public function getAction()
	{
		return $this->Action;
	}
	
	public function getEffetType()
	{
		return $this->EffetType;
	}
	
	public function getEffet()
	{
		return $this->Effet;
	}
	
	public function getDegatType()
	{
		return $this->DegatType;
	}
	
	public function getMenace()
	{
		return $this->Menace;
	}
	
	public function getAbsorb()
	{
		return $this->Absorb;
	}
	
	public function getOutput()
	{
		return $this->Output;
	}
	
	public function getDef()
	{
		return $this->Def;
	}
	
	public function getIsShield()
	{
		return $this->IsShield;
	}
	
	public function getIsCrit()
	{
		return $this->IsCrit;
	}
	
	public function __toString()
	{
		return $this->Log;
	}
}

?>