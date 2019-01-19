<?php
/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (Blue Nest Digital LLC, All rights reserved)
 * Copyright: Copyright 2017 Blue Nest Digital LLC
 */

function strIsEmpty($str, bool $allowWhitespace = false) {
    if(!is_string($str)) {
        throw new InvalidArgumentException("str argument must be a string: " . debugVar($str));
    }

    if(!$allowWhitespace) {
        $str = trim($str);
    }

    return $str === "";
}

function strIsEmptyOrNull($str, bool $allowWhitespace = false) {
    if($str === null) {
        return true;
    }
    if(!is_string($str)) {
        throw new InvalidArgumentException("str argument must be a string: " . debugVar($str));
    }
    return strIsEmpty($str, $allowWhitespace);
}

function strBegins($str, $needle) {
    return substr($str, 0, strlen($needle)) === $needle;
}

function strEnds($str, $needle) {
    return substr($str, strlen($str) - strlen($needle)) === $needle;
}

function stringContains($str, $searchFor) {
    if(strpos(strtolower($str), strtolower($searchFor)) !== false) {
        return true;
    }
    return false;
}

function stringContainsAny($str, array $searchForArray) {
    foreach($searchForArray as $searchFor) {
        if(strpos(strtolower($str), strtolower($searchFor)) !== false) {
            return true;
        }
    }
    return false;
}

function stringsEqualInsensitive($str1, $str2) {
    return strcasecmp($str1, $str2) === 0;
}

function stringContainsCase($str, $searchFor) {
    if(strpos($str, $searchFor) !== false) {
        return true;
    }
    return false;
}

function doubleQuote($str) {
    return enclose($str, '"');
}

function singleQuote($str) {
    return enclose($str, "'");
}

function enclose($str, $char) {
    return $char . $str . $char;
}

function isEmptyOrWhitespace($str) {
    return $str === "" || ctype_space($str);
}

/**
 * Split as close to length as possible while breaking on whole words
 *
 * examples:
 * strSplitOnWord("The cat in the hat", 6) = ["The", "cat in the hat"]
 * strSplitOnWord("The cat in the hat", 7) = ["The cat", "in the hat"]
 *
 * @param $str
 * @param $length
 * @return array
 */
function strSplitOnWord($str, $length, $delimiters = [" "]) {
    if(strlen($str) <= $length) {
        return [$str, ""];
    }

    $length += 1;
    // str is bigger than length
    // start at length and walk back until we find a delimiter
    while(--$length > 0) {
        $char = $str[$length];
        if(in_array($char, $delimiters)) {
            $sub = substr($str, 0, $length);
            $rest = substr($str, $length + 1);
            return [$sub, $rest];
        }
    }

    return ["", $str];
}

function strSplitOnWordWithEnclosures($str, $length, $delimiters = [" "]) {
    if(strlen($str) <= $length) {
        return [$str, ""];
    }

    $enclosures = [
        "(" => ")",
        "{" => "}",
        "[" => "]"
    ];

    $allEnclosures = array_merge(array_keys($enclosures), array_values($enclosures));

    $length += 1;
    $insideEnclosure = null;

    // str is bigger than length
    while(--$length > 0) {
        $char = $str[$length];

        if(in_array($char, $allEnclosures)) {
            if($insideEnclosure !== null) {
                if($insideEnclosure !== $char) {
                    throw new \Exception("Unbalanced enclosure in string: " . $str);
                } else {
                    $insideEnclosure = null;
                }
            } else {
                if(!array_key_exists($char, $enclosures)) {
                    throw new \Exception("End enclosure in string with start enclosure: " . $str);
                }
                $insideEnclosure = $enclosures[$char];
            }
        }

        if(!$insideEnclosure && in_array($char, $delimiters)) {
            $sub = substr($str, 0, $length);
            $rest = substr($str, $length + 1);
            return [$sub, $rest];
        }
    }

    return ["", $str];
}

function removeBom($str) {
    return strRemoveFromBeginning($str, UTF8_BOM);
}

function strRemoveFromBeginning($str, $needle, $strict = false) {
    $needleLength = strlen($needle);
    if(substr($str, 0, $needleLength) === $needle) {
        return substr($str, $needleLength);
    } else {
        if($strict) {
            throw new \Exception("String " . singleQuote($str) . " does not begin with " . singleQuote($needle));
        }
        return $str;
    }
}

function strRemoveFromEnd($str, $needle, $strict = false) {
    $needleLength = strlen($needle);
    if(substr($str, 0, (-1 * $needleLength)) === $needle) {
        return substr($str, $needleLength);
    } else {
        if($strict) {
            throw new \Exception("String " . singleQuote($str) . " does not end with " . singleQuote($needle));
        }
        return $str;
    }
}

function bndAbbreviate($str, $maxLength, $abbreviateSuffix = "...") {
    if(strlen($str) > $maxLength) {
        $str = substr($str, 0, $maxLength) . $abbreviateSuffix;
    }
    return $str;
}

function bndTruncate($str, $maxLength) {
    return bndAbbreviate($str, $maxLength, " (...truncated...)");
}

function bndToString($var) {
    if(is_string($var)) {
        return $var;
    } else if(is_array($var)) {
        return implodeKeyValuePretty($var);
    } else {
        return (string)$var;
    }
}