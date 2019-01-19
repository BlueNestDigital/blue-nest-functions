<?php
/**
 * Created by PhpStorm.
 * User: work
 * Date: 9/11/18
 * Time: 15:23
 */

class ReportingEventListener implements RoostEventListenerIface
{
	/** @var ReportingEvent[] */
	private $eventsToReport = [];

	function __construct() {
	}

	function isInterestedInEvent($event) {
		return $event instanceof ReportingEvent;
	}

	function handleEvent($event) {
		$this->eventsToReport[] = $event;
	}

	function listeningDone() {
		echo "Done listening " . PHP_EOL;
		$reportGenerator = new ReportGenerator();
		$reportContent = $reportGenerator->generateReport($this->eventsToReport);

		assert(sendHtmlMail("bryan@bluenestdigital.com", "Script report generated", $reportContent, true));
	}
}