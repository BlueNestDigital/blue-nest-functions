<?php
/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (Blue Nest Digital LLC, All rights reserved)
 * Copyright: Copyright 2017 Blue Nest Digital LLC
 */

function jsonPretty($obj) {
    return jsonEncode($obj, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}

function jsonEncode($obj, $flags) {
    $res = json_encode($obj, $flags);
    if($res === false) {
        throw new \RuntimeException("Could not json encode data: " . print_r($obj, true));
    }
    return $res;
}

/**
 * @param $obj
 * @return array
 */
function jsonDecode($obj, $assoc = true) {
    $allRows = json_decode($obj, $assoc);
    if(!isVarType($allRows, 'array')) {
        $jsonError = json_last_error_msg();
        throw new \RuntimeException("Could not parse raw data as json, error message: " . $jsonError);
    }
    return $allRows;
}