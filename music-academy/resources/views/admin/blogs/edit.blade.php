@extends('layouts.dashboard')

@section('title', 'Edit Publication — ' . $blog->title)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="font-serif text-white fw-bold mb-0">Edit Publication</h2>
        <span class="text-muted small">Update article analysis, category, or cover photography.</span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('blog.show', $blog) }}" target="_blank" class="btn btn-outline-light btn-sm border-secondary">
            <i class="bi bi-eye me-1"></i> View Live
        </a>
        <a href="{{ route('admin.blogs.index') }}" class="btn btn-outline-secondary text-white btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Articles
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card card-solid p-4 p-md-5">
            <form action="{{ route('admin.blogs.update', $blog) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <label class="form-label text-white small fw-bold">Article Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control bg-surface text-white border-secondary" value="{{ old('title', $blog->title) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-white small fw-bold">Publication Category <span class="text-danger">*</span></label>
                        <select name="category" class="form-select bg-surface text-white border-secondary" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ old('category', $blog->category) == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-white small fw-bold">Short Excerpt / Catalog Preview</label>
                    <textarea name="excerpt" rows="2" class="form-control bg-surface text-white border-secondary">{{ old('excerpt', $blog->excerpt) }}</textarea>
                </div>

                <!-- Rich Text Editor for Body -->
                <div class="mb-4">
                    <label class="form-label text-white small fw-bold d-flex justify-content-between align-items-center">
                        <span>Article Content & Analysis <span class="text-danger">*</span></span>
                        <span class="badge bg-gold text-dark small"><i class="bi bi-pen-fill me-1"></i> Rich Text Formatter</span>
                    </label>
                    <textarea name="body" rows="12" class="form-control bg-surface text-white border-secondary richtext">{{ old('body', $blog->body) }}</textarea>
                </div>

                <!-- Cover Image Upload with Live Preview & Existing Image Display -->
                <div class="row g-3 mb-4">
                    <div class="col-md-8">
                        <label class="form-label text-white small fw-bold">Cover Artwork</label>

                        @if($blog->cover_image)
                            <div class="mb-2 p-2 rounded-2 bg-surface-elevated border border-secondary d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $blog->coverUrl() }}" alt="Current Cover" height="48" class="rounded border border-secondary" style="object-fit: cover;">
                                    <div>
                                        <small class="text-white d-block fw-semibold">Current Cover Image</small>
                                        <small class="text-muted font-monospace" style="font-size: 0.7rem;">{{ $blog->cover_image }}</small>
                                    </div>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remove_cover_image" value="1" id="removeCover">
                                    <label class="form-check-label text-danger small" for="removeCover">Remove</label>
                                </div>
                            </div>
                        @endif

                        <input type="file" name="cover_image" class="form-control bg-surface text-white border-secondary image-preview-input" accept="image/*" data-preview-target="#coverPreviewBox">
                        <small class="text-muted">Upload a new image to replace current cover (JPG, PNG, WEBP, max 3MB).</small>

                        <!-- Live Image Preview Container -->
                        <div id="coverPreviewBox" class="mt-3 d-none">
                            <div class="p-3 rounded-3 bg-surface-elevated border border-gold d-inline-flex flex-column align-items-start gap-2">
                                <span class="badge bg-gold text-dark small fw-bold"><i class="bi bi-eye-fill me-1"></i> New Image Preview Before Saving</span>
                                <img src="" alt="Preview" class="preview-img rounded border border-secondary" style="max-height: 200px; max-width: 100%; object-fit: cover;">
                                <div class="d-flex align-items-center gap-3">
                                    <small class="preview-info text-secondary font-monospace"></small>
                                    <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 cancel-preview-btn">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-white small fw-bold">Tags / Keywords</label>
                        <div class="input-group">
                            <span class="input-group-text bg-surface-elevated text-gold border-secondary"><i class="bi bi-tags-fill"></i></span>
                            <input type="text" name="tags" class="form-control bg-surface text-white border-secondary" placeholder="e.g. Bach, Counterpoint, Violin" value="{{ old('tags', $blog->tags) }}">
                        </div>
                        <small class="text-muted">Comma-separated tags for filtering.</small>
                    </div>

                    <div class="col-md-12">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label text-white small fw-bold">Estimated Read Time (Minutes)</label>
                                <input type="number" name="read_time_minutes" class="form-control bg-surface text-white border-secondary" value="{{ old('read_time_minutes', $blog->read_time_minutes) }}" min="1" max="60">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-check form-switch mb-4 p-3 rounded-2 bg-surface-elevated border border-secondary">
                    <input class="form-check-input ms-0 me-3" type="checkbox" role="switch" id="isPublished" name="is_published" value="1" {{ old('is_published', $blog->is_published) ? 'checked' : '' }}>
                    <label class="form-check-label text-white fw-bold" for="isPublished">
                        Published to Public Conservatory Journal
                    </label>
                    <small class="d-block text-muted">When unchecked, this article remains saved as a private draft.</small>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.blogs.index') }}" class="btn btn-outline-secondary text-white">Cancel</a>
                    <button type="submit" class="btn btn-gold px-4">
                        <i class="bi bi-check2-circle me-1"></i> Update Publication
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
