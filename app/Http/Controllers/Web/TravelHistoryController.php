<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class TravelHistoryController extends Controller
{
    /**
     * Display the travel history view
     */
    public function index()
    {
        return view('travel-history');
    }
} 