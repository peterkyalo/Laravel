<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\InstrumentResource;
use App\Models\Instrument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class AdminInstrumentController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $instruments = Instrument::withCount('courses')->latest()->get();
        return InstrumentResource::collection($instruments);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:instruments,name'],
            'icon' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $instrument = Instrument::create($validated);

        return response()->json([
            'message' => 'Instrument created successfully.',
            'instrument' => new InstrumentResource($instrument),
        ], 201);
    }

    public function update(Request $request, Instrument $instrument): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:instruments,name,' . $instrument->id],
            'icon' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $instrument->update($validated);

        return response()->json([
            'message' => 'Instrument updated successfully.',
            'instrument' => new InstrumentResource($instrument),
        ]);
    }

    public function destroy(Instrument $instrument): JsonResponse
    {
        if ($instrument->courses()->exists()) {
            return response()->json(['message' => 'Cannot delete instrument linked to active courses.'], 422);
        }

        $instrument->delete();

        return response()->json([
            'message' => 'Instrument deleted successfully.',
        ]);
    }
}
