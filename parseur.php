 <?php 
 
include_once("autoloader.class.php");
autoloader::init();

//rKWhUerT4va3TD8F mdp bdd

abstract class Thorparse_utils
{
	private static $boss_fight=
	array(
		3303040304021504, // Nefra "Qui barre la route"
		3307992401313792, // Droïde d'assaut surchargé
		
		3303405376241664, // Commandant de porte Draxus
		3281320654405632, // Subteroth
		3288141062471680, // Commissionnaire de la Garde d'Effroi
		3288136767504384, // Corrupteur de la Garde d'Effroi
		3281329244340224, // Démanteleuse de la Garde d'Effroi
		3288029393321984, // Démanteleuse de la Garde d'Effroi
		3288145357438976, // Rempart de la Garde d'Effroi
		3281324949372928, // Pilleur de la Garde d'Effroi
		3317793516683264, // Gardien de la Forteresse
	
		3302567857618944, // Grob'thok "Qui nourrit la Forge"
		3307678868701184, // Ugnaught effroyable
		
		3303551405129728, // Corrupteur Zéro
		3282643504332800, // Droïde de réparation D-03
		2857785339412480  //Mannequin d'entraînement des opérations
	);
	
	public function graph_it($location,$graph)
	{
		
	}
	
	public function catch_fight($src="")
	{
		$src="./combat_2014-10-26_11_26_28_793509.txt";

		$logs  = array();
		$tmp   = array();
		$n=0;
		$boss_fight=false;
		$catch=false;
		$unknow=true;
		$pseudo="";


		$myfile = fopen($src, "r") or die("Unable to open file!");
		// Output one line until end-of-file
		while(!feof($myfile)) 
		{
		  $ligne=fgets($myfile);
		  
		  if ($unknow && preg_match("/^(?:\[[^\]]*\] )\[@([^\]]+)\].*\{973870949466372\}/", $ligne,$tmp))
		  {
			$pseudo=$tmp[1];
			$unknow=false;
		  }
		  else if (preg_match("/^(?:\[([^\]]*)\] ){4}.*\{836045448945489\}/", $ligne)) //début fight
		  {
			$catch=true;
			$i=0;
			$logs[$n]["participants"]=array("pnj"=>array(),"pj"=>array());
			$logs[$n]["debut"]=substr($ligne,1,12);
		  }
		  else if (preg_match("/^(?:\[([^\]]*)\] ){4}.*\{836045448945490\}/", $ligne) or preg_match("/^(?:\[([^\]]*)\] ){2}\[@".$pseudo."\].*\{836045448945493\}/", $ligne)) //fin de combat
		  {
			$catch=false;
			$logs[$n]["acteurs"]=$pseudo;
			$logs[$n]["fin"]=substr($ligne,1,12);
			$n++;
		  }
		  if ($catch)
		  {
			preg_match("/^(?:\[([^\]]*)\] )(?:\[([^\]]*)\] )(?:\[([^\]]*)\] )(?:\[([^\]]*)\] )(?:\[([^\]]*)\] )(?:\(([^\)]*)\))(?: \<([^\>]*)\>)?/", $ligne,$logs[$n]["logs"][$i]);
			
			if (substr($logs[$n]["logs"][$i][2],0,1)=="@" && !in_array($logs[$n]["logs"][$i][2],$logs[$n]["participants"]["pj"]))
			{
				$logs[$n]["participants"]["pj"][]=$logs[$n]["logs"][$i][2];
			}
			else if (substr($logs[$n]["logs"][$i][2],0,1)!="@" && !in_array($logs[$n]["logs"][$i][2],$logs[$n]["participants"]["pnj"]))
			{
				$logs[$n]["participants"]["pnj"][]=$logs[$n]["logs"][$i][2];
			}
			if (substr($logs[$n]["logs"][$i][3],0,1)=="@" && !in_array($logs[$n]["logs"][$i][3],$logs[$n]["participants"]["pj"]))
			{
				$logs[$n]["participants"]["pj"][]=$logs[$n]["logs"][$i][3];
			}
			else if (substr($logs[$n]["logs"][$i][3],0,1)!="@" && !in_array($logs[$n]["logs"][$i][3],$logs[$n]["participants"]["pnj"]))
			{
				$logs[$n]["participants"]["pnj"][]=$logs[$n]["logs"][$i][3];
			}
			
			unset($logs[$n]["logs"][$i][0]);
			
			$i++;
		  }
		  
		}

		fclose($myfile);
		
		return $logs;
	}

	public function is_it_a_boss_fight($l)
	{
		foreach($l["participants"]["pnj"] as $key=>$value)
		{
			$id=substr($value,strrpos($value,"{")+1,16);
			if (in_array($id,self::$boss_fight)) return true;
		}
		
		return false;
	}

}

$v=Thorparse_utils::catch_fight();

$Bragi = new Thorparse_Langue_Modele("fr");

$test=array();

foreach ($v as $k=>$value)
{
		$test[] = new Thorparse_Combat_Modele($value);
		
}

$vue = new Thorparse_Combat_View(array("Langue"=>$Bragi));

echo "<html>
		<head>
			<link type='text/css' rel='stylesheet' href='./style/font-awesome-4.2.0/css/font-awesome.min.css'>
			<link type='text/css' rel='stylesheet' charset='UTF-8' href='./style/style.css'>
		</head><body>";

echo $vue->show_page($test[count($test)-1]);

echo 	"<script src='./script/jquery-1.11.1.min.js'></script>
		<script src='./script/thorparse_js.js'></script>";
echo "</body></html>";



?>