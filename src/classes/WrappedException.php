<?php
/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (Blue Nest Digital LLC, All rights reserved)
 * Copyright: Copyright 2018 Blue Nest Digital LLC
 */

class WrappedException extends Exception
{
    private $extraDebugData;

    function __construct(string $message = "", int $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    static function from(\Exception $e, $message = null) {
        if($message === null);{
            $message = $e->getMessage();
        }
        return new WrappedException($message, 0, $e);
    }

    function debugging($extraDebugData) {
        $this->extraDebugData = $extraDebugData;
        return $this;
    }
}