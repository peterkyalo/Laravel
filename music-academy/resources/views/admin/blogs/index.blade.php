@extends('layouts.dashboard')

@section('title', 'Manage Academy Publications & Articles')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-gold text-dark fw-bold"><i class="bi bi-journal-richtext me-1"></i> Publications</span>
            <span class="text-gold fw-bold small text-uppercase" style="letter-spacing: 0.08em;">Academy Journal</span>
        </div>
        <h2 class="font-serif text-white fw-bold mb-0">Conservatory Blog & Articles</h2>
        <span class="text-muted small">Publish essays, masterclass insights, and academy announcements for public visitors.</span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.blogs.comments.index') }}" class="btn btn-outline-warning btn-sm position-relative">
            <i class="bi bi-chat-left-dots me-1"></i> Comments
            @php $pendingCount = \App\Models\BlogComment::pending()->count(); @endphp
            @if($pendingCount > 0)
                <span class="badge bg-danger rounded-pill ms-1">{{ $pendingCount }}</span>
            @endif
        </a>
        <a href="{{ route('blog.index') }}" target="_blank" class="btn btn-outline-light btn-sm border-secondary">
            <i class="bi bi-box-arrow-up-right me-1"></i> Public Blog
        </a>
        <a href="{{ route('admin.blogs.create') }}" class="btn btn-gold btn-sm">
            <i class="bi bi-plus-lg me-1"></i> New Publication
        </a>
    </div>
</div>

<div class="card card-solid p-4">
    <!-- Filter Bar -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <form action="{{ route('admin.blogs.index') }}" method="GET" class="d-flex gap-2">
                <input type="text" name="q" class="form-control form-control-sm bg-surface text-white border-secondary" placeholder="Search by title..." value="{{ request('q') }}">
                @if(request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                <button type="submit" class="btn btn-gold btn-sm px-3">Search</button>
            </form>
        </div>
        <div class="col-md-6 d-flex justify-content-md-end gap-2 flex-wrap">
            <a href="{{ route('admin.blogs.index') }}" class="btn btn-sm {{ !request('category') ? 'btn-gold' : 'btn-outline-secondary text-white border-secondary' }}">
                All ({{ $posts->total() }})
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('admin.blogs.index', ['category' => $cat]) }}" class="btn btn-sm {{ request('category') === $cat ? 'btn-gold' : 'btn-outline-secondary text-white border-secondary' }}">
                    {{ $cat }}
                </a>
            @endforeach
        </div>
    </div>

    @if($posts->count() > 0)
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0">
                <thead class="text-muted small">
                    <tr>
                        <th>Article</th>
                        <th>Category</th>
                        <th>Author</th>
                        <th>Status</th>
                        <th>Read Time</th>
                        <th>Published Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($posts as $post)
                        <tr>
                            <td style="max-width: 300px;">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ $post->coverUrl() }}" alt="Cover" class="rounded border border-secondary" width="50" height="40" style="object-fit: cover;">
                                    <div>
                                        <div class="fw-semibold text-white text-truncate" style="max-width: 240px;">{{ $post->title }}</div>
                                        <small class="text-muted font-monospace" style="font-size: 0.72rem;">/blog/{{ $post->slug }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-surface-elevated text-gold border border-secondary">{{ $post->category }}</span>
                            </td>
                            <td>
                                <small class="text-white">{{ $post->author->name }}</small>
                            </td>
                            <td>
                                @if($post->is_published)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Published</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-muted border border-secondary">Draft</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">{{ $post->read_time_minutes }} min</small>
                            </td>
                            <td>
                                <small class="text-muted">{{ $post->published_at ? $post->published_at->format('M d, Y') : '—' }}</small>
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('blog.show', $post) }}" target="_blank" class="btn btn-sm btn-outline-secondary text-white" title="Preview Public Page">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.blogs.edit', $post) }}" class="btn btn-sm btn-outline-gold" title="Edit Article">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.blogs.destroy', $post) }}" method="POST" onsubmit="return confirm('Delete this publication permanently?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Article">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $posts->links() }}
        </div>
    @else
        <div class="text-center py-5 text-muted">
            <i class="bi bi-journal-x fs-1 d-block mb-2"></i>
            <h5>No Articles Found</h5>
            <p class="small">Get started by creating your first academy publication.</p>
            <a href="{{ route('admin.blogs.create') }}" class="btn btn-gold btn-sm">Create Publication</a>
        </div>
    @endif
</div>
@endsection
