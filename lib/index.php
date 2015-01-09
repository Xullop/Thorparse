<?php
namespace einherjar\lib;

class Index extends Entity
{

	public function display()
	{
		$html="
		<div class=\"jumbotron\">
			<h1><img src=\"./style/Star-Wars-The-Old-Republic-small-icon.png\" class=\".img-responsive\" />Thorparse</h1>
			<p>".$this->Langue->get("text_presentation")."</p>
			<p class=\"text-center\">
				"/*<a class=\"btn btn-primary btn-lg\" href=\"#\" role=\"button\">".$this->Langue->get("form_button_inscription")."</a>*/
				."<a class=\"btn btn-primary btn-lg\" href=\"?page=logFile\" role=\"button\">".$this->Langue->get("form_button_upload")."</a>
			</p>
			
		</div>";
		
		$html="<div class=\"container-fluid\"><div class=\"row\"><div class=\"col-sm-6 col-sm-offset-3 col-md-6 col-md-offset-2 main\">".$html."</div></div>";
		
		return $this->pageIni($html);
	}
}

?>