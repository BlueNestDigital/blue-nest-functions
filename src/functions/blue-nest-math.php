<?php
/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (Blue Nest Digital LLC, All rights reserved)
 * Copyright: Copyright 2017 Blue Nest Digital LLC
 */

function compareFloat($a, $b, $epsilon) {
    if(abs($a - $b) > $epsilon) {
        return 1;
    } else if(abs($b - $a) > $epsilon) {
        return -1;
    }

    return 0;
}

function floatsEqual($a, $b, $epsilon) {
    return compareFloat($a, $b, $epsilon) === 0;
}