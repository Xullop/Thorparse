<?php

class Thorparse_Combat_Modele extends Thorparse_Entity_Modele
{

	public 	$Date,
			$Debut,
			$Fin,
			$Logs,
			$Phases,
			$Participants,
			$Acteurs;
			
	private $Tableaux
			,$Duree;
	
	public function __construct(array $donnees)
	{
		Thorparse_Entity_Modele::__construct($donnees);
		
		$this->setDuree();
	}
	
	private function setDuree()
	{
		$this->Duree = $this->getFin(true) - $this->getDebut(true);
	}

	public function setDebut($i)
	{
		$this->Debut = $i;
	}
	
	public function setFin($i)
	{
		$this->Fin = $i;
	}
	
	public function setLogs($i)
	{	
		$tab=array();

		foreach ($i as $key => $ligne)
		{	
			$cells=array();

			foreach ($ligne as $col => $value)
			{
				$cells[$col*10]=$value;
				
				switch ($col)
				{
					//11 temps en secondes
					case 1:
						$cells[11]=floor($this->time_to_seconds($value) - $this->getDebut(true));
						break;

					// 21 L'attaquant
					// 31 La cible
					case 2:
					case 3:
						if (strlen($value)>0) 
						{	
							if ($value{0}=="@")
							{
								$cells[($col*10)+1] = $value;
								$cells[($col*10)+2] = 42;
							}
							else
							{
								$position_premiere_accolade = strpos($value,"{");
								$cells[($col*10)+1] = substr($value, 0, $position_premiere_accolade);
								$cells[($col*10)+2] = substr($value, $position_premiere_accolade+1 ,( strpos($value,"}") - ($position_premiere_accolade+1) ) );
							}
						}
						break;

					// 41 L'attaque
					case 4:
					case 5:
						$matches=array();
						if (preg_match("/([^\{]+)\{([0-9]+)\}(?::\s([^\{]+)\{([0-9]+)\})?/",$value,$matches))
						{
							for ($i=1;isset($matches[$i]);$i++)
							{
								$cells[$col*10 + $i] = $matches[$i];
							}
						}
						break;
					
					//61 les degats
					case 6:
						$matches=array();
						if (preg_match("/([0-9]+(?:\*)?)\s(?:([^\{]*)\s\{([^\}]*)\})?(?:\s-(?:([^\{]*)\s\{([^\}]*)\})?)?(?:\s\(([0-9]*)\s([^\{]*)\s\{([^\}]*)\})?/",$value,$matches))
						{
							for ($i=1;isset($matches[$i]);$i++)
							{
								$cells[$col*10 + $i] = $matches[$i];
							}
							
						}
						break;
						
					default:
						break;
				}
			}
			
			$tab[]=$cells;
		}
		
		$this->Logs=$tab;
		
	}
	
	public function setPhases($i)
	{
		$this->Phases = $i;
	}

	public function setParticipants($i)
	{
		$this->Participants = $i;
	}
	
	public function setActeurs($i)
	{
		if (!is_array($i))
		{
			$this->Acteurs = array($i);
		}
		else
		{
			$this->Acteurs = $i;
		}
	}
	
	public function getDebut($format=false,$heure_minute_seconde=0)
	{
		if ($heure_minute_seconde===0)
		{
			return $format ? $this->time_to_seconds($this->Debut) : $this->Debut;
		}
		else
		{
			$time=array();
			preg_match("/^([0-9]+):([0-9]+):([0-9]+)\.([0-9]+)/",$this->Debut,$time);
			return intval($time[$heure_minute_seconde]);
		}
	}
	
	public function getFin($format=false,$heure_minute_seconde=0)
	{
		if ($heure_minute_seconde===0)
		{
			return $format ? $this->time_to_seconds($this->Fin) : $this->Fin;
		}
		else
		{
			$time=array();
			preg_match("/^([0-9]+):([0-9]+):([0-9]+)\.([0-9]+)/",$this->Fin,$time);
			return sprintf("%02d",$time[$heure_minute_seconde]);
		}
	}
	
	private function getDuree()
	{
		return $this->Duree;
	}
	
	public function getLogs()
	{
		return $this->Logs;
	}
	
	public function getPhases()
	{
		return $this->Phases;
	}

	public function getParticipants($type="")
	{
		$tab=$this->Participants;
		
		if (array_key_exists($type,$tab))
			return $tab[$type];
		else
			return $tab;
	}
	
	public function getActeurs()
	{
		return $this->Acteurs;
	}
	
	private function time_to_seconds($c)
	{
		$time=array();
		if (preg_match("/^([0-9]+):([0-9]+):([0-9]+)\.([0-9]+)/",$c,$time))
		{
			return ($time[1]*3600 + $time[2]*60 + $time[3] + $time[4]/1000);
		}
		else
		{
			return 0;
		}
	}
	
	private function get_table_tete($choix)
	{
		$tab=array();
	
		switch ($choix["type"])
		{
			case "damage_receive":
				$titre_colonne=array(
									array("tableau_heure",array("sort"=>"time"))
									,array("tableau_duree",array("sort"=>"int"))
									,array("tableau_source",array("sort"=>"str","search"=>"str_swtor"))
									,array("tableau_cible",array("sort"=>"str","search"=>"str_swtor"))
									,"tableau_degats"
									,"tableau_absorb"
									);
				$titre = "tableau_titre_damage_receive_by_time";
				break;
			case "damage_dealt":
				$titre_colonne=array(
					array("tableau_heure",array("sort"=>"time"))
					,array("tableau_duree",array("sort"=>"int"))
					,array("tableau_source",array("sort"=>"str","search"=>"str_swtor"))
					,array("tableau_cible",array("sort"=>"str","search"=>"str_swtor"))
					,array("tableau_pouvoir",array("sort"=>"str","search"=>"str_swtor"))
					,"tableau_degats"
					);
				$titre = "tableau_titre_damage_dealt_by_time";
				break;
			
			case "cycle":
				$titre_colonne=array(
				array("tableau_heure",array("sort"=>"time"))
				,array("tableau_duree",array("sort"=>"int"))
				,array("tableau_source",array("sort"=>"str","search"=>"str_swtor"))
				,array("tableau_pouvoir",array("sort"=>"str","search"=>"str_swtor"))
				,"tableau_derniere_utilisation");
				$titre = "tableau_titre_cycle_by_time";
				break;
				
			case "cycle_stat":
				$titre_colonne=array(
				array("tableau_source",array("sort"=>"str"))
				,array("tableau_pouvoir",array("sort"=>"str"))
				,array("tableau_pouvoir_nbr",array("sort"=>"int"))
				,array("tableau_delai_moyen",array("sort"=>"int"))
				);
				$titre = "tableau_titre_cycle_stat";
				break;

			case "buffs_receive":
				$titre_colonne=array
				(
				array("tableau_heure",array("sort"=>"time"))
				,array("tableau_duree",array("sort"=>"int"))
				,"tableau_source"
				,"tableau_buff_name"
				);
				$titre = "tableau_titre_buffs_receive_by_time";
				break;				

			case "buffs_stat":
				$titre_colonne=array(
				array("tableau_cible",array("sort"=>"str"))
				,array("tableau_buff_name",array("sort"=>"str","search"=>"str_swtor"))
				,array("tableau_buff_poucentage",array("sort"=>"int"))
				);
				$titre = "tableau_titre_buffs_stat";
				break;	
				
			default:
				throw new Exception("Erreur de tableau"); 
				break;
		}
		
		$titre_colonne = array_map(function ($test)
									{ 
										$cell= isset($test[0]) && is_array($test) ? array(array("value"=>$test[0],"type"=>"langue")) : array(array("value"=>$test,"type"=>"langue")) ;
										$option=array("search"=>false,"sort"=>false,"hide"=>false);
										
										if (isset($test[1]) && is_array($test[1]))
										foreach ($test[1] as $key=>$val)
										{
											$option[$key]=$val;
										}
										return array_merge(array("cell"=> $cell,"cell_type"=>"titre"),$option);
									},$titre_colonne);
	
		$tab["thead"][]=array(0,$titre_colonne);
	
		$tab["title"] = array("value"=>$titre,"type"=>"langue");
		
		$tab["id"] = substr_replace($titre, '', 7, 6);
	
		return $tab;
	}

	
	private function get_table_corps($choix)
	{
		$corps=array();
		$types = array();
		$res=array();

		$choix["options"]["see_null"] = isset($choix["options"]["see_null"]) ? $choix["options"]["see_null"] : true;
	
		switch ($choix["type"])
		{
			case "damage_receive":
				$types = array("damage_involve"=>array("from"=>$choix["from"],"dealt"=>false,"see_null"=>$choix["options"]["see_null"]));
				break;
			case "damage_dealt":
				$types = array("damage_involve"=>array("from"=>$choix["from"],"dealt"=>true,"see_null"=>$choix["options"]["see_null"]));
				break;
			
			case "cycle":
				$types = array("cycle"=>array("from"=>$choix["from"]));
				break;
				
			case "cycle_stat":
				$types = array("cycle"=>array("from"=>$choix["from"]));
				break;
				
			case "buffs_receive":
				$types = array("buffs"=>array("from"=>$choix["from"]));
				break;
			
			case "buffs_stat":
				$types = array("buffs"=>array("from"=>$choix["from"]));
				break;
			
			default:
				throw new Exception("Erreur de tableau"); 
				break;
		}
		
		foreach ($types as $type=>$args)
		{
			ksort($args);
			
			if (isset($this->Tableaux[$type][serialize($args)]))
			{
				$res = $this->Tableaux[$type][serialize($args)];
			}
			else
			{
				$function_name="filter_".$type;
				$function = $this->$function_name($args);
				$res = array_merge(array_filter($this->getLogs(),$function),$res);
				$this->Tableaux[$type][serialize($args)] = $res;
			}
		}
			
		$array=array();

		foreach ($res as $key => $cells)
		{
		
			if ($choix["type"]=="damage_receive")
			{
					$abs= (isset($cells[68]) && $cells[68]==836045448945511 && $cells[65]==836045448945509 )? $cells[66] : 0 ;
					$corps[]=array(		array(array("value"=>$cells[10],"type"=>"temps"))
										,array(array("value"=>$cells[11],"type"=>"int"))
										,array(array("value"=>$cells[21],"type"=>"str"),array("value"=>$cells[22],"name"=>$cells[21],"type"=>"id_swtor"))
										,array(array("value"=>$cells[31],"type"=>"str"),array("value"=>$cells[32],"name"=>$cells[31],"type"=>"id_swtor"))
										,array(array("value"=>$cells[61],"type"=>"int_swtor"))
										,array(array("value"=>$abs,"type"=>"int"))
									);
			}
			else if ($choix["type"]=="damage_dealt")
			{
					$corps[]=array(		array(array("value"=>$cells[10],"type"=>"temps"))
										 ,array(array("value"=>$cells[11],"type"=>"int"))
										 ,array(array("value"=>$cells[21],"type"=>"str"),array("value"=>$cells[22],"name"=>$cells[21],"type"=>"id_swtor"))
										 ,array(array("value"=>$cells[31],"type"=>"str"),array("value"=>$cells[32],"name"=>$cells[31],"type"=>"id_swtor"))
										 ,array(array("value"=>$cells[41],"type"=>"str"),array("value"=>$cells[42],"name"=>$cells[41],"type"=>"id_swtor"))
										 ,array(array("value"=>$cells[61],"type"=>"int_swtor"))
									);
			}
			else if ($choix["type"]=="cycle" || $choix["type"]=="cycle_stat")
			{			
					if (!isset($array[$cells[21]]))
					{
						$array[$cells[21]]["id"]=$cells[22];
						$array[$cells[21]]["pouvoirs"]=array();
					}
					else if (!array_key_exists($cells[42],$array[$cells[21]]["pouvoirs"]))
					{
						$up_time=0;
						$array[$cells[21]]["pouvoirs"][$cells[42]][0] = 1;
						$array[$cells[21]]["pouvoirs"][$cells[42]][1] = $cells[10];
						$array[$cells[21]]["pouvoirs"][$cells[42]][2] = $up_time;
						$array[$cells[21]]["pouvoirs"][$cells[42]][3] = $cells[41];
					}
					else
					{
						$up_time = floor($this->time_to_seconds($cells[10])-$this->time_to_seconds($array[$cells[21]]["pouvoirs"][$cells[42]][1]));
						$array[$cells[21]]["pouvoirs"][$cells[42]][2]+=$up_time;
						$array[$cells[21]]["pouvoirs"][$cells[42]][0]++;
						$array[$cells[21]]["pouvoirs"][$cells[42]][1]=$cells[10];
						
					}
					if ($choix["type"]=="cycle")
					{
						$corps[]=array(
									array(array("value"=>$cells[10],"type"=>"temps"))
									,array(array("value"=>$cells[11],"type"=>"int"))
									,array(array("value"=>$cells[21],"type"=>"str"),array("value"=>$cells[22],"name"=>$cells[21],"type"=>"id_swtor"))
									,array(array("value"=>$cells[41],"type"=>"str"),array("value"=>$cells[42],"name"=>$cells[41],"type"=>"id_swtor"))
									,array(array("value"=>$up_time,"type"=>"int"))
								);
					}
			}
			else if ($choix["type"]=="buffs_receive" || $choix["type"]=="buffs_stat")
			{
				$switch = ($cells[52]==836045448945477) ? 1 : 0;

				$name= $switch==1 ? "+".$cells[53] : "-".$cells[53] ;
				
				if ($choix["type"]=="buffs_receive")
				{
					$corps[]=array(
								array(array("value"=>$cells[10],"type"=>"temps"))
								,array(array("value"=>$cells[11],"type"=>"int"))
								,array(array("value"=>$cells[21],"type"=>"str"),array("value"=>$cells[22],"name"=>$cells[21],"type"=>"id_swtor"))
								//,array(array("value"=>$name,"type"=>"str"),array("value"=>$cells[42],"name"=>$cells[41],"type"=>"id_swtor"))
								,array(array("value"=>$name,"type"=>"str"),array("value"=>$cells[54],"name"=>$cells[53],"type"=>"id_swtor"))
							);
					
					if (count($array)==0)
					{
						$array[][$cells[53]]=$switch;
					}
					else
					{				
						if (!array_key_exists($cells[53],$array[count($array)-1]))
						{
							$array[]=array_merge($array[count($array)-1],array($cells[53]=>$switch));
						}
						else
						{
							$array[]=$array[count($array)-1];
							$array[count($array)-1][$cells[53]]=$switch;
						}
					}
				}
				else
				{
					if (!isset($array[$cells[31]][$cells[54]]) && $switch==1)
					{
						$array[$cells[31]][$cells[54]]=array($cells[10],0,$cells[53]);
					}
					else if (isset($array[$cells[31]][$cells[54]]) && $array[$cells[31]][$cells[54]][0]==-1 && $switch==1)
					{
						$array[$cells[31]][$cells[54]]=array($cells[10],$array[$cells[31]][$cells[54]][1],$cells[53]);
					}
					else if (isset($array[$cells[31]][$cells[54]]) && $array[$cells[31]][$cells[54]][0]!=-1 && $switch==0)
					{
						$array[$cells[31]][$cells[54]] = array( -1 ,
															$array[$cells[31]][$cells[54]][1] + ( $this->time_to_seconds($cells[10]) - $this->time_to_seconds($array[$cells[31]][$cells[54]][0]) )
															,$cells[53]
														);
					}
				}
			}
			else
			{
				throw new Exception("Erreur de tableau"); 
			}	
		}
		
		if ($choix["type"]=="cycle_stat")
		{
			foreach ($array as $char=>$tab)
			foreach ($tab["pouvoirs"] as $pouvoir=>$stat)
			{
				$up_time_moyen = $stat[0]>1 ? floor(($stat[2]/($stat[0]-1))*10)/10 : 0 ;
		
				$corps[]=array( array(array("value"=>$char,"type"=>"str"),array("value"=>$char["id"],"name"=>$char,"type"=>"id_swtor"))
								,array(array("value"=>$stat[3],"type"=>"str"),array("value"=>$pouvoir,"name"=>$stat[0],"type"=>"id_swtor"))
								,array("value"=>$stat[0],"type"=>"int")
								,array("value"=>$up_time_moyen,"type"=>"str")
								);
			}
		}
		else if ($choix["type"]=="buffs_receive" && count($array)>0)
		{
			$ordre_clefs = array_keys($array[count($array)-1]);

			foreach ($array as $index_ligne=>$ligne)
			{
				for ($i=0;$i<count($ordre_clefs);$i++)
				{
					$corps[$index_ligne][$i+4]= isset($ligne[$ordre_clefs[$i]]) ? array("cell"=>array(array("value"=>$ligne[$ordre_clefs[$i]],"type"=>"int")),"cell_type"=>"buff") : array("cell"=>array(array("value"=>0,"type"=>"int")),"cell_type"=>"buff");
				}
			}
		}
		else if ($choix["type"]=="buffs_stat")
		{
			$fin_seconde = $this->getFin(true);
			foreach ($array as $char=>$buffs_tab)
			foreach ($buffs_tab as $buff_id=>$tab)
			{
				$duree =intval(ceil($this->getDuree()));
				$up_time = $tab[0]==-1 ? $tab[1] : $tab[1] + ($fin_seconde - $this->time_to_seconds($tab[0]));
				$poucentage = ($duree!=0) ? floor(($up_time/$duree)*10000)/100 : 0;
				$corps[] = array( array(array("value"=>$char,"type"=>"str")),array(array("value"=>$tab[2],"type"=>"str"),array("value"=>$buff_id,"name"=>$tab[2],"type"=>"id_swtor")) , array( "value"=>$poucentage."%","type"=>"str") );
			}
		}
		
		$corps = array_map( function ($ligne) 
							{ 
								return array(0, array_map( function ($col) 
															{ 
																if (is_array($col) && isset($col["cell"]) && isset($col["cell_type"])) return $col;
																else if (is_array($col) && !isset($col["cell"]) && !isset($col["cell_type"]) && isset($col[0])) return array("cell"=>$col,"cell_type"=>"normal");
																else { return array("cell"=>array($col),"cell_type"=>"normal"); }
															},$ligne ));
							},$corps);

		return array("tbody"=>$corps);
	}
	
	private function get_table_pied($choix)
	{
		return array("tfoot"=>array());
	}
	
	// choix : tableau 
	//		-from
	//		-type : cycle|damage_receive|damage_dealt
	//		-options
	public function get_table($choix,$data_only=false)
	{
		$tab=array();

		$tab= $this->get_table_corps($choix,$data_only);

		
		if (!$data_only)
		{
			$tab = array_merge_recursive($tab,$this->get_table_tete($choix));
			$tab = array_merge_recursive($tab,$this->get_table_pied($choix));
		}

		return $tab;
	}

	private function filter_damage_involve($args)
	{
		$from = is_array($args["from"]) ? $args["from"] : array($args["from"]);
		$dealt=$args["dealt"];
		$see_null=$args["see_null"];
	
		$i = $dealt ? 2 : 3 ;
		return function($test) use($from,$i,$see_null) 
				{ 
					return ( in_array($test[$i*10],$from) && ( $see_null or $test[61]!=0 ) && isset($test[54]) && $test[52]==836045448945477 && $test[54]==836045448945501); 
				};
	}	

	private function filter_cycle($args)
	{
		$from = is_array($args["from"]) ? $args["from"] : array($args["from"]) ;

		return function($test) use($from) 
				{ 
					return (in_array($test[20],$from) && isset($test[54]) && $test[52]==836045448945472 && $test[54]==836045448945479); 
				};
	}
	
	private function filter_buffs($args)
	{
		$from = is_array($args["from"]) ? $args["from"] : array($args["from"]) ;

		return function($test) use($from) 
				{ 
					return (in_array($test[30],$from) && isset($test[54]) && ( $test[52]==836045448945477 || $test[52]==836045448945478 ) && $test[54]!=836045448945500 && $test[54]!=836045448945501 && $test[54]!=810670782152704); 
				};
	}
	
}

?>