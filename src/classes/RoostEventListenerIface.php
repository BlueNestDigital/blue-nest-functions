<?php
/**
 * Created by PhpStorm.
 * User: work
 * Date: 9/12/18
 * Time: 16:24
 */

interface RoostEventListenerIface {
	function isInterestedInEvent($event);
	function handleEvent($event);
	function listeningDone();
}