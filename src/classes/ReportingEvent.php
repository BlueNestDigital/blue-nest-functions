<?php
/**
 * Created by PhpStorm.
 * User: work
 * Date: 9/12/18
 * Time: 16:28
 */

class ReportingEvent extends RoostEvent
{
	private $reportName;

	function __construct($data, $reportName = null) {
		parent::__construct($data);
		$this->reportName = $reportName;
	}

	function hasReportName() {
		return $this->reportName !== null;
	}

	function getReportName() {
		return $this->reportName;
	}
}