<?php

namespace einherjar\controller;

class RaidEncounter extends Entity
{
	private $RaidEncounter,
			$RaidEncounterVue;

	public function setPage()
	{
		$this->RaidEncounter   =new \einherjar\modele\RaidEncounter();
		$vueInit		 =array("ObjetModel"=>$this->RaidEncounter,"Langue"=>$this->Langue);
		$this->RaidEncounterVue=new \einherjar\lib\RaidEncounter($vueInit);
	
		$html = $this->tryAndCatch("getCombats","erreurCombat");

		$titre= $this->Langue->get("rubrique_RaidFightStats_titre");

		$this->passeNoticeTo($this->RaidEncounterVue);

		$page = \einherjar\lib\Entity::pageIni(\einherjar\lib\Entity::pageTroisColonnes($html,"",$this->RaidEncounterVue->noticesToHTML()),"",\einherjar\lib\Entity::pageTitre($titre));

		return $page;
	}	 
	
	public function getCombats()
	{
		$idCombat = intval($this->Environement["_GET"]["id_combat"]);
	
		if ($idCombat!=0)
		{
			$combat =new \einherjar\modele\Combat(array("Id"=>$idCombat));
			$combat->charge();
			$this->RaidEncounter->setCombats(array($combat));
			$this->RaidEncounter->FusionneFights();
		}
		
		return $this->RaidEncounterVue->display();
	}
	
	public function erreurCombat()
	{
	
	}
}

?>