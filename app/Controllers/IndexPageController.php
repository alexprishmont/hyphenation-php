<?php
declare(strict_types=1);

namespace NXT\Controllers;

use NXT\Core\Controller;
use NXT\Core\View;

class IndexPageController extends Controller
{
    public function index(): void
    {
        echo View::create('index');
    }

}