<?php
namespace einherjar\lib;

class ErreurAOA extends Entity
{

	public function display()
	{
		$html="
		<div class=\"jumbotron\">
			<h1><span class=\"glyphicon glyphicon-warning-sign\"></span> ".$this->Langue->get('erreur_e404')."</h1>
			<h2>".$this->Langue->get('erreur_pageNotFound')."</h2>
		</div>";
		
		$html="<div class=\"container-fluid\"><div class=\"row\"><div class=\"col-sm-6 col-sm-offset-3 col-md-6 col-md-offset-2 main\">".$html."</div></div>";
		
		return $this->pageIni($html);
	}
}

?>