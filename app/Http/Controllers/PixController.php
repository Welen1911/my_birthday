<?php

namespace App\Http\Controllers;

use App\Models\PixOption;
use Illuminate\View\View;

class PixController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('pixs.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('pixs.create');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PixOption $pixOption): View
    {
        return view('pixs.edit', compact('pixOption'));
    }
}
