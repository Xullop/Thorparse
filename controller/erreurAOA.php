<?php

namespace einherjar\controller;

class ErreurAOA
{
	 public function setPage()
	 {
		$page = new \einherjar\lib\ErreurAOA();

		return $page->display();
	 }
}

?>