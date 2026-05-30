<h1>CMS Pages</h1>

<p><a href="<?= site_url('admin/pages/create') ?>">Add New Page</a></p>

<?php if (session()->getFlashdata('success')): ?>
    <p><?= esc(session()->getFlashdata('success')) ?></p>
<?php endif; ?>

<table border="1" cellpadding="10" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Page Key</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
    <?php foreach ($pages as $page): ?>
        <tr>
            <td><?= esc($page['id']) ?></td>
            <td><?= esc($page['page_key']) ?></td>
            <td><?= esc($page['status']) ?></td>
            <td>
                <a href="<?= site_url('admin/pages/edit/' . $page['id']) ?>">Edit</a> |
                <a href="<?= site_url('admin/pages/delete/' . $page['id']) ?>" onclick="return confirm('Delete this page?')">Delete</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>