<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class SectionController extends Controller
{
    public function section(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $sections = Section::select('id', 'name', 'status')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->latest('id')
            ->cursorPaginate($this->perPage($request))
            ->withQueryString()
            ->through(fn (Section $section) => $section->only(['id', 'name', 'status']));
        $title = "Section Page";
        return view('admin.sections.section', compact('sections', 'title'));
    }

    public function store(Request $request)
    {
        Section::create($this->validateSection($request));
        $this->clearSectionCache();
        return response()->json(['message' => 'Section saved successfully.'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Section $section)
    {
        return response()->json(['record' => $section]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Section $section)
    {
        return response()->json(['record' => $section]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Section $section)
    {
        $section->update($this->validateSection($request, $section));
        $this->clearSectionCache();
        return response()->json(['message' => 'Section updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Section $section)
    {
        $section->delete();
        $this->clearSectionCache();
        return response()->json(['message' => 'Section deleted successfully.']);
    }

    public function updateStatus(Section $section)
    {
        $section->update(['status' => ! $section->status]);
        $this->clearSectionCache();
        return response()->json(['message' => 'Section status updated successfully.']);
    }

    private function validateSection(Request $request, ?Section $section = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('sections', 'name')->ignore($section)],
            'status' => ['required', 'boolean'],
        ], [
            'name.unique' => 'This section name already exists.',
        ]);
    }

    private function clearSectionCache(): void
    {
        Cache::forget('api.sections-with-categories.v1');
    }

}
