<?php
namespace einherjar\lib;
//
//Entity
//
abstract class Entity
{
	public $Langue;
	
	public $Notices;
	
	protected $ObjetModel;
	
	public function setObjetModel($o)
	{
		$this->ObjetModel=$o;	
	}
	
	public function hydrate(array $donnees)
	  {
		foreach ($donnees as $key => $value)
		{
		  $method = 'set'.ucfirst($key);
		   
		  if (method_exists($this, $method))
		  {
			$this->$method($value);
		  }
		}
	  }
	
	public function __construct($donnees=array())
	{
		if(!isset($donnees["Langue"]))
			$donnees["Langue"]=new \einherjar\modele\Langue();
	
		$this->hydrate($donnees);
	}

	public function setLangue($i)
	{
		$this->Langue = $i;
	}
	
	public function getLangue()
	{
		return $this->Langue;
	}	

	protected static function protect($var,$type="string")
	{
		if ($type=="int")
		{
			return intval($var);
		}
		else
		{
			return htmlspecialchars($var);
		}
	}
	
	public static function displayUrlArgs($args)
	{
		$str="?";
		foreach($args as $key=>$value)
		{
			$str.=$key."=".$value."&";
		}
		return substr($str,0,strlen($str)-2);
	}
	
	public static function displayHTMLArgs($args)
	{
		$str="";
		foreach($args as $key=>$value)
		{
			$str.= is_int($key) ? $value." " : $key."=\"".$value."\" ";
		}
		return substr($str,0,strlen($str)-1);
	}
	
	public static function displayHTMLTags($baliseName,$contenu=array(),$args=array(),$double=true)
	{
		$html="<".$baliseName." ".self::displayHTMLArgs($args);
		
		$contenu = is_array($contenu) && !empty($contenu) ? arrayToHTML($contenu) : ( is_string($contenu) ? $contenu : "" );
		
		$html.= $double ? ">".$contenu."</".$baliseName.">" : "/>" ;
		return $html;
	}
	
	public static function displayForm($arrayForm=array(),$action="")
	{
		$form="<form role=\"form\" method=\"post\" action=\"".self::protect($action)."\" enctype=\"multipart/form-data\">";
		
		foreach($arrayForm as $key=>$fieldset)
		{
			$legend = isset($fieldset["legend"]) ? $fieldset["legend"] : "";
		
			$form.="<fieldset>\n<legend>".$legend."</legend>\n";	
			
				$form.=self::arrayToHTML($fieldset["champs"])."<br/>";
				
			$form.="</fieldset>";
		}
		
		$form.="<p class=\"text-center\"><input class=\"btn btn-default\" type=\"submit\"/></p></form>";
		
		return $form;
	}
	
	public static function arrayToHTML($array)
	{
		$html="";

		foreach ($array as $k=>$champ)
		{
			$baliseName = isset($champ["baliseName"]) ? $champ["baliseName"] 	: "input";
			$args	 	= isset($champ["args"]) 	  ? $champ["args"] 	: array();
			$contenu 	= isset($champ["contenu"]) 	  ? $champ["contenu"] : "";
			$double  	= isset($champ["double"])	  ? $champ["double"]  : true;
			$pre  	 	= isset($champ["pre"])		  ? $champ["pre"] 	: "" ;
			$post 	 	= isset($champ["post"])		  ? $champ["post"] 	: "" ;
			$html.=$pre.self::displayHTMLTags($baliseName,$contenu,$args,$double).$post."\n";
		}
		
		return $html;
	}
	
	protected static function navBar($bar="")
	{
		$html=
		"<nav class=\"navbar navbar-inverse navbar-fixed-top\" role=\"navigation\">
		  <div class=\"container\">
			<div class=\"navbar-header\">
			  <a class=\"navbar-brand\" href=\"./\">Thorparse</a>
			</div>"
			/*<div id=\"navbar\" class=\"navbar-collapse collapse\">
			  <form class=\"navbar-form navbar-right\" role=\"form\">
				<div class=\"form-group\">
				  <input placeholder=\"Email\" class=\"form-control\" type=\"text\">
				</div>
				<div class=\"form-group\">
				  <input placeholder=\"Password\" class=\"form-control\" type=\"password\">
				</div>
				<button type=\"submit\" class=\"btn btn-success\">Sign in</button>
			  </form>
			</div><!--/.navbar-collapse -->*/
		 ."</div>
		</nav>";
	
		return $html;
	}
	
	protected static function pageHead($head="")
	{
		$html="
		<html>
			<head>
				<!--<link type='text/css' rel='stylesheet' charset='UTF-8' href='./style/style.css'>-->
				<link href=\"./style/bootstrap/css/bootstrap.min.css\" rel=\"stylesheet\">
				<link href=\"./style/style.css\" rel=\"stylesheet\">
				<link rel=\"icon\" href=\"./style/Star-Wars-The-Old-Republic-xs-icon.png\" type=\"image/png\">
				".$head."
			</head>
			<body>";

		return $html;
	}
	
	public static function pageTitre($titre)
	{
		return "<title>Thorparse - ".$titre."</title>";
	}
	
	protected static function pageFoot($foot="")
	{
		$html=
		"	<script src='https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js'></script>
			<script src='./script/thorparse_js.js'></script>
			<script src='./script/bootstrap/js/bootstrap.min.js'></script>
			<script src=\"http://code.highcharts.com/highcharts.js\"></script>
		</body>
		</html>";
		
		return $html;
	}
	
	public static function pageIni($html="",$bar="",$head="",$foot="")
	{
		return self::pageHead($head).self::navBar($bar).$html.self::pageFoot($foot);
	}
	
	public static function pageTroisColonnes($html="",$left="",$right="")
	{
		//$this->pageLeft($left)
		$html="<div class=\"container-fluid\"><div class=\"row\">".""."<div class=\"col-sm-6 col-sm-offset-3 col-md-6 col-md-offset-2 main\">".$html."</div>".self::pageRight($right)."</div></div>";
		return $html;
	}
	
	public function addNotice($message,$type="danger")
	{
		$this->Notices[$type][]=$message;
	}
	
	public function noticesToHTML()
	{
		$html="";

		$order=array("danger","warning","info");
		
		foreach ($order as $k=>$type)
		{		
			if (isset($this->Notices[$type]))
			{
				$html.= $this->tabNoticesToHTML(array($type=>$this->Notices[$type]));
				unset($this->Notices[$type]);
			}
		}

		return $html;
	}
	
	private static function tabNoticesToHTML($tab)
	{
		$html="";
	
		foreach($tab as $typeName=>$type)
		foreach ($type as $n=>$message)
		{
			$html.="<div class=\"alert alert-".$typeName." alert-dismissable\">
									<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">×</button>
									".$message."
					</div>";
		}
		
		return $html;
	}
	
	protected static function pageLeft($left="")
	{
	
      $html="<div class=\"col-sm-3 col-md-2 sidebar\">
				  <ul class=\"nav nav-sidebar\">
					<li class=\"active\"><a href=\"#\">Upload <span class=\"sr-only\">(current)</span></a></li>
					<li><a href=\"#\">Logs</a></li>
					<li><a href=\"#\">Profil</a></li>
					<li><a href=\"#\">Forum</a></li>
				  </ul>
				</div>";
		return $html;
	}
	
	protected static function pageRight($contenu)
	{
		$html="";
		
		$html.="<div class=\"col-sm-3 col-md-3 main\">";
		$html.=$contenu;
		$html.="</div>";
		
		return $html;
	
	}
	
	protected function arrayToTable($array,$elem="table",$option="class=\"table\"")
	{
		$html="\n<".$elem." ".$option.">\n";
		
		foreach ($array as $row)
		{
			$html.="\t<tr>\n";
			if (is_array($row))
				foreach($row as $cell)
				{
					if (is_string($cell))
						$html.="\t\t<td>".$cell."</td>\n";
					else if (is_array($cell) && isset($cell[1]) && isset($cell[0]))
						$html.="\t\t<td ".$cell[1].">".$cell[0]."</td>\n";
				}
			else
				$html.="\t\t<td>".$row."</td>\n";
			$html.="\t</tr>\n";
		}
		
		$html.="</".$elem.">\n";
		
		return $html;
	}
	
	protected function arrayToListe($array,$option="")
	{
		$html="\n<ul ".$option.">\n";
		
		foreach ($array as $row)
		{
			$html.="\t<li>".$row."</li>\n";
		}
		
		$html.="</ul>\n";
		
		return $html;
	}
}

?>