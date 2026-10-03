@extends('layouts.app')

@section('title', 'Academy Journal & Masterclass Blog — ' . setting('site_name', 'Harmonia Music Academy'))

@section('content')

<!-- Blog Header -->
<section class="hero-gradient text-white text-center position-relative py-5">
    <div class="container position-relative z-1 py-lg-4">
        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3 border border-gold" style="background: rgba(245, 158, 11, 0.1);">
            <i class="bi bi-journal-richtext text-gold"></i>
            <span class="text-gold fw-semibold small text-uppercase" style="letter-spacing: 0.08em;">Conservatory Publications</span>
        </div>

        <h1 class="display-4 font-serif fw-bold mb-3 mx-auto" style="max-width: 800px;">
            The Virtuoso Journal & Pedagogy Insights
        </h1>

        <p class="lead text-muted mx-auto mb-4" style="max-width: 650px; font-weight: 300;">
            Essays on classical interpretation, practice methodologies, harmonic analysis, and faculty masterclasses.
        </p>

        <!-- Search Bar -->
        <div class="mx-auto mb-3" style="max-width: 520px;">
            <form action="{{ route('blog.index') }}" method="GET" class="d-flex gap-2">
                @if(request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                <div class="input-group">
                    <span class="input-group-text bg-surface text-muted border-secondary"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control bg-surface text-white border-secondary" placeholder="Search repertoire, techniques, composer essays..." value="{{ request('q') }}">
                    <button class="btn btn-gold" type="submit">Search</button>
                </div>
            </form>
        </div>

        <!-- Category Pills -->
        <div class="d-flex justify-content-center flex-wrap gap-2 mt-4">
            <a href="{{ route('blog.index', array_filter(request()->except('category', 'page'))) }}" class="btn btn-sm {{ !request('category') ? 'btn-gold' : 'btn-outline-secondary text-white border-secondary' }} rounded-pill px-3">
                All Articles
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('blog.index', array_merge(request()->except('category', 'page'), ['category' => $cat])) }}" class="btn btn-sm {{ request('category') === $cat ? 'btn-gold' : 'btn-outline-secondary text-white border-secondary' }} rounded-pill px-3">
                    {{ $cat }}
                </a>
            @endforeach
        </div>

        <!-- Popular Tags Cloud -->
        @if(isset($popularTags) && count($popularTags) > 0)
            <div class="d-flex align-items-center justify-content-center flex-wrap gap-2 mt-3 pt-2">
                <span class="text-gold small fw-bold"><i class="bi bi-tags-fill me-1"></i> Tags:</span>
                @foreach($popularTags as $t)
                    <a href="{{ route('blog.index', array_merge(request()->except('tag', 'page'), ['tag' => $t])) }}" class="badge {{ request('tag') === $t ? 'bg-gold text-dark' : 'bg-surface-elevated text-gold border border-secondary' }} text-decoration-none py-1 px-2" style="font-size: 0.72rem;">
                        #{{ $t }}
                    </a>
                @endforeach
                @if(request('tag'))
                    <a href="{{ route('blog.index', request()->except('tag', 'page')) }}" class="badge bg-danger text-white text-decoration-none py-1 px-2" style="font-size: 0.72rem;">
                        <i class="bi bi-x me-1"></i> Clear #{{ request('tag') }}
                    </a>
                @endif
            </div>
        @endif
    </div>
</section>

<!-- Blog Catalog Grid -->
<section class="py-5" style="background-color: var(--bg-surface);">
    <div class="container py-3">

        @if(request('tag') || request('category') || request('q'))
            <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom border-secondary flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="text-white small fw-bold">Active Filters:</span>
                    @if(request('category'))
                        <span class="badge bg-gold text-dark">{{ request('category') }}</span>
                    @endif
                    @if(request('tag'))
                        <span class="badge bg-gold text-dark"><i class="bi bi-tag-fill me-1"></i> #{{ request('tag') }}</span>
                    @endif
                    @if(request('q'))
                        <span class="badge bg-surface-elevated text-white border border-secondary">"{{ request('q') }}"</span>
                    @endif
                </div>
                <a href="{{ route('blog.index') }}" class="text-danger small text-decoration-none">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset All Filters
                </a>
            </div>
        @endif

        @if($posts->count() > 0)
            <div class="row g-4">
                @foreach($posts as $post)
                    <div class="col-md-6 col-lg-4">
                        <div class="card card-glass h-100 overflow-hidden d-flex flex-column">
                            <div class="position-relative">
                                <a href="{{ route('blog.show', $post) }}">
                                    <img src="{{ $post->coverUrl() }}" 
                                         alt="{{ $post->title }}" 
                                         class="w-100" 
                                         style="height: 220px; object-fit: cover;"
                                         onerror="this.onerror=null;this.src='{{ asset('images/courses/piano.svg') }}';">
                                </a>
                                <span class="position-absolute top-0 start-0 m-3 badge bg-gold text-dark text-uppercase fw-bold" style="font-size: 0.72rem;">
                                    {{ $post->category }}
                                </span>
                                <span class="position-absolute bottom-0 end-0 m-2 badge bg-dark bg-opacity-75 text-muted border border-secondary" style="font-size: 0.7rem;">
                                    <i class="bi bi-clock me-1 text-gold"></i> {{ $post->read_time_minutes }} min read
                                </span>
                            </div>

                            <div class="card-body p-4 d-flex flex-column flex-grow-1">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <img src="{{ $post->author->avatarUrl() }}" alt="{{ $post->author->name }}" class="rounded-circle" width="22" height="22" style="object-fit: cover;">
                                    <small class="text-muted">{{ $post->author->name }}</small>
                                    <small class="text-secondary ms-auto" style="font-size: 0.75rem;">
                                        {{ $post->published_at ? $post->published_at->format('M d, Y') : $post->created_at->format('M d, Y') }}
                                    </small>
                                </div>

                                <h5 class="font-serif fw-bold text-white mb-2">
                                    <a href="{{ route('blog.show', $post) }}" class="text-white text-decoration-none hover-gold">
                                        {{ $post->title }}
                                    </a>
                                </h5>

                                <p class="text-muted small mb-3 flex-grow-1" style="line-height: 1.6;">
                                    {{ Str::limit($post->excerpt ?? strip_tags($post->body), 115) }}
                                </p>

                                @if(count($post->tags_list) > 0)
                                    <div class="d-flex flex-wrap gap-1 mb-3">
                                        @foreach($post->tags_list as $t)
                                            <a href="{{ route('blog.index', ['tag' => $t]) }}" class="badge bg-surface-elevated text-gold border border-secondary text-decoration-none" style="font-size: 0.65rem;">
                                                #{{ $t }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="pt-3 border-top border-secondary d-flex justify-content-between align-items-center">
                                    <a href="{{ route('blog.show', $post) }}" class="text-gold text-decoration-none small fw-semibold">
                                        Read Masterclass Essay <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                    <span class="text-muted small" style="font-size: 0.75rem;">
                                        <i class="bi bi-chat-text text-gold me-1"></i> {{ $post->approvedComments()->count() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-5 d-flex justify-content-center">
                {{ $posts->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-journal-x fs-1 text-muted mb-3 d-block"></i>
                <h4 class="text-white font-serif">No Articles Found</h4>
                <p class="text-muted small">No publications match your filter criteria.</p>
                <a href="{{ route('blog.index') }}" class="btn btn-outline-gold btn-sm">Clear Search & Filters</a>
            </div>
        @endif

    </div>
</section>

@endsection
