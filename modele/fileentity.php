<?php
namespace einherjar\modele;

abstract class Fileentity extends Entity implements Persistant
{
	public function enregistre()
	{
		$ref = new \ReflectionClass($this);
		$contenu="";
		$str="";
		
		foreach($ref->getProperties() as $o)
		{
			$prop=$o->getName();
			$method="get".$prop;
			if (!method_exists($this,$method))
				continue;
			$value=$this->$method();
			
			if ($prop!="_Id" && (is_string($value) || is_int($value)))
			{
				$str.= $prop."=".$value."_";
				$tab[$prop]=$value;
			}
			else if(is_array($value))
			{
				foreach ($value as $k=>$v)
				{
					if(is_object($v))
					{
						$contenu.=$v."\n";
					}
				}
			}
		}

		$file=fopen($this->pathIt(),'w');
		
		if ($file)
		{
			fwrite($file,substr($str,0,-1)."\n");
			fwrite($file,$contenu);
		}
		
		fclose($file);
		
	}

	private function pathIt()
	{
		$path= explode("\\",get_class($this));
		$path= strtolower(end($path));
		
		return "./".$path."/".$this->get_Id().".txt";
	}

	public function count_element()
	{
		return file_exists($this->pathIt()) ? 1 : 0;
	}
	
	public function get()
	{
		if($this->count_element($this)==0)
			return;

	}
	
	public function update()
	{		
		$this->enregistre();
	}
	
	public function delete()
	{
		if($this->count_element($objet)==0)
			return;

		unlink($this->pathIt());
	}
	
}

?>