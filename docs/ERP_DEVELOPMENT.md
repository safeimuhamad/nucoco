# ERP Development Notes

This project is still a custom PHP application. Keep new ERP modules close to the existing admin pattern while using the shared foundation below.

## Shared Foundation

- App config: `config/app.php`
- Database config: `config/database.php`
- Bootstrap/helpers: `includes/bootstrap.php`
- Database helpers: `includes/database.php`
- Admin auth/permission helpers: `admin/includes/auth.php`

## URL Helpers

Use these instead of hardcoding domains:

```php
site_base_url();
admin_base_url();
asset_url('img/logo-nucoco.webp');
admin_url('product');
```

## Database Helpers

For new modules, prefer prepared helper calls:

```php
$row = db_select_one('SELECT * FROM customers WHERE id = ?', 'i', [$id]);
$rows = db_select_all('SELECT * FROM customers ORDER BY id DESC');
$id = db_insert('INSERT INTO customers (name) VALUES (?)', 's', [$name]);
$affected = db_update('UPDATE customers SET name = ? WHERE id = ?', 'si', [$name, $id]);
db_delete('DELETE FROM customers WHERE id = ?', 'i', [$id]);
```

## Admin Module Pattern

Use this folder shape for new ERP modules:

```text
admin/module-name/
  index.php
  create.php
  edit.php
  delete.php
```

At the top of protected admin pages:

```php
<?php
$page = 'module-name';

include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
```

## CSRF

For new POST forms:

```php
<?= csrf_field() ?>
```

At the top of the POST handler:

```php
require_csrf_token();
```

## Permissions

`admin/includes/auth.php` now has:

```php
can('permission.name');
require_permission('permission.name');
```

Admin currently has full access. Add ERP roles and permissions there before exposing staff accounts.
