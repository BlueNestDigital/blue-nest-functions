<?php

use phpseclib\Crypt\Hash;
use Roost\Collections\HashMap;

/**
 * Created by PhpStorm.
 * User: work
 * Date: 9/12/18
 * Time: 17:01
 */

class ReportGenerator
{
	/**
	 * @param $data ReportingEvent[]
	 */
	public function generateReport($data) {
		$reportGroups = [
			"by-report-name" => new HashMap(),
			"by-columns-used" => new HashMap()
		];

		foreach($data as $reportingEvent) {
			$data = $reportingEvent->getData();

			if($reportingEvent->hasReportName()) {
				$reportGroup = $reportingEvent->getReportName();
				$reportGroups["by-report-name"]->append($reportGroup, $reportingEvent);
			} else {
				$reportGroup = implode("-", array_keys($data));
				$reportGroups["by-columns-used"]->append($reportGroup, $reportingEvent);
			}
		}

		/** @var ReportingEvent[] $reportEvents */
		$reportTables = [];

		$orderedReportsToProcess = ["by-report-name", "by-columns-used"];
		foreach($orderedReportsToProcess as $reportType) {
			foreach($reportGroups[$reportType] as $reportGroup => $reportEvents) {
				$str = "";

				if($reportType === "by-report-name") {
					$str .= "<h1>" . $reportEvents[0]->getReportName() . "</h1>";
				}

				$str .= "<table class='report-table' style='border: 1px solid black'>";

				$headers = array_keys($reportEvents[0]->getData());

				$str .= "<tr class='table-header'>";
				foreach($headers as $header) {
					$str .= "<th>" . $header . "</th>";
				}
				$str .= "</tr>";

				foreach($reportEvents as $rowCount => $reportEvent) {
					if($rowCount % 2 === 0) {
						$rowAttributes = " " . "style='background-color: lightgray'";
					} else {
						$rowAttributes = " " . "style='background-color: white'";
					}
					$str .= "<tr" . $rowAttributes . " class='table-row'>";

					$eventData = $reportEvent->getData();
					foreach($headers as $header) {
						$str .= "<td>" . $eventData[$header] . "</td>";
					}
					$str .= "</tr>";
				}

				$str .= "</table>";
				$reportTables[] = $str;
			}
		}

		$styles = "<style>";
		$styles .= ".report-table {border: 1px solid black}" . PHP_EOL;
		$styles .= "</style>";

		return $styles . PHP_EOL . implode("<hr>", $reportTables);
	}
}