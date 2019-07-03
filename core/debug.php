<?php

function logtofile($logfile, $content) {
    file_put_contents($logfile, "");
    file_put_contents($logfile, $content);
}