<?php
namespace einherjar\lib;

class LogFile extends Entity
{
	public function upload()
	{
		$html="";
		
		$form[]=array("legend"=>$this->Langue->get("logfile_titre_upload"));
		$form[0]["champs"][]=array(  "pre"=>"<div class=\"form-group\">".$this->displayHTMLTags("label",$this->Langue->get("form_label_upload_log"))." : <br/>".$this->displayHTMLTags("div","",array("class"=>"placeholder"))."<div class=\"fileUpload btn btn-primary\">".$this->displayHTMLTags("span",$this->Langue->get("form_button_browse"),array())
									,"post"=>"</div></div>"
									,"args"=>array("name"=>"path","class"=>"buttonUpload","type"=>"file")
									,"double"=>False);
		
		$serverListeOptions="\n<option value=\"EUBM\">Battle Meditation (Europe)</option>\n";
		
		$form[0]["champs"][]=array(  "pre"=>"<div class=\"form-group\">".$this->displayHTMLTags("label",$this->Langue->get("form_label_upload_server"))." : "
									, "post"=>"</div>"
									,"baliseName"=>"select"
									,"args"=>array("class"=>"form-control")
									,"contenu"=>$serverListeOptions);
									
		$form[0]["champs"][]=array("args"=>array("type"=>"hidden","name"=>"submit","value"=>1));
		
		$html.=$this->displayForm($form);

		$this->addNotice($this->Langue->get("notice_form_uploadActivation"),"info");
		$this->addNotice($this->Langue->get("notice_form_uploadLocation"),"info");
		
		return $html;
	}
	
	public function uploadedLogFile()
	{
		$html="";
		
		$html.="<h3>".$this->Langue->get("logfile_titre_listeUpload")."</h3>";
		
		$html.=$this->show();
		
		$this->addNotice($this->Langue->get("notice_form_uploadLogFileSucess"),"info");
		
		return $html;
	}
	
	public function show()
	{
		$html="<p>".$this->ObjetModel->getName()."</p>";
		
		return $html;
	}
}

?>