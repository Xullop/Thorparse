<?php
namespace einherjar\modele;

class LogFile extends Entity
{
	const LogFileNamePattern="/^combat_([^\_]{10})_[_0-9]{15}\.txt$/";

	protected   $User=false,
				$Date="",
				$Name="",
				$Logs=array();

	private $PotentielsFights=array(),
			$Owner=false;
			
	public function setName($name)
	{
		if (!isset($name) or !is_string($name) or !preg_match(self::LogFileNamePattern,$name,$result))
			throw new \einherjar\Exception('erreur_logFileFormat',101);
				
		$this->Date=$result[1];
		
		$this->Name=$name;
	}
	
	public function setLogs($myFile)
	{
		$fights=array();
		$bornesFlag=0;
		$i=0;
		foreach (explode("\n",$myFile) as $ligne)
		{	  
			$ligne=trim($ligne);
		
			if ($ligne=="") continue;
		
			$this->Logs[$i]=new Log(Log::strToLogArray($ligne));

			if (!$this->Owner)
			{
				if ($this->Logs[$i]->getAction()->compare("973870949466112"))
					$this->Owner=$this->Logs[$i]->getSource();
			}
			else if ($bornesFlag==0 && $this->Logs[$i]->getSource()==$this->Owner && $this->Logs[$i]->getEffet()->compare("836045448945489"))
			{
				$debutEtFin = array($this->Logs[$i]->getTime());
				$bornesFlag=1;
			}
			else if ($bornesFlag==1 && $this->Logs[$i]->getCible()==$this->Owner && $this->Logs[$i]->getEffet()->compare(array("836045448945490","836045448945493")))
			{
				$debutEtFin[] = $this->Logs[$i]->getTime();
				$bornesFlag=0;
				$this->PotentielsFights[]=$debutEtFin;
			}
		  $i++;
		}
		
		if (!$this->Owner)
			throw new \einherjar\Exception('erreur_logFileFormat',101);
	}
	
	public function getName()
	{
		return $this->Name;
	}
	
	public function getIdUser()
	{
		return $this->IdUser;
	}
	public function getOwner()
	{
		return $this->Owner;
	}
	public function getDate()
	{
		return $this->Date;
	}
	public function getLogs()
	{
		return $this->Logs;
	}
	public function getPotentielsFights()
	{
		return $this->PotentielsFights;
	}
}

?>

