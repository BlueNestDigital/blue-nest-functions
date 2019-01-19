<?php

/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (Blue Nest Digital LLC, All rights reserved)
 * Copyright: Copyright 2017 Blue Nest Digital LLC
 */

class Timer {
    private $startTime;
    private $timeAllowed;
    private $label;
    private $verbose;

    function __construct($label, $timeAllowed, $verbose = false) {
        $this->startTime = time();
        $this->label = $label;
        $this->verbose = $verbose;
    }

    function elapsedSeconds() {
        $elapsed = time() - $this->startTime;
        if($this->verbose) {
            printMsg('Timer "' . $this->label . '" elapsed seconds = ' . $elapsed . PHP_EOL, false);
        }
    }

    function timeIsUp() {
        return $this->elapsedSeconds() > $this->timeAllowed;
    }
}