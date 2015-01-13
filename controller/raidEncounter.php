<?php

namespace einherjar\controller;

class RaidEncounter extends Entity
{
	private $RaidEncounter,
			$RaidEncounterVue;

	public function setPage()
	{
		$this->RaidEncounter   =new \einherjar\modele\RaidEncounter();
		$vueInit=array("ObjetModel"=>$this->RaidEncounter,"Langue"=>$this->Langue);
		$this->RaidEncounterVue=new \einherjar\lib\RaidEncounter($vueInit);
	
		$res = $this->tryAndCatch("getCombats","erreurCombat");
		
		$html=$res[0];
		$left=$res[1];

		$titre= $this->Langue->get("rubrique_RaidFightStats_titre");

		$this->passeNoticeTo($this->RaidEncounterVue);

		$page = \einherjar\lib\Entity::pageIni(\einherjar\lib\Entity::pageTroisColonnes($html,$left,$this->RaidEncounterVue->noticesToHTML()),"",\einherjar\lib\Entity::pageTitre($titre));

		return $page;
	}	 
	
	public function getCombats()
	{
		$idCombat = intval($this->Environement["_GET"]["id_combat"]);
		
		$left="";
		$html="";
	
		if ($idCombat!=0)
		{
			$combat =new \einherjar\modele\Combat(array("Id"=>$idCombat));
			$combat->charge();
			$this->RaidEncounter->setCombats(array($combat));
			$this->RaidEncounter->FusionneFights();
			
			$autresCombats=array();
			$whereArray=array("CurrentDate"=>$combat->getDate(),"Id"=>$combat->getId(),"id_serveur"=>0,"Acteur"=>$combat->getActeur());

			$autresCombats=$combat->select($combat,
											array("Id",
													"Date",
													"StartTime",
													"EndTime",
													"DATEDIFF(:CurrentDate, Date) as DaysBetweenDates"),
											"id_serveur=:id_serveur AND Acteur=:Acteur AND Id!=:Id ORDER BY DaysBetweenDates ASC, StartTime ASC LIMIT 0,20",
											$whereArray);
			
			
			
			if (count($autresCombats)>0)
			foreach ($autresCombats as $autrecombat)
			{
				$start = $autrecombat->msToTime($autrecombat->getStartTime());
				$duree = $autrecombat->getDuree();
				$linkStr= $start." : ".$duree." s";
				$left.=\einherjar\lib\Combat::linkIt($autrecombat,$linkStr)."<br/>";
			}
			
			$html=$this->RaidEncounterVue->display();
		}
		
		return array($html,$left);
	}
	
	public function erreurCombat()
	{
	
	}
}

?>