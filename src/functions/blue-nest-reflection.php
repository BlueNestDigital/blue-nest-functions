<?php
/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (Blue Nest Digital LLC, All rights reserved)
 * Copyright: Copyright 2017 Blue Nest Digital LLC
 */

function reflectGetTypesForClass($className) {
    $reflectionInstance = new ReflectionClass($className);

    $types = [];
    foreach($reflectionInstance->getProperties() as $refProperty) {
        preg_match("#@var\s+([A-Za-z0-9]+)(\[\])?\s+#", $refProperty->getDocComment(), $matches);
        $type = $matches[1];

        $isArray = count($matches) > 2;

        $types[$refProperty->getName()] = [
            'type' => $type,
            'array' => $isArray
        ];
    }

    return $types;
}

function reflectDescribeClosure(closure $callback){
    $reflectionFunction = new ReflectionFunction($callback);

    $file = $reflectionFunction->getFileName();
    $startLine = $reflectionFunction->getStartLine();
    $endLine = $reflectionFunction->getEndLine();
    $fileContents = file_get_contents($file);
    $fileContents = explode(PHP_EOL, $fileContents);

    $sourceLength = $endLine - $startLine;
    $functionSource = array_slice($fileContents, $startLine - 1, $sourceLength + 1);

    $description = "Function source: " . PHP_EOL . print_r($functionSource, true);
    return $description;
}

function diffObjects($objectLeft, $objectRight) {
    $diffs = array();

    if(get_class($objectLeft) !== get_class($objectRight)) {
        throw new \InvalidArgumentException("Objects for comparison must be the same class");
    }
    $reflection = new \ReflectionClass($objectLeft);
    $properties = $reflection->getProperties();

    foreach($properties as $reflectionProperty) {
        $propertyName = $reflectionProperty->getName();

        $reflectionProperty->setAccessible(true);
        $valLeft = $reflectionProperty->getValue($objectLeft);
        $valRight = $reflectionProperty->getValue($objectRight);

        if($valLeft !== $valRight) {
            $diffs[$propertyName] = [
                "left" => $valLeft,
                "right" => $valRight
            ];
        }
    }

    return $diffs;
}

function bndClassGetShortName($className) {
    return (new \ReflectionClass($className))->getShortName();
}
