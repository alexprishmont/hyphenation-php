<?php
    require_once("scan.php");
    require_once("algorithm.php");

    require_once("debug.php");


    print("Hey!\nEnter your word: ");
    $word = getUserInput();

    $patternsList = getTextFileData('tex-hyphenation-patterns.txt');

    // Start measuring execution time
    $start_time = microtime(true);

    $patterns = getPatternsForWord($word, $patternsList);

    $logcontent = $patterns;
    logtofile('patterns.txt', $logcontent);

    echo hyphenate($word, $patterns);

    // End measuring execution time
    $end_time = microtime(true);
    $execution_time = ($end_time - $start_time);

    // Print result
    echo "\nExecution time of hyphenation algorithm = $execution_time sec.\n";