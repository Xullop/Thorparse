<?php

namespace einherjar\modele;

class Combat extends SqlEntity
{

	public 	$Date,
			$StartTime,
			$EndTime,
			$Acteur,
			$Logs;
			
	private $Participants,
			$Duree=-1;
	
	public static function NewFight($logs,$date,$acteur)
	{	
		$participantsNewFight=array();
	
		foreach ($logs as $log)
		{
			if (!in_array($log->getSource(),$participantsNewFight))
				$participantsNewFight[]=$log->getSource();
			if (!in_array($log->getCible(),$participantsNewFight))
				$participantsNewFight[]=$log->getCible();
		}
		
		$last=key( array_slice( $logs, -1, 1, TRUE ) ); 

		$newFight=new Combat(array("logs"=>$logs,"date"=>$date,"startTime"=>($logs[0]->getTime()),"endTime"=>($logs[$last]->getTime()),"acteur"=>$acteur,"participants"=>$participantsNewFight));
		
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
	
	public function setEndTime($s)
	{
		$this->EndTime=intval($s);
	}
	
	public function setDuree()
	{
		$this->Duree=$this->EndTime-$this->StartTime;
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
	
	public function getEndTime()
	{
		return $this->EndTime;
	}
	
	public function getParticipants()
	{
		return $this->Participants;
	}
	
	public function getDuree($seconde=true)
	{
		if ($this->Duree==-1)
			$this->setDuree();
		
		return $seconde ? floor($this->Duree/1000) : $this->Duree;
	}
	
	public static function msToTime($ms)
	{
		$ms=$ms/1000;
	
		$seconde_calc = $ms%60;

		$seconde = ($seconde_calc>=10) ? $seconde_calc : "0".$seconde_calc;

		$minute_calc = floor($ms/60)%60;	

		$minute = ($minute_calc>=10) ? $minute_calc : "0".$minute_calc;

		$hour_calc = floor($ms/3600)%24;

		$hour = ($hour_calc>=10) ? $hour_calc : "0".$hour_calc;
	
		return $hour.":".$minute.":".$seconde;
	}
}

?>