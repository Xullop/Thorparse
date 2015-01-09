<?php

namespace einherjar\controller;

class Index
{
	 public function setPage()
	 {
		$page = new \einherjar\lib\Index();

		return $page->display();
	 }
}

?>