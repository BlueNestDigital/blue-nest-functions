<?php
/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (Blue Nest Digital LLC, All rights reserved)
 * Copyright: Copyright 2017 Blue Nest Digital LLC
 */

function sendMail($to, string $subject, string $message, bool $verbose = false, $from = null, $extraHeaders = []) {
	$LOG_MAIL_DELAY_THRESHOLD_IN_SECONDS = 10;

	if(empty($to)) {
		throw new RuntimeException("Must specific at least one 'to' email recipient");
	}

    if(!is_array($to)) {
    	$to = explode(",", $to);
    }

    assert(is_array($to));

    if($from === null) {
        if(count($to) > 1) {
            throw new RuntimeException("From address must be provided when using multiple recipients");
        } else {
        	$from = $to[0];
		}
    }

    $to = implode(", ", $to);

    $startTime = time();

    $headers = [
        "From" => $from,
        "Reply-To" => $from
    ];

    $headers = array_merge($headers, $extraHeaders);

    $headerString = "";
    foreach($headers as $header => $headerValue) {
        $headerString .= $header . ": " . $headerValue . "\r\n";
    }

    $additionalParams = "";
    if(!empty($from)) {
        $additionalParams .= "-f" . $from;
    }

    $result = mail($to, $subject, $message, $headerString, $additionalParams);

    if(!$result) {
        echo "PHP mail() returned false (was not accepted for delivery)" . PHP_EOL;
    }

    if($verbose) {
        echo "Sent mail to: " . $to . ", which was " . ($result ? "accepted" : "rejected by outbound mail program") . PHP_EOL;
    }

    if((time() - $startTime) > $LOG_MAIL_DELAY_THRESHOLD_IN_SECONDS) {
        echo "Warning: sendMail() took more than " . $LOG_MAIL_DELAY_THRESHOLD_IN_SECONDS . " seconds" . PHP_EOL;
    }

    return $result;
}

function sendHtmlMail($to, string $subject, string $message, bool $verbose = false, $from = null, $extraHeaders = []) {
	if(!isset($extraHeaders["Content-Type"])) {
		$extraHeaders["Content-Type"] = "text/html; charset=ISO-8859-1";
	}
	return sendMail($to, $subject, $message, $verbose, $from, $extraHeaders);
}