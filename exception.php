<?php
namespace einherjar;

/**
* Fichier de définition de Exception
*/

/**
* Définition d'une classe d'exception personnalisée
*/
class Exception extends \Exception
{
	public $NoticeType;

  // Redéfinissez l'exception ainsi le message n'est pas facultatif
  public function __construct($message, $code = 0, $NoticeType="danger" ,Exception $previous = null) 
  {
    parent::__construct($message, $code, $previous);
	
	$this->NoticeType=$NoticeType;
  }

  // chaîne personnalisée représentant l'objet
  public function __toString() {
    return $this->message;
  }
}

?>