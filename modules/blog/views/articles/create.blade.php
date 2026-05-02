@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Créer un nouvel article</h1>

    <form action="{{ route('blog.articles.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label for="title" class="form-label">Titre</label>
            <input type="text" name="title" id="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="excerpt" class="form-label">Extrait</label>
            <textarea name="excerpt" id="excerpt" rows="3" class="form-control"></textarea>
        </div>

        <div class="mb-3">
            <label for="content" class="form-label">Contenu</label>
            <div id="quill-editor" style="height: 300px;"></div>
            <textarea name="content" id="content" class="d-none"></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Catégories</label>
            @foreach($categories as $category)
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="categories[]" value="{{ $category->id }}" id="category-{{ $category->id }}">
                <label class="form-check-label" for="category-{{ $category->id }}">
                    {{ $category->name }}
                </label>
            </div>
            @endforeach
        </div>

        <div class="mb-3">
            <label class="form-label">Tags</label>
            @foreach($tags as $tag)
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="tags[]" value="{{ $tag->id }}" id="tag-{{ $tag->id }}">
                <label class="form-check-label" for="tag-{{ $tag->id }}">
                    {{ $tag->name }}
                </label>
            </div>
            @endforeach
        </div>

        <div class="mb-3">
            <label for="featured_image" class="form-label">Image mise en avant</label>
            <input type="file" name="featured_image" id="featured_image" class="form-control">
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Statut</label>
            <select name="status" id="status" class="form-select">
                <option value="draft">Brouillon</option>
                <option value="published">Publié</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="published_at" class="form-label">Date de publication</label>
            <input type="datetime-local" name="published_at" id="published_at" class="form-control">
        </div>

        <div class="bg-light p-3 rounded mb-3">
            <h4>Analyse SEO</h4>
            <div id="seo-score" class="fw-bold">0/100</div>
            <ul id="seo-suggestions" class="list-unstyled"></ul>
        </div>

        <button type="submit" class="btn btn-primary">Publier</button>
    </form>
</div>

<!-- Quill.js -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
    const quill = new Quill('#quill-editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ 'header': 1 }, { 'header': 2 }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'script': 'sub'}, { 'script': 'super' }],
                [{ 'indent': '-1'}, { 'indent': '+1' }],
                [{ 'direction': 'rtl' }],
                [{ 'size': ['small', false, 'large', 'huge'] }],
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'font': [] }],
                [{ 'align': [] }],
                ['clean'],
                ['link', 'image', 'video']
            ]
        }
    });

    quill.on('text-change', function() {
        document.getElementById('content').value = quill.root.innerHTML;
        analyzeSeo();
    });

    function analyzeSeo() {
        const title = document.getElementById('title').value;
        const content = quill.root.innerHTML;
        const score = Math.min(100, Math.floor(Math.random() * 100));
        const suggestions = [];

        if (title.length < 50 || title.length > 60) {
            suggestions.push("Le titre doit contenir entre 50 et 60 caractères.");
        }
        if (content.length < 300) {
            suggestions.push("Le contenu doit contenir au moins 300 mots.");
        }

        document.getElementById('seo-score').textContent = `${score}/100`;
        document.getElementById('seo-suggestions').innerHTML = suggestions.map(s => `<li>${s}</li>`).join('');
    }
</script>
@endsection
