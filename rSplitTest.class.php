<?php

class rSplitTest {	
	
	public $items = array();
	
	function __construct($experiment_tag, $variations_rules = array('v1' => 0.5, 'v2' => 0.5)) {
		$this->experiment = $experiment_tag;
		$this->variations = $variations_rules;
	}
		
	function deleteAll() {
		global $predis;
		foreach($this->variations as $k => $v) {
			$predis->del("e{$this->experiment}:v{$k}:u");
		}
	}
	
	function distributeItemsIntoVariations() {
		global $predis;
		
		$items = $this->items;
		shuffle($items);
		$item_count = count($items);
		foreach($this->variations as $k => $v) {
			if($predis->exists("e{$this->experiment}:v{$k}:u") == 1) {
				echo("Experiment {$this->experiment} is running!\n");
				return FALSE;	
			}
			for($i = 0; $i < ceil($item_count * $v); $i++) {
				$item = array_pop($items);
				if($item != NULL)
					$predis->sadd("e{$this->experiment}:v{$k}:u", $item);
			}
		}
		return count($items);
	}
	
	function getItemsFromVariation($variation) {
		global $predis;
		
		return $predis->smembers("e{$this->experiment}:v{$variation}:u");
	}

	function getCountFromVariation($variation) {
		global $predis;
		
		return $predis->scard("e{$this->experiment}:v{$variation}:u");
	}
	
	
	function getResultsFromVariation($test_hypothesis_function, $variation) {
		global $predis;
		
		$positives = 0;
		$items = $predis->smembers("e{$this->experiment}:v{$variation}:u");
		foreach($items as $i) {
			$r = call_user_func($test_hypothesis_function, $i);
			if($r == TRUE) $positives++;
		}
		
		return $positives/count($this->getItemsFromVariation($variation));
	}
	
	function getResultsFromAllVariations($test_hypothesis_function) {
		global $predis;
		
		$result = array();
		foreach($this->variations as $k => $v) {
			$result[$k] = $this->getResultsFromVariation($test_hypothesis_function, $k);
		}
		return $result;
	}
	
	function getItemsFromAllVariations() {
		global $predis;
		
		$result = array();
		foreach($this->variations as $k => $v) {
			$result = array_merge($result, $this->getItemsFromVariation($k));
		}
		return $result;
	}
	
	function getCountFromAllVariations() {
		global $predis;
		
		$result = array();
		foreach($this->variations as $k => $v) {
			$result[] = $this->getCountFromVariation($k);
		}
		return array_sum($result);
	}
	
}