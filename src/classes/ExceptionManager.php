<?php
/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (Blue Nest Digital LLC, All rights reserved)
 * Copyright: Copyright 2018 Blue Nest Digital LLC
 */

class ExceptionManager
{
    private static $exceptionCountByClass = [];

    /**
     * @param $exceptionInstance
     * @return mixed
     * @throws Exception
     */
    public static function logException($exceptionInstance, $maxAllowedExceptions = null) {
        $className = get_class($exceptionInstance);
        if(!array_key_exists($className, static::$exceptionCountByClass)) {
            static::$exceptionCountByClass[$className] = 0;
        }
        static::$exceptionCountByClass[$className] += 1;

        if($maxAllowedExceptions !== null && $maxAllowedExceptions <= static::$exceptionCountByClass[$className]) {
            trigger_error("Too many exceptions occurred, max allowed = " . $maxAllowedExceptions, E_USER_ERROR);
        }

        return $exceptionInstance;
    }
}