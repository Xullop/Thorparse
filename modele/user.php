<?php
namespace einherjar\modele;

class Thorparse_User_Modele extends Thorparse_Entity_Modele
{
	public	$pseudo,
			$email,
			$persos;
	private $password;

	public function setPseudo($new_pseudo)
	{
		$pseudo_len = strlen($new_pseudo);
		if (($pseudo_len >= 4) && ($pseudo_len <= 25)) 	
		{ 
			$this->pseudo = $new_pseudo; 
		}
	}
	
	public function setEmail($new_email)
	{
		if (self::check_email_address($new_email))
		{
			$this->email = $new_email;
		}
	}
	
	public function setPassword($new_password)
	{

			if (strlen($new_password) == 128 && is_string($new_password))
			{
				$this->password = $new_password;
			}
	}
	
	public function setPersos ($new_persos)
	{
		if (is_array($new_persos))
		{
			$persos_temp = array();
			for ($i = 0, $nb_persos = count($new_persos) ; $i<$nb_persos ; $i++)
			{
				if ( is_object($new_persos[$i]) && get_class($new_persos[$i]) == "Thorparse_Perso_Modele")
				{
					$persos_temp[] = $new_persos[$i];
				}
			}
			$this->persos = $persos_temp;
		}
	}
	
	public static function check_email_address($email)
	{
		// First, we check that there's one @ symbol, 
		// and that the lengths are right.
		if (!preg_match("/^[^@]{1,64}@[^@]{1,255}$/", $email)) 
		{
			// Email invalid because wrong number of characters 
			// in one section or wrong number of @ symbols.
			return false;
		}
		
		// Split it into sections to make life easier
		$email_array = explode("@", $email);
		$local_array = explode(".", $email_array[0]);
		for ($i = 0, $size=sizeof($local_array) ; $i < $size ; $i++)
		{
			if (!preg_match("/^(([A-Za-z0-9!#$%&'*+\/=?^_`{|}~-][A-Za-z0-9!#$%&'*+\/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$/",$local_array[$i])) 
			{
				return false;
			}
		}
		
		// Check if domain is IP. If not, 
		// it should be valid domain name
		if (!preg_match("/^\[?[0-9\.]+\]?$/", $email_array[1]))
		{
			$domain_array = explode(".", $email_array[1]);
			
			if (sizeof($domain_array) < 2)
			{
				return false; // Not enough parts to domain
			}
			
			for ($i = 0, $size=sizeof($domain_array); $i < $size; $i++)
			{
				if(!preg_match("/^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$/",$domain_array[$i]))
				{
					return false;
				}
			}
		}
		
		return true;
		
	}
}


?>