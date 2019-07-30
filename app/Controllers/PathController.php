<?php
declare(strict_types=1);

namespace NXT\Controllers;

use NXT\Core\Controller;
use NXT\Core\View;

class PathController extends Controller
{
    public function pathNotFound()
    {
        echo View::create('404');
    }
}