<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UIController extends Controller
{
    public function index()
    {
        return redirect()->route('login');
    }
}
