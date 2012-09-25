<?php

// Retrieve JSON interests, transform them as needed and
// cache into GLOBALS variable 

class CacheGlobalTest extends CacheGlobal {
	function __construct($name = 'CacheGlobalTest', $language_code, Debug &$Debug = null) {
		parent::__construct($name);
		$this->cache = EvaluatorTest::get_interests_text_by_language2($language_code);
		//		$this->cache = array_slice($this->cache, 0, 50);
		$this->T = new TransformerEN($name, $this, $this, $Debug);
		$this->T->prepare();
	}
}

?>