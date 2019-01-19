<?php
/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (Blue Nest Digital LLC, All rights reserved)
 * Copyright: Copyright 2017 Blue Nest Digital LLC
 */

function arrayKeyExists($key, $arr) {
    if(array_key_exists($key, $arr)) {
        return true;
    }
    if($arr instanceof ArrayAccess) {
        return $arr->offsetExists($key);
    }

    return false;
}

/**
 * array_key_exists with type checks for key and array
 *
 * @param string $key
 * @param array $arr
 * @return bool
 */
function arrayStringKeyExists(string $key, array $arr) {
	return array_key_exists($key, $arr);
}
/*
function arrayishKeyExists(string $key, array $arr) {
    if(array_key_exists($key, $arr)) {
        return true;
    }
    if($arr instanceof ArrayAccess) {
        return $arr->offsetExists($key);
    }

    return false;
}
*/
function isArrayLike($arr) {
    return is_array($arr) || $arr instanceof ArrayAccess;
}

function assertArrayValue($arr, $key, $expectedValue) {
    if(!arrayKeyExists($key, $arr)) {
        throw new \Exception('Array is missing key: ' . $key);
    }

    $val = $arr[$key];
    if($val !== $expectedValue) {
        throw new \Exception('Array value for ' . $key . ' is wrong, expected ' . varDumpData($expectedValue) . ' vs actual ' . varDumpData($val));
    }
}

function assertArrayUniformLength($arr) {
    $firstRowCount = null;
    $rowNumber = 0;
    foreach($arr as $row) {
        $rowNumber += 1;

        if($firstRowCount === null) {
            $firstRowCount = count($row);
            continue;
        }

        if(count($row) !== $firstRowCount) {
            throw new Exception('Rows are not uniform length, first row has ' . $firstRowCount . ' elements, row ' . $rowNumber . ' has ' . count($row));
        }
    }
}

function assertObjectValue($obj, $key, $expectedValue) {
    if(!property_exists($obj, $key)) {
        throw new \Exception('Object is missing property: ' . $key);
    }

    $val = $obj->{$key};
    if($val !== $expectedValue) {
        throw new \Exception('Array value for ' . $key . ' is wrong, expected ' . varDumpData($expectedValue) . ' vs actual ' . varDumpData($val));
    }
}

function checkInt($v) {
    return checkVarType($v, 'integer');
}

function checkString($v) {
    return checkVarType($v, 'string');
}

function checkVarType($v, $type, $exceptionText = null) {
    if(!isVarType($v, $type)) {
        if($exceptionText === null) {
            $exceptionText = 'Expecting type \'' . $type . '\' but found type \'' . getTypeOrClass($v) . '\'';
        }
        throw new Exception($exceptionText);
    }
    return $v;
}

function isVarType($v, $type) {
    if(strtolower(getTypeOrClass($v)) !== strtolower($type)) {
        return false;
    }
    return true;
}

function getInt($val) {
    if(!is_int($val)) {
        throw new \InvalidArgumentException('Value is not an integer: ' . debugVar($val));
    }
    return $val;
}

function getFloat($val) {
    if(!is_float($val)) {
        throw new \InvalidArgumentException('Value is not a float: ' .  debugVar($val));
    }
    return $val;
}

function getNumber($val) {
    if(!is_int($val) && !is_float($val) && !is_double($val)) {
        throw new \InvalidArgumentException('Value is not a number (int/float/double): ' .  debugVar($val));
    }
    return $val;
}

function integerValue($val) {
    if(!is_numeric($val)) {
        throw new \InvalidArgumentException('Value is not numeric: ' .  debugVar($val));
    }

    if(is_int($val)) {
        return $val;
    }

    return intval($val);
}

function moneyFloatValue($val) {
    $val = str_replace(",", "", $val);
    return floatValue($val);
}

function floatValue($val) {
    if(!is_numeric($val)) {
        throw new \InvalidArgumentException('Value is not numeric: ' .  debugVar($val));
    }

    if(is_float($val)) {
        return $val;
    }

    return floatval($val);
}

function checkStrNotEmpty($val) {
    if(!is_string($val) || empty($val)) {
        throw new \InvalidArgumentException('Value is not a string or is empty: ' . $val);
    }
    return $val;
}

function checkStr($val) {
    if(!is_string($val)) {
        throw new \InvalidArgumentException('Value is not a string: ' . $val);
    }
    return $val;
}

/**
 * Assert all array elements are of the same type
 *
 * @param $arr
 * @param $collectionItemClass
 * @return mixed
 * @throws Exception
 */
function assertArrayIsUniform($arr, $collectionItemClass) {
    if(!is_array($arr)) {
        throw new \Exception('assertArrayIsUniform: Expected array as array input, got: ' . getTypeOrClass($arr));
    }
    if(!is_string($collectionItemClass)) {
        throw new \Exception('assertArrayIsUniform: Expected string as class name, got: ' . getTypeOrClass($arr));
    }
    array_map(function ($item) use($collectionItemClass) {
        if(!getTypeOrClass($item) === $collectionItemClass) {
            throw new \Exception('assertArrayIsUniform: Expecting array of ' . $collectionItemClass . ', but received  item of type: ' . getTypeOrClass($item));
        }
    }, $arr);
    return $arr;
}

function getTypeOrClass($v) {
    $type = gettype($v);
    if($type === 'object') {
        $type = get_class($v);
    }
    return $type;
}

function booleanTrueFalse($val) {
    if(!is_bool($val)) {
        throw new \InvalidArgumentException("Boolean value expected");
    }
    return ($val === true) ? "true" : "false";
}

function booleanOnOff($val) {
    if(!is_bool($val)) {
        throw new \InvalidArgumentException("Boolean value expected");
    }
    return $val ? "on" : "off";
}

function booleanYesNo($val) {
    if(!is_bool($val)) {
        throw new \InvalidArgumentException("Boolean value expected");
    }
    return $val ? "yes" : "no";
}

function assertEqualUsingGetterMethods($obj1, $obj2) {
    $class = new ReflectionClass(get_class($obj1));
    $methods = $class->getMethods();

    /** @var ReflectionMethod $method */
    foreach($methods as $method) {
        if(strBegins($method->getName(), "get")) {
            $val1 = $method->invoke($obj1);
            $val2 = $method->invoke($obj2);

            if($val1 !== $val2) {
                throw new \Exception("Values not equal for " . $method->getName() . ": " . $val1 . " vs " . $val2);
            }
        }
    }
}

function describeIfNotEmpty($label, $str) {
    return !empty($str) ? $label . $str : "";
}

function isObjectOneOf($object, $classList) {
    foreach($classList as $class) {
        if(is_a($object, $class)) {
            return true;
        }
    }

    return false;
}

function printableObjectDescription($instance, $condenseEmptyProperties = true) {
    try {
        $reflection = new \ReflectionClass($instance);
        $properties = $reflection->getProperties();

        $propertyValues = [];
        foreach($properties as $reflectionProperty) {
            $reflectionProperty->setAccessible(true);

            $propertyName = $reflectionProperty->getName();

            $val = $reflectionProperty->getValue($instance);
            $propertyValues[$propertyName] = $val;
        }

        $description = get_class($instance) . " Object (";

        if($condenseEmptyProperties) {
            $emptyProperties = [];
            $nullProperties = [];

            foreach($propertyValues as $propertyName => $propertyValue) {
                if(is_null($propertyValue)) {
                    $nullProperties[] = $propertyName;
                    unset($propertyValues[$propertyName]);
                } else if($propertyValue === "") {
                    $emptyProperties[] = $propertyName;
                    unset($propertyValues[$propertyName]);
                }
            }

        }

        $description .= print_r($propertyValues, true) . PHP_EOL;

        if(!empty($nullProperties)) {
            $description .= "Null properties: " . implode(", ", $nullProperties) . PHP_EOL;
        }
        if(!empty($emptyProperties)) {
            $description .= "Empty properties: " . implode(", ", $emptyProperties) . PHP_EOL;
        }

        $description .= ")" . PHP_EOL;

        return $description;
    } catch(\Exception $e) {
        printMsg("Warning: " . "Exception in " . "printObjectDescription()");
        error_log($e);
        return [
            "fallback description" => print_r($instance, true)
        ];
    }

}