@extends('layouts.app')

@section('title', 'Document Library')

@section('content')
<section class="section">
    <div class="container">
        <h1 class="section-title">Document Library</h1>
        <p class="section-subtitle">
            @if(isset($search))
                Search results for "<strong>{{ $search }}</strong>"
            @else
                Browse free document templates and resources.
            @endif
        </p>

        <!-- Search and filter -->
        <div class="flex gap-2 mb-4" style="flex-wrap:wrap;align-items:center">
            <form action="{{ route('library.search') }}" method="GET" style="flex:1;max-width:400px;display:flex;gap:.5rem">
                <input type="search" name="q" id="library-search" class="form-control"
                       placeholder="Search library…" aria-label="Search library"
                       value="{{ $search ?? '' }}" required>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>

            @if($categories->isNotEmpty())
            <form action="{{ route('library.index') }}" method="GET">
                <select name="category" id="category-filter" class="form-control" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" @selected($cat === $category)>{{ ucfirst($cat) }}</option>
                    @endforeach
                </select>
            </form>
            @endif
        </div>

        <!-- Library items -->
        @if($items->isNotEmpty())
        <div class="grid grid-3" style="margin-top:2rem">
            @foreach($items as $item)
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">{{ $item->title }}</h3>
                    <p class="card-text">{{ \Illuminate\Support\Str::limit($item->description, 100) }}</p>
                    <div class="card-meta" style="display:flex;justify-content:space-between;align-items:center;margin-top:1rem;font-size:.85rem">
                        <span>{{ $item->download_count }} downloads</span>
                        <span style="color:var(--success)">{{ $item->is_free ? 'Free' : 'Premium' }}</span>
                    </div>
                    <a href="{{ route('library.show', ['id' => $item->id, 'slug' => \Illuminate\Support\Str::slug($item->title)]) }}"
                       class="btn btn-outline btn-sm mt-2 btn-block">View Details</a>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $items->links() }}
        </div>
        @else
        <div style="text-align:center;padding:3rem 1rem">
            <p style="font-size:1.1rem;color:var(--text-muted)">No documents found.</p>
            @if(isset($search))
            <p>We couldn't find any documents matching your search. We've noted your request!</p>
            <a href="{{ route('library.index') }}" class="btn btn-link">← Back to library</a>
            @endif
        </div>
        @endif
    </div>
</section>
@endsection
