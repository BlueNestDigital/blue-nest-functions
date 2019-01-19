<?php

/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (Blue Nest Digital LLC, All rights reserved)
 * Copyright: Copyright 2017 Blue Nest Digital LLC
 */
class PreparedStatementQueryWriter
{
    private $bindParamMethod = null;
    private $rowInsertQuery;
    private $stmt;
    private $bindParamTypes = [];
    private $valArray;

    function prepare($rowInsertQuery) {
        if((!$this->stmt = $mysqli->prepare($rowInsertQuery))) {
            die("Could not prepare query: " . $mysqli->error);
        }
        $stmtReflection = new ReflectionClass('mysqli_stmt');
        $this->bindParamMethod = $stmtReflection->getMethod("bind_param");
    }

    function add($value, $paramType) {
        $this->valArray[] = $value;
        $this->bindParamTypes .= $paramType;
    }

    function bind() {
        $argArrayForReflection = array(&$this->bindParamTypes);
        foreach($this->valArray as &$theVal) {
            $argArrayForReflection[] = &$theVal;
        }

        $this->bindParamMethod->invokeArgs($this->stmt, $argArrayForReflection);
    }

    function run() {
        $stmt->execute();
    }
}