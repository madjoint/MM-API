<?php

abstract class Search {
	public $name;
	abstract function __construct($name, Cache &$Cache);
}

?>