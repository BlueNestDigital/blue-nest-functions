<?php
/**
 * Created by PhpStorm.
 * User: work
 * Date: 9/12/18
 * Time: 16:38
 */

class RoostEvent
{
	private $data = null;

	function __construct($data) {
		if(is_string($data)) {
			$data = ["message" => $data];
		}
		$this->data = $data;
	}

	function getData() {
		return $this->data;
	}
}