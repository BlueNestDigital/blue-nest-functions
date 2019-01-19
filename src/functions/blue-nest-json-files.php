<?php
/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (Blue Nest Digital LLC, All rights reserved)
 * Copyright: Copyright 2018 Blue Nest Digital LLC
 */

/**
 * Helper for saving state in files as JSON
 * @param $file
 * @param $arr
 * @return int
 */
function filePutState($file, $arr) {
    if(count($arr)) {
        $arr['timestamp'] = time();
    }
    return filePutJson($file, $arr);
}

/**
 * Retrieve saved JSON state data from a file
 * @param $file
 * @return mixed|null
 */
function fileGetState($file, $defaultValue = null) {
    $data = fileGetJson($file);
    return $data;
}

function filePutJson($file, $contentArray, $jsonOptions = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) {
    $encodedContent = json_encode($contentArray, $jsonOptions);
    return file_put_contents($file, $encodedContent);
}

function fileGetJson($file, $defaultValue = null) {
    if(!file_exists($file)) {
        return $defaultValue;
    }

    $c = file_get_contents($file);
    if($c === false) {
        throw new \RuntimeException("Could not read data from file: " . $file);
    }

    $decoded = json_decode($c, true);

    if($decoded === null) {
        throw new \RuntimeException("Could not json_decode data from file: " . $file);
    }

    return $decoded;
}