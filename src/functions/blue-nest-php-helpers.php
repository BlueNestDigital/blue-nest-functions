<?php
/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (Blue Nest Digital LLC, All rights reserved)
 * Copyright: Copyright 2018 Blue Nest Digital LLC
 */

if(!function_exists("bndExit")) {
    function bndExit($status = "")
    {
        echo "Exit called with status: " . $status . PHP_EOL;
        exit($status);
    }
}