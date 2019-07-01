<?php
    require_once("scan.php");
    require_once("algorithm.php");


    print("Hey!\nEnter your word: ");
    $word = getUserInput();

    $list = getTextFileData('tex-hyphenation-patterns.txt');

    remakeWord($word, $list);
