<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Instrument;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InstrumentController extends Controller
{
    public function index()
    {
        $instruments = Instrument::withCount('courses')->orderBy('name')->get();
        return view('admin.instruments.index', compact('instruments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:instruments,name'],
            'icon' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['icon'] = $validated['icon'] ?: 'music-note-beamed';

        Instrument::create($validated);

        return redirect()->route('admin.instruments.index')->with('success', 'Instrument added successfully.');
    }

    public function update(Request $request, Instrument $instrument)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('instruments')->ignore($instrument->id)],
            'icon' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['icon'] = $validated['icon'] ?: 'music-note-beamed';

        $instrument->update($validated);

        return redirect()->route('admin.instruments.index')->with('success', 'Instrument updated successfully.');
    }

    public function destroy(Instrument $instrument)
    {
        if ($instrument->courses()->exists()) {
            return back()->with('error', 'Cannot delete instrument with linked courses.');
        }

        $instrument->delete();

        return redirect()->route('admin.instruments.index')->with('success', 'Instrument deleted successfully.');
    }
}
