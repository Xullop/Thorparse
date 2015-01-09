<?php 

class Thorparse_tableaux_helper extends Thorparse_Entity_Helper
{
	private $Id,
			$Thead=array(),
			$Tbody=array(),
			$Tfoot=array(),
			$Largeur=0,
			$Longueur=0,
			$Title="";

	public function __construct(array $donnees=array())
	{
		Thorparse_Entity_Helper::__construct($donnees);
		
		$this->setLongueur();
		$this->setLargeur();
	}
			
	protected function setId($i)
	{
		$this->Id=$i;
	}
	
	protected function getId()
	{
		return $this->Id;
	}
	
	protected function setThead($i)
	{
		$this->Thead=$i;
	}
	
	protected function getThead()
	{
		return $this->Thead;
	}
	
	protected function setTbody($i)
	{
		$this->Tbody=$i;
	}
	
	protected function getTbody()
	{
		return $this->Tbody;
	}
	
	protected function setTfoot($i)
	{
		$this->Tfoot=$i;
	}
	
	protected function getTfoot()
	{
		return $this->Tfoot;
	}
	
	protected function setTitle($i)
	{
		$this->Title=$i;
	}
	
	protected function getTitle()
	{
		return $this->Title;
	}
	
	protected function setLargeur()
	{
		$helper=$this;
		$this->Largeur = array_reduce( 	array($this->getTbody(),$this->getThead(),$this->getTfoot())
										,function ($carry,$item) 
											{ 
												foreach ($item as $row_index=>$row)
												{
													$carry = max($carry,count($row[1]));
												}
												return $carry;
											}
										,0	
								);
	}
	
	protected function getLargeur()
	{
		return $this->Largeur;
	}
	
	protected function setLongueur()
	{
		$this->Longueur = count($this->getTbody())+count($this->getThead())+count($this->getTfoot());
	}
	
	protected function getLongueur()
	{
		return $this->Longueur;
	}
	
	public function range_time($combat)
	{
		$langue = $this->getLangue();
	
		$debut = $this->protect($combat->getDebut(true));
		$fin   = $this->protect($combat->getFin(true));

		$form_update = $this->protect($langue->get("form_update"));
		$erreur = $this->protect($langue->get("erreur_nojavascript"));
	
/* 	
		$html="
		<script>
			thorparse_afficher_range(".$fin.",".$debut.",'".$s_deb."','".$m_deb."','".$h_deb."','".$s_fin."','".$m_fin."','".$h_fin."',".$min.",".$max.",'".$form_update."');
		</script>
		<noscript>
			".$erreur."
		</noscript>\n";  */
		
		$from="";
	
		foreach ($combat->getActeurs() as $n=>$perso)
		{
			$from.="<input type=\"hidden\" name=\"participant_".$n."\" value=\"".$perso."\" />\n";
		}
		
		$html="<div id=\"range\">
		<input id=\"debut\" type=\"hidden\" name=\"debut\" value=\"".$debut."\" />
		<input id=\"fin\" type=\"hidden\" name=\"fin\" value=\"".$fin."\" />
		".$from."
		<input type='hidden' name=\"submit\" value='".$form_update."' />
		</div>";
		
		return $html;
	}

	public function assemble_table($part)
	{
		$html="";
		
		foreach ($part as $row_index=>$row)
		{
			$class = "";
			$html.="\t<tr ".$class.">\n";

			if (isset($row[1]))
			{
				foreach ($row[1] as $col_index=>$col)
				{					
					if (isset($col["cell"]) && isset($col["cell_type"]))
					{
						$balise = "td";
						$colspan = isset($col["colspan"]) ? $this->protect($col["colspan"],"int") : (($col_index+1)==count($row[1]) && $col_index+1<$this->getLargeur() ? " colspan='".($this->getLargeur()-$col_index)."' " : "") ;
						$class="";
					
						switch ($col["cell_type"])
						{
							case "titre":
								$balise  = "th";
								break;
							case "buff":
								$class = isset($col["cell"][0]["value"]) && $col["cell"][0]["value"]==0 ? " class='buff_down' " : " class='buff_up".($col_index%3)."' " ;
								break;
							default:
								break;
						}
						
						$cell_content="";
						$cell_option="";
						
						foreach($col["cell"] as $n=>$cell_element)
						{

							switch ($cell_element["type"])
							{
								case "int":
									$cell_content .= "<span>".$this->protect($cell_element["value"],"int")."</span>";
									break;
								case "int_swtor":
								case "temps":
								case "str":
									$cell_content .= "<span>".$this->protect($cell_element["value"])."</span>";
									break;
								case "langue":
									$cell_content .= "<span>".$this->getLangue()->get($cell_element["value"])."</span>";
									break;
								case "id_swtor":
									$cell_content .= "<input type='hidden' name='".preg_replace( "/\W/", "", $cell_element["name"])."' value='".$cell_element["value"]."'/>";
									break;
								default:
									break;
							}
						}

						
						if (isset($col["sort"]) && $col["sort"]!=false)
						{
							$cell_option.=" <i class='fa fa-sort clicable sort_".$col["sort"]."'></i>";
						}
						if ($col["cell_type"]=="titre")
						{
							$cell_option.=" <i class='fa fa-compress clicable'></i>";
						}
						
						$html.="\t<".$balise.$colspan.$class.">".$cell_content.$cell_option."</".$balise.">\n";
					}
				}
			}

			$html.="\t</tr>\n";
		}
		
		return $html;
	}
	
	public function show_table()
	{
		$class="reference";
		$doit_contenir = array("Title","Tbody","Thead","Tfoot");
		
		foreach ( $doit_contenir as $key=>$value)
		{
		 if (!isset($this->$value)) return "";
		}
	
		$html_id= isset($this->Id) ? "id='".$this->protect($this->getId())."'" : "" ;
	
		$titre = $this->getTitle();
		$titre = isset($titre['type']) && $titre["type"]=="langue" && isset($titre["value"]) ? $this->getLangue()->get($titre["value"]) : $this->protect($this->getTitle()) ;
	
		$html="<table ".$html_id." class='".$class."'>\n
				<caption> ".$titre." </caption> 
				<thead>\n".$this->assemble_table($this->getThead())."</thead>\n
				<tbody>\n".$this->assemble_table($this->getTbody())."</tbody>\n
				<tfoot>\n".$this->assemble_table($this->getTfoot())."</tfoot>\n
			</table>\n";

		return $html;
	}

}


?>