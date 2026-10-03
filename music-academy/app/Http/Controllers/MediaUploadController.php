<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MediaUploadController extends Controller
{
    /**
     * Upload an image from the rich text editor (Quill) and return the public URL.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp,svg|max:5120',
        ]);

        $file = $request->file('image');
        $directory = public_path('uploads/editor');

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true, true);
        }

        $filename = 'editor_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return response()->json([
            'success' => true,
            'url' => asset('uploads/editor/' . $filename),
        ]);
    }
}
