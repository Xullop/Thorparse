<?php
namespace einherjar\modele;

 Interface Persistant
 {
	public function enregistre();
	public function update();
	public function delete();
	public function get();
	public function count_element();
 }

?>