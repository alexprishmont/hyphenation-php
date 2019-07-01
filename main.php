<?php
    require_once("scan.php");
    require_once("algorithm.php");


    print("Hey!\nEnter your word: ");
    $word = getUserInput();

    $patterns = getTextFileData('tex-hyphenation-patterns.txt');

    hyphenate($word, $patterns);