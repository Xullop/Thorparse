<?php
namespace einherjar\modele;

class Langue
{
    private $languageArray;
    private $userLanguage;

    public function __construct($language="fr")
    {
        $this->userLanguage = $language;
        $this->languageArray = array();
    }

    private function load($var)
    {
        $file = './langue/'.$this->userLanguage."/".strtolower(substr($var, 0, strpos($var,"_"))).'.ini';
        if(!file_exists($file))
        {
            return $var;
        }

        $this->languageArray = array_merge($this->languageArray,parse_ini_file($file));
    }

	public function getLangue()
	{
		return $this->userLanguage;
	}
	
	public function get($var)
	{
		$item = substr($var, strpos($var,"_")+1 , strlen($var));
	
		if (!isset($this->languageArray[$item]))
		{
			$this->load($var);
		}
		if (isset($this->languageArray[$item]))
		{
			return $this->languageArray[$item];
		}
		else 
		{
			return $var;
		}
	}
}

?>