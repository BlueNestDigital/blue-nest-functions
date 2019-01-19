<?php
/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (Blue Nest Digital LLC, All rights reserved)
 * Copyright: Copyright 2017 Blue Nest Digital LLC
 */

function arrayIsEmpty($arr) {
    return count($arr) === 0;
}

function arrayValue($item) {
    if($item instanceof SplFixedArray) {
        $item = $item->toArray();
    }

    if(!is_array($item)) {
        throw new \InvalidArgumentException("Expecting array value: " . debugVar($item));
    }

    return $item;
}

/**
 * array_unique, but also keep the order and reindex
 *
 * @param $arr
 * @return array
 */
function bndArrayUnique($arr) {
    $unique = [];
    foreach($arr as $key=>$val) {
        if(!in_array($val, $unique)) {
            $unique[] = $val;
        }
    }

    return $unique;
}

function bndArrayGroupByFunction($arr, $comparatorFunc) {
    $groupedElems = [];
    for($x = 0; $x<count($arr); $x++) {
        $elemOne = $arr[$x];
        $matchingElements = [$elemOne];

        for($i = $x + 1; $i < count($arr); $i++) {
            $elemTwo = $arr[$i];

            if($comparatorFunc($elemOne, $elemTwo) === 0) {
                $matchingElements[] = $elemTwo;
                unset($arr[$i]);
            }
        }
        $groupedElems[] = $matchingElements;
    }

    return $groupedElems;
}

function arrayToShortString(array $arr) {
    return arrayToString($arr, ', ');
}

function isKeyValueArray($arr) {
    if(!empty($arr)) {
        end($arr);
        $key = key($arr);
        return is_string($key);
    }
    return false;
}

function printArraySummary($arr, $maxLengthInChars = 1000, $return = false) {
    $output = "";
    foreach($arr as $el) {
        if(!empty($output)) {
            $output .= ", ";
        }
        $output .= $el;

        if(strlen($output) >= $maxLengthInChars) {
            $output = substr($output, 0, $maxLengthInChars) . ", ... more ...";
            break;
        }
    }

    $output = "[" . $output . "] (count=" . count($arr) . ")" . PHP_EOL;

    if($return) {
        return $output;
    } else {
        echo $output;
    }
}

function arrayMap($arr, $callback, $preserveKeys = false) {
    if(!isArrayLike($arr)) {
        throw new InvalidArgumentException("Argument 1 must be an array, found: " . getTypeOrClass($arr));
    }

    $resultArray = array();
    foreach($arr as $key=>$val) {
        $newVal = $callback($key, $val);
        if($preserveKeys) {
            $resultArray[$key] = $newVal;
        } else {
            $resultArray[] = $newVal;
        }
    }
    return $resultArray;
}

function arrayMapKeyValue($arr, $callback, $allowOverwritingKeys = false) {
    if(!isArrayLike($arr)) {
        throw new InvalidArgumentException("Argument 1 must be an array, found: " . getTypeOrClass($arr));
    }
    $resultArray = array();
    foreach($arr as $key=>$val) {
        list($newKey, $newVal) = $callback($key, $val);

        if(!$allowOverwritingKeys && array_key_exists($newKey, $resultArray)) {
            throw new RuntimeException("Would have overwritten duplicate key: " . $newKey);
        }

        $resultArray[$newKey] = $newVal;
    }
    return $resultArray;
}

function arrayGetFirstElement($arr) {
    if(empty($arr)) {
        throw new OutOfRangeException("Expecting non empty array");
    }

	reset($arr);
    $firstKey = key($arr);
    reset($arr);
    return $arr[$firstKey];
}

function arrayGetOnlyOneItem($arr) {
    if(count($arr) !== 1) {
        throw new OutOfRangeException("Expecting one array item, saw: " . count($arr) . " " . print_r($arr, true));
    }
    return $arr[0];
}


function retrieveFirstArrayKey($arr) {
    reset($arr);
    $firstKey = key($arr);
    reset($arr);
    return $firstKey;
}

function randomFromArray($arr) {
    return $arr[array_rand($arr)];
}

function isSetAndTrue($array, $key) {
    if(isset($array[$key])) {
        return $array[$key];
    }
    return false;
}

function arrayCheckAndGet($arr, $key, &$data) {
    if(!array_key_exists($key, $arr)) {
        return false;
    }

    $data = $arr[$key];

    return true;
}

function valueOrNull($arr, $key) {
    return valueOrDefault($arr, $key, null);
}

function valueOrDefault($arr, $key, $default = "") {
    if(arrayKeyExists($key, $arr)) {
        return $arr[$key];
    } else {
        return $default;
    }
}

function valueIfSet($arr, $key, &$var) {
    if(isset($arr[$key])) {
        $var = $arr[$key];
        return true;
    }
    return false;
}

function valueIfNotEmpty($arr, $key, &$var) {
    if(isset($arr[$key]) && $arr[$key] != '') {
        $var = $arr[$key];
        return true;
    }
    return false;
}

function valueOrThrow($arr, $key) {
    if(!isArrayLike($arr)) {
        throw new \InvalidArgumentException("valueOrThrow(): 'Array' argument must be an array");
    }
    if($key == null) {
        throw new \InvalidArgumentException("valueOrThrow(): 'Key' argument must not be null");
    }
    if(!arrayKeyExists($key, $arr)) {
        throw new \OutOfBoundsException("valueOrThrow(): Array does not contain key '" . $key . "', keys are: (" . implode(",", array_keys($arr)) . ")");
    }

    return $arr[$key];
}

function arraySmartDisplay(array $arr) {
	$str = implodeKeyValue($arr, ': ',', ');

	if(strlen($str) > 200) {
		$str = implodeKeyValue($arr, ': ',"\n");
	}

	return $str;
}

function implodeKeyValue($keyValueArray, $keyValSeparator = ': ', $lineSeparator = ', ', $niceDisplay = false) {
    $str = '';

    $isKeyValue = isKeyValueArray($keyValueArray);

    foreach($keyValueArray as $key=>$val) {
        if(!empty($str)) {
            $str .= $lineSeparator;
        }

        if(is_array($val)) {
            $val = implodeKeyValue($val, $keyValSeparator, $lineSeparator, $niceDisplay);
        }

        if($niceDisplay) {
            $val = varInspect($val);
        }
        if($isKeyValue) {
            $str .= $key . $keyValSeparator . $val;
        } else {
            $str .= $keyValSeparator . $val;
        }
    }
    return $str;
}

function implodeKeyValuePretty($keyValueArray) {
    return implodeKeyValue($keyValueArray, ": ", PHP_EOL);
}

/**
 * Implode array, getting rid of empty string elements
 *
 * @param $separator
 * @param $arr
 * @return string
 */
function implodePruned($separator, $arr) {
    $strArray = array();
    foreach($arr as $item) {
        if($item !== "") {
            $strArray[] = $item;
        }
    }

    return implode($separator, $strArray);
}

function arrayFlip($arr, $allowDuplicateKeys = false) {
    $inverted = array();
    foreach($arr as $key=>$val) {
        if(!$allowDuplicateKeys && array_key_exists($val, $inverted)) {
            throw new \RuntimeException("Value occurs more than once in array: " . $val);
        }
        $inverted[$val] = $key;
    }

    return $inverted;
}

function arrayFlipAndGroup($arr) {
    $inverted = array();
    foreach($arr as $key=>$val) {
        if(arrayKeyExists($val, $inverted)) {
            if(!is_array($inverted[$val])) {
                $inverted[$val] = array($inverted[$val]);
            }
            $inverted[$val][] = $key;
        }
        $inverted[$val] = $key;
    }

    return $inverted;
}

function arrayToString($arr, $keyValueLineEnd = PHP_EOL, $bracketsOnNewLines= false, $level = 0) {

    $arrayType = 'value';
    if(isKeyValueArray($arr)) {
        $arrayType = 'key-value';
    }

    if($arr === null) {
        return '<null array>' . PHP_EOL;
    }

    if(empty($arr)) {
        return '<empty array>' . PHP_EOL;
    }

    $indent = str_repeat("\t", $level);

    $s = "";
    $s .= $indent . '[';
    if($bracketsOnNewLines) {
        $s .= PHP_EOL;
    };

    $arrString = '';
    foreach($arr as $key=>$val) {
        if($arrString !== '') {
            $arrString .= $keyValueLineEnd;
        }
        if(gettype($val) === 'array') {
            $val = arrayToString($val, $keyValueLineEnd, $bracketsOnNewLines, $level + 1);
        } else if($val === null) {
            $val = 'null';
        }
        if($arrayType === 'key-value') {
            $arrString .= $indent . "\t" . $key . ": " . $val;
        } else {
            $arrString .= $indent . "\t" . $val;
        }
    }

    $s .= $arrString;

    if($bracketsOnNewLines && $keyValueLineEnd !== PHP_EOL) {
        $s .= PHP_EOL;
    }

    $s .= $indent . ']' . PHP_EOL;

    return $s;
}

function arrayContainsValue($arr, $searchFor) {
    foreach($arr as $k=>$v) {
        if($v === $searchFor) {
            return true;
        }
    }

    return false;
}

function arrayIncrement(&$arr, $key, $default = 0) {
    if(!array_key_exists($key, $arr)) {
        $arr[$key] = 0;
    }

    $arr[$key] += 1;
}

function &arrayGetReference(&$arr, $key, $default = null) {
    if(!array_key_exists($key, $arr)) {
        $arr[$key] = $default;
    }

    return $arr[$key];
}

function arrayGetValue($arr, $key, $default = null) {
    if(!array_key_exists($key, $arr)) {
        $arr[$key] = $default;
    }

    return $arr[$key];
}

function arrayAppend(&$arr, $key, $val, $default = []) {
    if(!array_key_exists($key, $arr)) {
        $arr[$key] = $default;
    }

    $arr[$key][] = $val;
}

// BND TODO revisit
/**
 * Filters an array. The builtin array_filter does not reset
 * keys, which can cause unexpected behavior
 *
 * @param $arr
 * @param $acceptFunction
 * @return array
 */
function filterArray($arr, callable $acceptFunction) {
    $filteredArr = [];
    foreach($arr as $i=>$value) {
        if($acceptFunction($value, $i)) {
            $filteredArr[] = $arr[$i];
        }

        unset($arr[$i]);
    }

    return $filteredArr;
}

function checkCountIs($arr, $val, $label = "") {
    if(count($arr) !== $val) {
        if($label !== null) {
            $exceptionMsg = "Checking array count for '" . bndToString($label) . "':";
        } else {
            $exceptionMsg = "Checking array count:";
        }

        throw new \RuntimeException($exceptionMsg . " expecting " . $val . " but found " . count($arr));
    }
}

/**
 * Copy any keys from $arrayKeys in $inputArray to a new array. If inputArray
 * does not have the key set, fill it with the default value
 *
 * @param $arrayKeys
 * @param $inputArray
 * @param string $default
 * @return array
 */
function arrayCopyWithDefault($arrayKeys, &$inputArray, $default = "") {
    $resultArray = [];

    foreach($arrayKeys as $arrayKey) {
        if(isset($inputArray[$arrayKey])) {
            $resultArray[$arrayKey] = $inputArray[$arrayKey];
        } else {
            $resultArray[$arrayKey] = $default;
        }
    }

    return $resultArray;
}

function diffArrays($arr1, $arr2, $ignoredKeysArray = null, $returnOnFirstDiff = false) {
	if(!is_array($arr1) || !is_array($arr2)) {
		throw new RuntimeException("Both arguements must be arrays");
	}

    $diffs = array();

    $seenKeys = array();
    foreach($arr1 as $key1 => $val1) {
        $seenKeys[$key1] = true;

        if($ignoredKeysArray !== null && in_array($key1, $ignoredKeysArray)) {
            continue;
        }

        if(array_key_exists($key1, $arr2)) {
            if($val1 !== $arr2[$key1]) {
                $diffs[$key1] = [
                    "left" => $val1,
                    "right" => $arr2[$key1]
                ];

                if($returnOnFirstDiff) {
                    return $diffs;
                }
            }
        } else {
            $diffs[$key1] = [
                "left" => $val1,
            ];

            if($returnOnFirstDiff) {
                return $diffs;
            }
        }
    }

    foreach($arr2 as $key2 => $val2) {
        if($ignoredKeysArray !== null && in_array($key2, $ignoredKeysArray)) {
            continue;
        }

        if(!array_key_exists($key2, $seenKeys)) {
            $diffs[$key2] = [
                "right" => $val2,
            ];

            if($returnOnFirstDiff) {
                return $diffs;
            }
        }
    }

    return $diffs;
}

function arrayGroupByProperty($arr, $property) {
    $keyedArray = [];
    foreach($arr as $elem) {
    	$newKey = $elem->{$property};

    	if(!array_key_exists($newKey, $keyedArray)) {
    		$keyedArray[$newKey] = [$elem];
		} else {
    		$keyedArray[$newKey][] = $elem;
		}
    }
    return $keyedArray;
}

function arrayMapByProperty($arr, $prop, $onlyValues = false) {
    $keyedArray = [];
    foreach($arr as $elem) {
    	$newKey = $elem->{$prop};

    	if(!$onlyValues && array_key_exists($newKey, $keyedArray)) {
    		throw new RuntimeException("This key already exists: " . $newKey);
		}

		if(!$onlyValues) {
			$keyedArray[$newKey] = $elem;
		} else {
    		$keyedArray[] = $newKey;
		}
    }
    return $keyedArray;
}

function arrayMapByKey($arr, $key) {
    $keyedArray = [];
    foreach($arr as $elem) {
    	$newKey = $elem[$key];

    	if(array_key_exists($newKey, $keyedArray)) {
    		throw new RuntimeException("This key already exists: " . $newKey);
		}

        $keyedArray[$newKey] = $elem;
    }
    return $keyedArray;
}

/**
 * Return an array of $arr where each entry is keyed by
 * pulling $key from each array element.
 *
 * @param $arr
 * @param $key
 * @return array
 * @throws Exception
 */
function arrayMapByKeyDeep($arr, $key) {
    $keyedArray = [];

    foreach($arr as $elem) {
        if(is_array($elem)) {
            $deepElems = arrayMapByKeyDeep($elem, $key);
            foreach($deepElems as $deepKey => $deepElem) {
                if(array_key_exists($deepKey, $keyedArray)) {
                    throw new \Exception("Duplicate key generated: " . $deepKey);
                }
                $keyedArray[$deepKey] = $deepElem;
            }
        } else {
            $keyedArray[$elem[$key]] = $elem;
        }
    }

    return $keyedArray;
}

function arrayContainsSubStringValue($arr, $str, $caseSensitive = false) {
    foreach($arr as $key => $item) {
        if(!$caseSensitive) {
            if(stringContains($item, $str)) {
                return true;
            }
        } else {
            if(stringContainsCase($item, $str)) {
                return true;
            }
        }
    }

    return false;
}

function arrayContainsSubStringKey($arr, $str, $caseSensitive = false) {
    foreach($arr as $key => $item) {
        if(!$caseSensitive) {
            if(stringContains($key, $str)) {
                return true;
            }
        } else {
            if(stringContainsCase($key, $str)) {
                return true;
            }
        }
    }

    return false;
}

function encloseArrayValues($arr, $char) {
    $newArr = array();

    foreach($arr as $key=>$val) {
        $newArr[$key] = $char . $val . $char;
    }

    return $newArr;
}

function arrayCountValueOccurrences($arr, $valueNeedle) {
    $count = 0;
    foreach($arr as $key=>$val) {
        if($val === $valueNeedle) {
            $count += 1;
        }
    }
    return $count;
}

function arrayHasValue($arr, $searchFor) {
    foreach($arr as $it) {
        if($it === $searchFor) {
            return true;
        }
    }

    return false;
}

function arrayPullByFunction($arr, $searchFor, $conditionFunc) {
	foreach($arr as $i => $it) {
		$value = $conditionFunc($it);
        if($searchFor === $value) {
        	unset($arr[$i]);
            return $it;
        }
    }

    return null;
}

function arrayMatchByFunction($arr, $searchFor, $conditionFunc) {
	foreach($arr as $it) {
		$value = $conditionFunc($it);
        if($searchFor === $value) {
            return $it;
        }
    }

    return false;
}

function arrayHasCondition($arr, $conditionFunc) {
    foreach($arr as $it) {
        if($conditionFunc($it)) {
            return true;
        }
    }

    return false;
}

function arrayCountValues($arr) {
    $vals = array();
    foreach($arr as $key=>$val) {
        if(!isset($vals[$val])) {
            $vals[$val] = 1;
        } else {
            $vals[$val] += 1;
        }
    }

    return $vals;
}

function arrayContainsAll($needles, $arr) {
    foreach($needles as $needle) {
        if(!in_array($needle, $arr)) {
            return false;
        }
    }

    return true;
}

function arrayContainsAny($needles, $arr) {
    foreach($needles as $needle) {
        if(in_array($needle, $arr)) {
            return true;
        }
    }

    return false;
}

function identityMap($arr) {
    return array_combine($arr, $arr);
}

function arrayPushAll(&$arr1, $arr2) {
    foreach($arr2 as $val) {
        $arr1[] = $val;
    }
}

function arrayCount($arr) {
    return array_map(function($item) {
        return count($item);
    }, $arr);
}

function bndflattenArray($arr, $retainKeys = false) {
    $res = [];
    foreach($arr as $key=>$val) {
        if(is_array($val)) {
            $resNested = bndflattenArray($val, $retainKeys);
            arrayPushAll($res, $resNested);
        } else {
            if($retainKeys) {
                $res[$key] = $val;
            } else {
                $res[] = $val;
            }
        }
    }

    return $res;
}

function arrayMergeInPlace(&$arr1, $arr2) {
    foreach($arr2 as $elem2) {
        $arr1[] = $elem2;
    }

    return $arr1;
}

function chunkApply(array $arr, int $chunkSize, callable $callback) {
	$chunks = array_chunk($arr, $chunkSize);
	foreach($chunks as $chunk) {
		$callback($chunk);
	}
}