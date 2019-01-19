<?php
/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (Blue Nest Digital LLC, All rights reserved)
 * Copyright: Copyright 2017 Blue Nest Digital LLC
 */

class ProgressPrinter {

    private $totalRecords = null;
    private $currentRecord = 0;
    private $printEveryNumRecords;
    private $label = "";

    function __construct($totalRecords, $printEveryNumRecords, $label = "") {
        $this->totalRecords = $totalRecords;
        $this->printEveryNumRecords = $printEveryNumRecords;
        $this->label = $label;
    }

    function increment($replacementValuesArray = null) {
        $this->currentRecord += 1;

        if($this->printEveryNumRecords === 1 || $this->currentRecord % $this->printEveryNumRecords === 1 || $this->currentRecord === $this->totalRecords) {
            $output = 'Progress: ' . $this->currentRecord;
            if($this->totalRecords !== null) {
                $output .= '/' . $this->totalRecords;
            }
            if($this->label !== "") {
                $labelText = $this->label;
                if(is_array($replacementValuesArray)) {
                    foreach($replacementValuesArray as $replacementValue) {
                        $labelText = str_replace_first("{}", $replacementValue, $labelText);
                    }
                }
                $output = $output . ": " . $labelText;
            }

            printMsg($output );
        }
    }

    /**
     * @return ProgressPrinter
     */
    static function builder() {
        return (new ReflectionClass(__CLASS__))->newInstanceWithoutConstructor();
    }

    function printEvery($val) {
        $this->printEveryNumRecords = $val;
        return $this;
    }

    function totalRecords($val) {
        $this->totalRecords = $val;
        return $this;
    }

    function currentRecords($val=null) {
        if (!is_null($val)) $this->currentRecord = $val;
        return $this->currentRecord;
    }

    function label($val) {
        $this->label = $val;
        return $this;
    }
}