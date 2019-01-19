<?php
/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (Blue Nest Digital LLC, All rights reserved)
 * Copyright: Copyright 2017 Blue Nest Digital LLC
 */

function parseUrl($url) {
    $url = trim($url);
    if($url === '') {
        return null;
    }

    $url = str_replace('http://', '', $url);
    $url = str_replace('https://', '', $url);
    $url = str_replace('://', '', $url);
    $url = str_replace('//', '', $url);

    $url = 'http://' . $url;

    /*
        if(strBegins($url, '://')) {
            $url = 'http' . $url;
        } else if(strBegins($url, '//')) {
            $url = 'http:' . $url;
        }
        else if(!strBegins('url', 'http://') && !strBegins('url', 'https://')) {
            $url = 'http://' . $url;
        }
    */
    $parts = parse_url($url);
    if(isset($parts['path'])) {
        $parsedUrl = $parts['host'] . $parts['path'];
    } else {
        $parsedUrl = $parts['host'];
    }
    return $parsedUrl;
}