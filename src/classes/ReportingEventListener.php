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
	private $emailsToNotify = [];
	private $emailSubject = "Script report generated";

	function __construct($notificationEmails, $emailSubject = null) {
		$this->emailsToNotify = $notificationEmails;

		if($emailSubject !== null) {
			$this->emailSubject = $emailSubject;
		}
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

		foreach($this->emailsToNotify as $email) {
			assert(sendHtmlMail($email, $this->emailSubject, $reportContent, true));
		}
	}
}