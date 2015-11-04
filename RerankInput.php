<?php

class RerankInput {

	private $checkbox;
	private $range;
	private $value;

	function __construct( $checkbox = false, $range = null, $value = null ) {
		$this->checkbox = $checkbox;
		if( $range == null )
			$this->range = ( MAX_WEIGHT / 2 );
		else
			$this->range = $range;
		$this->value = $value;
	}

	public function getCheckbox() {
		return $this->checkbox;
	}

	public function getRange() {
		return $this->range;
	}

	public function getValue() {
		if( $this->value instanceof \Datetime )
			return $this->value->format('Y-m-d');
		return $this->value;
	}

}