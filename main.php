<?php
    require_once("debug.php"); // logtofile function

    require_once("scan.php");
    require_once("algorithm.php");



    print("Hey!\nEnter your word: ");
    $word = getUserInput();

    $patternsList = getTextFileData('tex-hyphenation-patterns.txt');

    // Start measuring execution time
    $start_time = microtime(true);

    $patterns = getPatternsForWord($word, $patternsList);

    // debug [adds all patterns which are needed for the given word]
    $logcontent = $patterns;
    logtofile('patterns.txt', $logcontent);

    print(hyphenate($word, $patterns));

    // End measuring execution time
    $end_time = microtime(true);
    $execution_time = ($end_time - $start_time);

    // Print result
    echo "\nExecution time of hyphenation algorithm = $execution_time sec.\n";