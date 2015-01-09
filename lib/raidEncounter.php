<?php
namespace einherjar\lib;

class RaidEncounter extends Entity
{	
	public function display()
	{
		$html="";
	
		$acteurTable=array();
		foreach ($this->ObjetModel->getActeurs() as $acteur)
		{
			$acteurTable[]=$this->protect($acteur);
		}
		
		$html.=$this->arrayToListe($acteurTable,"id=\"raidEncounterActeurs\"");
		
		$logsTable=array();
		foreach ($this->ObjetModel->getLogs() as $log)
		{
			$logTr=array();
			
			$logView=new \einherjar\lib\Log(array("ObjetModel"=>$log));

			$logTr[]=array($logView->displayTime(),"class=\"logTime\"");
			$logTr[]=array($logView->displaySource(),"class=\"logSource\"");
			$logTr[]=array($logView->displayCible(),"class=\"logCible\"");
			$logTr[]=array($logView->displayAction(),"class=\"logAction\"");
			$logTr[]=array($logView->displayEffet(),"class=\"logEffet\"");
			$logTr[]=array($logView->displayEffetType(),"class=\"logEffetType\"");
			$logTr[]=array($logView->displayDegatType(),"class=\"logDegatType\"");
			$logTr[]=array($logView->displayOutput(),"class=\"logOutput\"");
			$logTr[]=array($logView->displayMenace(),"class=\"logMenace\"");
			$logTr[]=array($logView->displayAbsorb(),"class=\"logAbsorb\"");
			$logTr[]=array($logView->displayDef(),"class=\"logDef\"");
			$logTr[]=array($logView->displayIsCrit(),"class=\"logIsCrit\"");
			$logTr[]=array($logView->displayIsShield(),"class=\"logIsShield\"");
			
			$logsTable[]=$logTr;
		}
		
		$html.=$this->arrayToTable($logsTable,"table","id=\"raidEncounterLogs\"");
		
		$html="<div id=\"thorparseSandBox\">\n<div class=\"hidden\">\n".$html."\n</div>\n</div>\n";
		
		return $html;
	
	}
}

?>