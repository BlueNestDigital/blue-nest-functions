<?php

/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (Blue Nest Digital LLC, All rights reserved)
 * Copyright: Copyright 2017 Blue Nest Digital LLC
 */
class ScriptTimer {
    private static $startTimes = [];

    public static function begin($timerName = 'main') {
        $startTime = time();
        static::$startTimes[$timerName] = $startTime;
        printfMsg("Script started at: %s", date('Y-m-d H:i:s', $startTime));
    }

    public static function end($timerName = 'main') {
        $endTime = time();
        $elapsedSeconds = ($endTime - static::$startTimes[$timerName]);
        printfMsg("Total elapsed time: %s seconds", $elapsedSeconds);
        printfMsg("Script ended at: %s", date('Y-m-d H:i:s', $endTime));
    }
}