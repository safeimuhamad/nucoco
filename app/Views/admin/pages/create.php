
<head>
    <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<h1>Create Page</h1>
<?php if (session()->getFlashdata('errors')): ?>
    <div style="color:red;">
        <?php foreach (session()->getFlashdata('errors') as $error): ?>
            <p><?= esc($error) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<div class="container mt-5">
<form method="post" action="<?= site_url('admin/pages/store') ?>">
    <p>
        <label>Page Key</label><br>
        <input type="text" name="page_key" required>
    </p>

    <p>
        <label>Status</label><br>
        <select name="status">
            <option value="draft">Draft</option>
            <option value="publish">Publish</option>
        </select>
    </p>

    <hr>
    <h2>Indonesia</h2>

    <p><label>Slug</label><br><input type="text" class="form-control" name="slug_id" required></p>
    <p><label>Title</label><br><input type="text" class="form-control" name="title_id" required></p>
    <p><label>Meta Title</label><br><input type="text" class="form-control" name="meta_title_id"></p>
    <p><label>Meta Description</label><br><textarea class="form-control" name="meta_description_id"></textarea></p>
    <p><label>Content</label><br><textarea class="form-control" name="content_id" rows="10"></textarea></p>

    <hr>
    <h2>English</h2>

    <p><label>Slug</label><br><input type="text" class="form-control"  name="slug_en" required></p>
    <p><label>Title</label><br><input type="text" class="form-control" name="title_en" required></p>
    <p><label>Meta Title</label><br><input type="text" class="form-control" name="meta_title_en"></p>
    <p><label>Meta Description</label><br><textarea class="form-control" name="meta_description_en"></textarea></p>
    <p><label>Content</label><br><textarea class="form-control" name="content_en" rows="10"></textarea></p>

    <p>
        <button type="submit">Save</button>
    </p>
</form>
</div>
<script>
function slugify(text) {
    return text
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/\s+/g, '-');
}

// Indonesia
document.querySelector('[name="title_id"]').addEventListener('input', function() {
    document.querySelector('[name="slug_id"]').value = slugify(this.value);
});

// English
document.querySelector('[name="title_en"]').addEventListener('input', function() {
    document.querySelector('[name="slug_en"]').value = slugify(this.value);
});
tinymce.init({
    selector: 'textarea[name="content_id"], textarea[name="content_en"]',
    height: 300,
    menubar: false,
    plugins: 'lists link image table code',
    toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code'
});
</script>