<?php
namespace Core\API;

use Controllers\PatternController;
use Core\DI\Container;

class APILoader
{
    private $patternController;
    private $wordController;

    public function __construct(PatternController $pc)
    {
        $this->patternController = $pc;
    }
}