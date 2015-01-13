<?php

namespace einherjar\modele;

abstract class SqlEntity extends Entity
{
	protected static $Bdd;

	protected static $Mapping;
	
	public function __construct($donnees=array())
	{
		parent::__construct($donnees);
		
		if (!isset(self::$Bdd))
		{
			$sqlArray=parse_ini_file("./config.ini");
			$ermagerd = str_rot13($sqlArray['pass']);
			$db_name  = $sqlArray['name'];
			$db_user  = $sqlArray['user'];
			self::$Bdd = new \PDO('mysql:host=localhost;dbname='.$db_name,$db_user ,str_rot13($ermagerd),array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
			self::$Bdd->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		}
	}
	
	private static function loadTable(Sqlentity $objet)
    {
		$table=self::tableName($objet);
	
		if (isset(self::$Mapping[$table]))
			return ;
		
        $file = "./modele/bdd/".strtolower($table).'.ini';
		
        if(!file_exists($file))
   			throw new \einherjar\Exception('erreur_model',202);
			
		self::$Mapping[$table] = parse_ini_file($file);
		
		if (!isset(self::$Mapping[$table]["agrege"]))
			self::$Mapping[$table]["agrege"]=array();
    }
	
	private static function tableName(Entity $objet)
	{
		$tab=explode("\\",get_class($objet));
	
		$str= count($tab)>1 ? $tab[0]."_" : "";
		$str.=lcfirst(array_pop($tab))."s";
	
		return $str;
	}
	
	private static function formatSqlString(Sqlentity $objet,$props,$lien=", ")
	{
		$tab=array();
		$str="";
		$relationBinaire=array();
		$tableName=self::tableName($objet);

		if (!is_array($props))
			return array("string"=>$str,"values"=>$tab,"relationBinaire"=>$relationBinaire);
		
		Sqlentity::loadTable($objet);
		
		foreach($props as $propArray)
			{
				if (is_array($propArray))
				{
					$prop=$propArray[0];
					$op=$propArray[1];
				}
				else
				{
					$prop=$propArray;
					$op="=";
				}
			
				$method="get".$prop;
				
				if (!method_exists($objet,$method))
					continue;
				
				$value=$objet->$method();
				
				if (is_string($value) || is_int($value))
				{
				
					$str.=$prop.$op.":".$prop.$lien;
					$tab[$prop]=$value;
				}
				else if(is_array($value) && count($value)>0 && !in_array($prop,self::$Mapping[$tableName]["agrege"]))
				{
					$ListeObjetsLiesName=self::tableName(get_class($value[0]));
					
					foreach ($value as $k=>$v)
					{
						$relationBinaire[$ListeObjetsLiesName][]= $v->getId()!=0 ? $v->getId() : $v->enregistre();
					}
				}
				else if (is_array($value) && count($value)>0 && in_array($prop,self::$Mapping[$tableName]["agrege"]))
				{
					$buffer="";
				
					foreach ($value as $v)
					{
						$buffer.=strval($v)."\n";
					}
					
					$str.= $prop.$op.":".$prop.$lien;
					$tab[$prop]=substr($buffer,0,strlen($buffer)-1);
				}
				else if (is_object($value))
				{
					$champ="id_".lcfirst(array_pop(explode("\\",get_class($value))));
					$str.=$champ.$op.":".$champ.$lien;
					$tab[$champ]=$value->getId();
				}
			}
		
		return array("sqlString"=>substr($str,0,strlen($str)-strlen($lien)),"sqlValues"=>$tab,"relationBinaire"=>$relationBinaire);
	}
	
	////
	//
	// Enregistre un objet entity dans la base de donnees
	// dans la table du meme nom de l'objet ( au pluriel )
	// si l'objet contient des collections d'objets 
	// la fonction les enregistres aussi
	// un check est fait sur l'id pour ne pas faire de doublon
	//
	//
	public function enregistre($objectPropsToRecord=array())
	{	
		try
		{
			self::$Bdd->beginTransaction();

			$tableName=self::tableName($this);
			$str="INSERT INTO ".$tableName." SET ";
			$ref = new \ReflectionClass($this);

			if (empty($objectPropsToRecord))
			foreach($ref->getProperties(\ReflectionProperty::IS_PUBLIC) as $prop)
			{
				$objectPropsToRecord[]=$prop->getName();
			}
			
			$res=self::formatSqlString($this,$objectPropsToRecord);

			$tab=$res["sqlValues"];
			$str.=$res["sqlString"];
			$relationBinaire=$res["relationBinaire"];
			
			// Préparation de la requête d'insertion.
			$q = self::$Bdd->prepare($str);	

			foreach ($tab as $key=>$val)
			{
				$param = is_int($val) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
				$q->bindValue(':'.$key, $val, $param);
			}
			
			// Exécution de la requête.
			$q->execute();
			$id = self::$Bdd->lastInsertId();	
			$this->setId($id);
			
			if (!empty($relationBinaire))
			foreach($relationBinaire as $table=>$rows)
			{
				if (count($rows)>0) 
				{
					$requeteString="INSERT INTO ".$tableName."_".$table." (`id_".$tableName."`,`id_".$table."`)";

					$args = array_fill(0, count($rows[0]), '?');
					$params = array();
					
					foreach($rows as $row)
					{
						$values[] = "(".implode(',', $args).")";
						foreach($row as $value)
						{
							$params[] = $value;
						}
					}

					$query = $requeteString." VALUES ".implode(',', $values);
					$stmt = self::$Bdd->prepare($query);
					$stmt->execute($params);
				}
			}
			
			self::$Bdd->commit();
			
			return 1;
		}
		catch (\PDOException $e)
		{
			self::$Bdd->rollBack();
		
			switch ($e->getCode())
			{
				case 23000:
					return 2;
				break;
				default:
					throw new \einherjar\Exception("erreur_model",200);
				break;
			}
		}
	}
	
	public function charge($objectPropsToSelect=array(),$whereProps=array("Id"))
	{
		try
		{
			self::$Bdd->beginTransaction();

			$tableName=self::tableName($this);
			
			$column="";
			
			if (count($objectPropsToSelect))
			{
				foreach($objectPropsToSelect as $prop)
				{
					$column.=$prop.",";
				}
				
				$column=substr($column,0,strlen($column)-1);
			}
			else
			{
				$column="*";
			}
			
			$str="SELECT ".$column." FROM ".$tableName." WHERE ";
			
			$res=self::formatSqlString($this,$whereProps,"AND ");

			$tab=$res["sqlValues"];
			$str.=$res["sqlString"];
			
			// Préparation de la requête
			$q = self::$Bdd->prepare($str);	

			foreach ($tab as $key=>$val)
			{
				$param = is_int($val) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
				$q->bindValue(':'.$key, $val, $param);
			}
			
			// Exécution de la requête.
			$q->execute();
			
			$queryResult=$q->fetch(\PDO::FETCH_ASSOC);
			
			if (!$queryResult)
				throw new \einherjar\Exception("erreur_pageNotFound",201);
			
				self::$Bdd->commit();
				
				foreach($queryResult as $prop=>$val)
				{
					if (method_exists($this,"get".$prop))
						$this->{"set".$prop}($val);
				}
			
			return 1;
		}
		catch (\PDOException $e)
		{
			self::$Bdd->rollBack();
		
			switch ($e->getCode())
			{
				default:
					throw new \einherjar\Exception("erreur_model",200);
				break;
			}
		}	
	}
	
	public static function select($objet,$objectPropsToSelect=array(),$whereStr="",$whereArray=array(),$before="",$after="")
	{
		try
		{
			self::$Bdd->beginTransaction();

			$tableName=self::tableName($objet);
	
			$column="";
	
			if (count($objectPropsToSelect))
			{
				foreach($objectPropsToSelect as $prop)
				{
					$column.=$prop.",";
				}
				
				$column=substr($column,0,strlen($column)-1);
			}
			else
			{
				$column="*";
			}
		
			$str=$before."SELECT ".$column." FROM ".$tableName." WHERE ".$whereStr.$after;
		
			// Préparation de la requête
			$q = self::$Bdd->prepare($str);	
		
			foreach ($whereArray as $key=>$val)
			{
				$param = is_int($val) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
				$q->bindValue(':'.$key, $val, $param);
			}
		
			// Exécution de la requête.
			$q->execute();
		
			$queryResult=$q->fetchAll(\PDO::FETCH_ASSOC);
		
			$objetsResult=array();
		
			if (!$queryResult)
				throw new \einherjar\Exception("erreur_pageNotFound",201);

				
			$className=get_class($objet);
			
			foreach ($queryResult as $row)
			{
				$objetsResult[]=new $className($row);
			}
			
			self::$Bdd->commit();
			
			return $objetsResult;
		}
		catch (\PDOException $e)
		{
			self::$Bdd->rollBack();
		
			echo $e;die();
		
			switch ($e->getCode())
			{
				default:
					throw new \einherjar\Exception("erreur_model",200);
				break;
			}
		}	
	}
}

?>