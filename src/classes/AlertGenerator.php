<?php
/**
 * Creator => Bryan Mayor
 * Company => Blue Nest Digital, LLC
 * License => (Blue Nest Digital LLC, All rights reserved)
 * Copyright => Copyright 2018 Blue Nest Digital LLC
 */

class AlertGenerator
{
    public static function getAlertDirectory() {
        $alertDirectory = getenv("ALERT_DIRECTORY");

        if($alertDirectory !== false) {
            return $alertDirectory;
        }

        return "stored-alerts";
    }

    public static function generateAlert($message) {
        $timestamp = time();
        $alertData = [
            "message" => $message,
            "was-sent-to" => [],
            "timestamp" => $timestamp,
            "is-processed" => false
        ];

        $i = 0;

        $alertDirectory = static::getAlertDirectory();
        if(!file_exists($alertDirectory)) {
            mkdir($alertDirectory);
        }

        do {
            $alertFileName = $alertDirectory . "/" . "alert-" . $timestamp . "." . $i . ".json";
            $i += 1;
            if($i > 10) {
                throw new \RuntimeException("Could not find filename to place alert: " . $alertFileName);
            }
        } while(file_exists($alertFileName));

        file_put_contents($alertFileName, json_encode($alertData, JSON_PRETTY_PRINT));
        echo "Wrote alert to file: " . $alertFileName . PHP_EOL;
    }
}

AlertGenerator::generateAlert("Some alert happened!");