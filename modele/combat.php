<?php

namespace einherjar\modele;

class Combat extends SqlEntity
{

	public 	$Date,
			$StartTime,
			$Acteur,
			$Logs;
			
	private $Participants,
			$Duree;
	
	public static function NewFight($logs,$date,$acteur)
	{	
		$participantsNewFight=array();
	
		foreach ($logs as $n=>$log)
		{
			if (!in_array($log->getSource(),$participantsNewFight))
				$participantsNewFight[]=$log->getSource();
			if (!in_array($log->getCible(),$participantsNewFight))
				$participantsNewFight[]=$log->getCible();
		}

		$newFight=new Combat(array("logs"=>$logs,"date"=>$date,"startTime"=>($logs[0]->getTime()),"acteur"=>$acteur,"participants"=>$participantsNewFight,"_Id"=>md5($date.$acteur.$logs[0])));
		
		return $newFight;
	}
	
	public static function sortLogsByFight($logs,$bornesArray)
	{
		$multiplesFights=array();
		$i=0;
	
		foreach ($logs as $n=>$log)
		{
			if ($log->getTime()>$bornesArray[$i][1])
			{
				if (!isset($bornesArray[$i+1]))
					break;
				
				$i++;
			}
			if ($log->getTime()>=$bornesArray[$i][0] && $log->getTime()<=$bornesArray[$i][1])
			{
				$multiplesFights[$i][]=$log;
			}
		}

		return $multiplesFights;
	}
	
	public function setLogs($logs)
	{
		if(is_string($logs))
		foreach (explode("\n",$logs) as $ligne)
		{	  
			$this->Logs[]=new Log(Log::strToLogArray($ligne));
		}
		else
			$this->Logs=$logs;
	}
	
	public function setActeur($acteur)
	{
		$this->Acteur=$acteur;
	}
	
	public function setParticipants($participants)
	{
		$this->Participants=$participants;
	}
	
	public function setDate($date)
	{
		$this->Date=$date;
	}
	
	public function setStartTime($s)
	{
		$this->StartTime=intval($s);
	}
	
	public function getLogs()
	{
		return $this->Logs;
	}
	
	public function getActeur()
	{
		return $this->Acteur;
	}	
	
	public function getDate()
	{
		return $this->Date;
	}
	
	public function getStartTime()
	{
		return $this->StartTime;
	}
	
	public function getParticipants()
	{
		return $this->Participants;
	}
}

?>