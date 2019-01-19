<?php
/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (Blue Nest Digital LLC, All rights reserved)
 * Copyright: Copyright 2018 Blue Nest Digital LLC
 */

require_once "blue-nest-arrays.php";

$arr = [
  [
    "a" => 1,
    "b" => "foo"
],
[
    "a" => 9,
    "b" => "bar"
],
[
    "a" => 1,
    "b" => "baz"
],
[
    "a" => 1,
    "b" => "bat"
],
    [
    "a" => 1,
    "b" => "bat"
],
    [
    "a" => 9,
    "b" => "bar",
        "c" => "cat"
],
];

$res = bndArrayUniqueByFunction($arr, function($elemOne, $elemTwo) {
    if($elemOne["a"] === $elemTwo["a"]
        && $elemOne["b"] === $elemTwo["b"]
    ) {
        return 0;
    }

    return 1;
});

var_dump($res);