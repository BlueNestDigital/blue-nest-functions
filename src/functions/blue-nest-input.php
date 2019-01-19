<?php

/**
 * Creator: Bryan Mayor
 * Company: Blue Nest Digital, LLC
 * License: (Blue Nest Digital LLC, All rights reserved)
 * Copyright: Copyright 2017 Blue Nest Digital LLC
 *
 * Old Function Bellow (Linux only)
 *
 *  function promptYesNo($question) {
 *      $result = null;
 *      while($result === null) {
 *          $answer = readline($question . ': ');
 *          $answer = strtolower($answer);
 *          if(in_array($answer, array('yes', 'y'))) {
 *              $result = true;
 *          } else if(in_array($answer, array('no', 'n'))) {
 *              $result = false;
 *          } else {
 *              echo 'Please type yes or no' . PHP_EOL;
 *          }
 *  }
 * return $result;
 * }
 */

/* This function was created with inspiration and knowledge of:
 * https://stackoverflow.com/questions/5879043/php-script-detect-whether-running-under-linux-or-windows
 * https://github.com/shaneharter/sheldon (GNU) 
 * 
 * It checks for the OS, then offers two options for replies.  
 * Windows = Interactive Mode
 * Linux/Mac/Other = Interactive Shell (also known as redline)
 */
function promptYesNo($question) {
    $result = null;
    //check OS Type for Input Method

    while($result === null) {
        if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            //if Windows use Interactive mode
            printMsg($question);
            $answer = trim(fgets(STDIN));

            if(!is_string($answer)) {
                continue;
            }
        } else {
            //if not windows use readline and interactive shell
            printMsg($question);
            $answer = readline($question . ': ');
        }

        $answer = strtolower($answer);
        if(in_array($answer, array('yes', 'y'))) {
            $result = true;
        } else if(in_array($answer, array('no', 'n'))) {
            $result = false;
        } else if(in_array($answer, array('exit', 'quit'))) {
            exit(1);
        }
    }
    return $result;  //Return True if yes or y, False if n or no.
}

function interactivePromptsAreEnabled() {
	return EnvLoader::get("execution.upload.interactive_prompts_enabled", true);
}

/**
 * @param $prompt
 * @param bool|true $exitOnNo
 * @return bool
 */
function interactivePrompt($prompt, $defaultValue = true, $exitOnNo = false) {
    if(EnvLoader::get("execution.upload.interactive_prompts_enabled")) {
        if(promptYesNo($prompt)) {
            return true;
        } else {
            if($exitOnNo) {
                printMsg("Interactive mode: exiting per request");
                exit();
            } else {
                return false;
            }
        }
    } else {
        return $defaultValue;
    }
}

function continuePrompt($prompt) {
    interactivePrompt($prompt, null, true);
}