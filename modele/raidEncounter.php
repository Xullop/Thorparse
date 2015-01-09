<?php

namespace einherjar\modele;

class RaidEncounter extends SqlEntity
{

	public 	$Combats;
	
	protected 	$Logs,
				$Acteurs,
				$Participants,
				$Date;
			
	public function FusionneFights()
	{	
		if (count($this->Combats)==1)
		{
			$uniqueCombat=$this->Combats[0];
		
			$this->setLogs($uniqueCombat->getLogs());
			$this->setActeurs(array($uniqueCombat->getActeur()));
			$this->setParticipants($uniqueCombat->getParticipants());
			$this->setDate($uniqueCombat->getDate());
		}
		else
		{
		
		}
	}
	
	public function getCombats()
	{
		return $this->Combats;
	}
	
	public function getLogs()
	{
		return $this->Logs;
	}
	
	public function getActeurs()
	{
		return $this->Acteurs;
	}
	
	public function getParticipants()
	{
		return $this->Participants;
	}
	
	public function getDate()
	{
		return $this->Date;
	}
	
	public function setCombats($c)
	{
		$this->Combats=$c;
	}
	
	public function setLogs($l)
	{
		$this->Logs=$l;
	}
	
	public function setActeurs($a)
	{
		$this->Acteurs=$a;
	}

	public function setParticipants($p)
	{
		$this->Participants=$p;
	}
	
	public function setDate($d)
	{
		$this->Date=$d;
	}
}

?>