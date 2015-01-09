<?php

namespace einherjar\controller;

class LogFile extends Entity
{
	private $LogFile,
			$LogFileVue,
			$Combats=array(),
			$CombatsVue=array();

	 public function setPage()
	 {
		$this->LogFile   =new \einherjar\modele\LogFile();
		$vueInit		 =array("ObjetModel"=>$this->LogFile,"Langue"=>$this->Langue);
		$this->LogFileVue=new \einherjar\lib\LogFile($vueInit);
	 
		$titre= $this->Langue->get("rubrique_upload_titre");
	 
		$html = $this->tryAndCatch("NewLogFile","getUploadForm");

		$this->passeNoticeTo($this->LogFileVue);
		
		$page = \einherjar\lib\Entity::pageIni(\einherjar\lib\Entity::pageTroisColonnes($html,"",$this->LogFileVue->noticesToHTML()),"",\einherjar\lib\Entity::pageTitre($titre));
		
		return $page;
	 }

	 protected function NewLogFile()
	 {
		if (!isset($this->Environement["_POST"]["submit"]))
			return $this->getUploadForm();
		else
		{
			if ($this->uploadLogFile())
				return $this->getUploadedLogFile();
		}
	 }

	 protected function getUploadForm()
	 {	 
		return $this->LogFileVue->upload();
	 }
	 
	 protected function uploadLogFile()
	 {	 
		if($this->Environement["_FILES"]["path"]["tmp_name"]=="" && $this->Environement["_FILES"]["path"]["tmp_name"]=="")
			throw new \einherjar\Exception('erreur_form',301,"warning");
	
		$logFiles=$this->Environement["_FILES"]["path"];
		
		$tmp_name = $logFiles["tmp_name"];
		$name = $logFiles["name"];
			
		$candidatNewLogFile = $this->instancieNewLogFile($tmp_name,$name,$logFiles["error"]);

		if (!$candidatNewLogFile->getPotentielsFights())
			throw new \einherjar\Exception('erreur_LogFileNoCombat',302,"warning");
		
		$this->LogFile=$candidatNewLogFile;
		
		$this->LogFileVue->setObjetModel($this->LogFile);
		
		return true;
	 }

	private function instancieNewLogFile($tmp_name,$name,$error)
	{
		
		if ($error!=UPLOAD_ERR_OK or !is_uploaded_file($tmp_name)) 
			throw new \einherjar\Exception('erreur_upload',211);
		 
		$result=array();
		$myFile = file_get_contents($tmp_name);
		
		if (!$myFile)
			throw new \einherjar\Exception('erreur_form_upload_vide',302,"warning");

		if( !mb_check_encoding($myFile, 'UTF-8') )
			$myFile = mb_convert_encoding( $myFile, 'UTF-8',"Windows-1252");
			
		$bom = pack('H*','EFBBBF');
		$myFile = preg_replace("/^".$bom."/", '',$myFile);

		$logFile = new \einherjar\modele\LogFile(array("name"=>$name,"logs"=>$myFile));
		
		if (!$logFile)
			throw new \einherjar\Exception('erreur_upload',201);
			
		return $logFile;
	}

	protected function getUploadedLogFile()
	{
		$combatDejaRecord=false;
		$arrayOfFightsLogs=\einherjar\modele\Combat::sortLogsByFight($this->LogFile->getLogs(),$this->LogFile->getPotentielsFights());
		$date=$this->LogFile->getDate();
		$acteur = $this->LogFile->getOwner();
		$html=$this->LogFileVue->uploadedLogFile();
		
		foreach ($arrayOfFightsLogs as $n=>$combatLogs)
		{
			$this->Combats[]=\einherjar\modele\Combat::NewFight($combatLogs,$date,$acteur);
		}
		
		foreach ($this->Combats as $n=>$combat)
		{
			$res=$combat->enregistre();
			if ($res==1)
			{
				$vueInit=array("ObjetModel"=>$combat,"Langue"=>$this->Langue);
				$this->Combats[]=$combat;
				$this->CombatsVue[]=new \einherjar\lib\Combat($vueInit);
			}
			else if ($res==2)
			{
				$combatDejaRecord=true;
			}
		}
		
		if ($combatDejaRecord)
			$this->Exceptions[]=new  \einherjar\Exception('erreur_ExistingCombats',204,"warning");
	
		foreach($this->CombatsVue as $n=>$combatView)
		{
			$html.=$combatView->linkIt("Voir")."<br/>";
		}
	
		return $html;
	}
}

?>