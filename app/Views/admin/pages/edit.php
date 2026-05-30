<h1>Edit Page</h1>

<form method="post" action="<?= site_url('admin/pages/update/' . $page['id']) ?>">
    <p>
        <label>Page Key</label><br>
        <input type="text" name="page_key" value="<?= esc($page['page_key']) ?>" required>
    </p>

    <p>
        <label>Status</label><br>
        <select name="status">
            <option value="draft" <?= $page['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
            <option value="publish" <?= $page['status'] === 'publish' ? 'selected' : '' ?>>Publish</option>
        </select>
    </p>

    <hr>
    <h2>Indonesia</h2>

    <p><label>Slug</label><br><input type="text" name="slug_id" value="<?= esc($translations['id']['slug'] ?? '') ?>" required></p>
    <p><label>Title</label><br><input type="text" name="title_id" value="<?= esc($translations['id']['title'] ?? '') ?>" required></p>
    <p><label>Meta Title</label><br><input type="text" name="meta_title_id" value="<?= esc($translations['id']['meta_title'] ?? '') ?>"></p>
    <p><label>Meta Description</label><br><textarea name="meta_description_id"><?= esc($translations['id']['meta_description'] ?? '') ?></textarea></p>
    <p><label>Content</label><br><textarea name="content_id" rows="10"><?= esc($translations['id']['content'] ?? '') ?></textarea></p>

    <hr>
    <h2>English</h2>

    <p><label>Slug</label><br><input type="text" name="slug_en" value="<?= esc($translations['en']['slug'] ?? '') ?>" required></p>
    <p><label>Title</label><br><input type="text" name="title_en" value="<?= esc($translations['en']['title'] ?? '') ?>" required></p>
    <p><label>Meta Title</label><br><input type="text" name="meta_title_en" value="<?= esc($translations['en']['meta_title'] ?? '') ?>"></p>
    <p><label>Meta Description</label><br><textarea name="meta_description_en"><?= esc($translations['en']['meta_description'] ?? '') ?></textarea></p>
    <p><label>Content</label><br><textarea name="content_en" rows="10"><?= esc($translations['en']['content'] ?? '') ?></textarea></p>

    <p>
        <button type="submit">Update</button>
    </p>
</form>