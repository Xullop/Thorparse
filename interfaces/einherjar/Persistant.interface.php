<?php
namespace interfaces\einherjar;

 Interface Persistant
 {
	public function enregistre(\modele\einherjar\Entity $objet);
	public function update(\modele\einherjar\Entity $objet);
	public function delete(\modele\einherjar\Entity $objet);
	public function get(\modele\einherjar\Entity $objet);
	public function count_element(\modele\einherjar\Entity $objet);
 }

?>