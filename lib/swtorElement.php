<?php
namespace einherjar\lib;

class SwtorElement extends Entity
{
	public function __toString()
	{
		$name="";
		$id=0;
	
		if (is_object($this->ObjetModel) && method_exists($this->ObjetModel,"getSwtorId") && method_exists($this->ObjetModel,"getNom"))
		{
			$name=$this->ObjetModel->getNom();
			$id=$this->ObjetModel->getSwtorId();
		}
		
		$str="<span class=\"SwtorElement\"><span class=\"SwtorName\">".$name."</span><span class=\"SwtorId\">".$id."</span></span>";
		
		return $str;
	}
	
	public static function SwtorElementToString($src)
	{
		if (!is_object($src))
			return $src;
	
		$view = new self(array("ObjetModel"=>$src));
	
		return strval($view);
	}
}

?>