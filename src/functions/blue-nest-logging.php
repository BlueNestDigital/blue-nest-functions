<?php
/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (Blue Nest Digital LLC, All rights reserved)
 * Copyright: Copyright 2017 Blue Nest Digital LLC
 */

function logMsg($msg) {
    error_log(logMsgCreate($msg));
}

/**
 * Prints a single message to the command or page in proper formatting.
 * Inputs string outputs sanatized HTML (if needed).  Converts CRLF to html if needed.
 *
 * Logs get a timestamp during display.
 * @param $arg1 string|boolean can be a boolean (will log) or a string (defaults to will not log), if the first is a boolean then the second
 * @param null $arg2 string if arg1 is bool.
 */
function printMsg($str, $logLocation = false, $functionName = null) {
    $logLocation = $logLocation || (defined("PRINT_MSG_LOG_CALL_LOCATIONS") && PRINT_MSG_LOG_CALL_LOCATIONS);
    $msg = logMsgCreate($str, $logLocation, $functionName);
    echo encodeForOutput($msg);
}

function printMsgIf($booleanCondition, $str, $logLocation = false) {
    if($booleanCondition) {
        printMsg($str, $logLocation);
    }
}

function debugPrint() {
    $args = func_get_args();

    $str = "";
    if(count($args) > 1) {
    	$label = array_shift($args);
    	$str .= $label . ": ";
	}

    foreach($args as $arg) {
        $str .= debugVar($arg) . PHP_EOL;
    }
    printMsg("DEBUG INFO: " . $str, false, __FUNCTION__);
}

function logException($e, $extraDebugInfo = []) {
    if(!empty($extraDebugInfo)) {
        echo $extraDebugInfo . PHP_EOL;
    }
    printMsg($e);
}

/**
 * Inputs string outputs sanatized HTML.  Converts CRLF to html if needed.
 *
 * @param $arg1 string|boolean if boolean (if needed), the param allows for timestimps.
 * @param array ...$printfArgs
 */
function printfMsg($arg1, ...$printfArgs) {
    if (is_bool($arg1) && is_string($printfArgs[0])) {
        if ($arg1===true)
            $msg = logMsgCreate(call_user_func_array ("sprintf",$printfArgs));
        else
            $msg =  call_user_func_array ("sprintf",$printfArgs);
    }
    elseif (is_string($arg1) && is_array($printfArgs)) {
        $msg =  call_user_func_array ("sprintf",array_merge (compact($arg1), $printfArgs));
    }
    else {
        die("Incorrect Arguments for printfMsg.".lineBr());
    }

    echo ((php_sapi_name() !== "cli") ? nl2br(htmlentities($msg)) . lineBr() : $msg . lineBr());
}

function logError($msg) {
    printMsg($msg);
    incrementErrorCount();
}

function logMsgCreate($msg, $includeMessageLocation = true, $functionName = null) {
    $logMessage = dateForLogs() . ': ' . $msg;

    if($includeMessageLocation) {
        $caller = findFunctionCallerLocation($functionName);
        if($caller !== null) {
            $logMessage = "[" . $caller . "] " . $logMessage;
        }
    }

    return $logMessage;
}

function getSapiName() {
    return "cli";
    /*
    if(!isset($GLOBALS['BLUE_NEST_CACHE_SAPI_NAME'])) {
        $GLOBALS['BLUE_NEST_CACHE_SAPI_NAME'] = php_sapi_name();
    }

    return $GLOBALS['BLUE_NEST_CACHE_SAPI_NAME'];*/
}

function encodeForOutput($msg) {
    if (getSapiName() == "cli") {
        return $msg . PHP_EOL;
    } else {
        return nl2br(htmlentities($msg))."<br>";
    }
}

function lineBr() {
    if (php_sapi_name() == "cli") {
        return "\n";
    } else {
        return "<br>";
    }
}

/**
 * @param null $functionName
 * @return null|string
 */
function findFunctionCallerLocation($functionName = null) {
    if(function_exists("debug_backtrace")) {
        $backTrace = debug_backtrace();

        foreach($backTrace as $backtraceEntry) {
            if(isset($backtraceEntry["file"]) && $backtraceEntry["file"] === __FILE__) {
                continue;
            }

            if($functionName !== null && $backtraceEntry["function"] !== $functionName) {
                continue;
            }

            $scriptName = basename($backtraceEntry["file"]);
            return $scriptName . ":" . $backtraceEntry["line"];
        }
    }

    return null;
}