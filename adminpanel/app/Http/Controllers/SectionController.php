<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SectionController extends Controller
{
    public function section()
    {
        $isCached = Cache::has('active_section');
        $sections = Cache::remember('active_section', 86400, function () {
            return Section::select('id', 'name', 'status')->get()->toArray();
        });
        $title = "Section Page";
        return view('admin.sections.section', [
            'status' => true,
            'source' => $isCached ? 'Redis Cache' : 'Database',
            'data'   => $sections,
            'title'  => $title
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|boolean',
        ]);
        Section::create([
            'name' => $request->name,
            'status' => $request->status,
        ]);
        Cache::forget('active_section');
        return view('admin.sections.section')->with('success', 'Section created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Section $section)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Section $section)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Section $section)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Section $section)
    {
        //
    }
}
