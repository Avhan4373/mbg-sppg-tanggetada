<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PageController extends Controller
{
    public function guide(): View
    {
        return view('pages.guide');
    }
}
