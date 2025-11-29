# Plugin Development Documentation

Welcome to the DemoLimo Plugin System! This guide will help you create powerful plugins that extend the platform without modifying core code.

## Table of Contents

1. [Getting Started](#getting-started)
2. [Plugin Structure](#plugin-structure)
3. [Hooks & Filters](#hooks--filters)
4. [Database & Migrations](#database--migrations)
5. [Routes & Controllers](#routes--controllers)
6. [Assets Management](#assets-management)
7. [Settings API](#settings-api)
8. [Best Practices](#best-practices)

---

## Getting Started

### What is a Plugin?

A plugin is a self-contained package that adds features to DemoLimo. Plugins can:
- Hook into application events
- Modify data with filters
- Add new routes and pages
- Create database tables
- Enqueue CSS/JS assets
- Store custom settings

### Creating Your First Plugin

1. Create a new directory in `plugins/`:
   ```
   plugins/my-awesome-plugin/
   ```

2. Create `plugin.json`:
   ```json
   {
       "name": "My Awesome Plugin",
       "slug": "my-awesome-plugin",
       "description": "Does awesome things",
       "version": "1.0.0",
       "author": "Your Name",
       "main_file": "plugin.php"
   }
   ```

3. Create `plugin.php`:
   ```php
   <?php
   /**
    * Plugin Name: My Awesome Plugin
    * Description: Does awesome things
    * Version: 1.0.0
    */
   
   add_action('app.booted', function () {
       \Log::info('My plugin is loaded!');
   });
   ```

---

## Plugin Structure

### Recommended Directory Structure

```
plugins/my-awesome-plugin/
├── plugin.json          # Plugin metadata
├── plugin.php           # Main plugin file
├── README.md            # Documentation
├── src/                 # PHP classes
│   ├── Controllers/
│   ├── Models/
│   └── Services/
├── migrations/          # Database migrations
│   └── 2024_01_01_create_my_table.php
├── routes/              # Custom routes
│   └── web.php
├── resources/           # Assets and views
│   ├── views/
│   ├── css/
│   └── js/
└── config/              # Plugin configuration
    └── settings.php
```

### plugin.json Format

```json
{
    "name": "Plugin Display Name",
    "slug": "plugin-slug",
    "description": "Brief description",
    "version": "1.0.0",
    "author": "Author Name",
    "author_url": "https://example.com",
    "plugin_url": "https://example.com/plugin",
    "main_file": "plugin.php",
    "requires": {
        "php": ">=8.0",
        "laravel": ">=10.0"
    }
}
```

---

## Hooks & Filters

### Available Core Hooks

#### Application Hooks
- `app.booted` - After application initialization
- `app.shutdown` - Before application shutdown

#### User Hooks
- `user.registered` - After user registration
  ```php
  add_action('user.registered', function ($user) {
      // Send welcome email
  });
  ```

- `user.login` - After successful login
  ```php
  add_action('user.login', function ($user) {
      // Track login
  });
  ```

- `user.logout` - After logout

#### Track Hooks
- `track.uploaded` - After track upload
  ```php
  add_action('track.uploaded', function ($track) {
      // Process track
  });
  ```

- `track.played` - When track is played
- `track.liked` - When track is liked
- `track.shared` - When track is shared

#### Payment Hooks
- `payment.completed` - After successful payment
- `payment.refunded` - After refund

### Available Filters

#### Data Filters
- `track.metadata` - Modify track metadata
  ```php
  add_filter('track.metadata', function ($metadata, $track) {
      $metadata['custom_field'] = 'value';
      return $metadata;
  }, 10);
  ```

- `user.profile` - Modify user profile data
- `search.results` - Filter search results
- `admin.menu` - Add admin menu items

### Creating Custom Hooks

In your controllers or services:

```php
// Fire an action
do_action('my_plugin.custom_event', $data);

// Apply a filter
$value = apply_filters('my_plugin.custom_filter', $value, $arg1, $arg2);
```

### Hook Priority

Lower numbers = higher priority (executed first):

```php
add_action('user.registered', 'myFunction', 5);  // Runs first
add_action('user.registered', 'otherFunction', 10); // Runs second
add_action('user.registered', 'lastFunction', 20);  // Runs last
```

---

## Database & Migrations

### Creating Migrations

1. Create migration file in `plugins/my-plugin/migrations/`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('my_plugin_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('custom_field');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('my_plugin_data');
    }
};
```

2. Migrations run automatically when plugin is installed.

### Creating Models

```php
<?php

namespace Plugins\MyPlugin\Models;

use Illuminate\Database\Eloquent\Model;

class MyData extends Model
{
    protected $table = 'my_plugin_data';
    
    protected $fillable = ['user_id', 'custom_field'];
    
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
```

---

## Routes & Controllers

### Registering Routes

Create `routes/web.php`:

```php
<?php

use Illuminate\Support\Facades\Route;
use Plugins\MyPlugin\Controllers\MyController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/my-plugin', [MyController::class, 'index'])
        ->name('my-plugin.index');
    
    Route::post('/my-plugin/save', [MyController::class, 'save'])
        ->name('my-plugin.save');
});
```

### Creating Controllers

```php
<?php

namespace Plugins\MyPlugin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MyController extends Controller
{
    public function index()
    {
        return view('my-plugin::index');
    }
    
    public function save(Request $request)
    {
        // Handle form submission
        return redirect()->back()->with('success', 'Saved!');
    }
}
```

---

## Assets Management

### Enqueueing Styles

```php
add_action('admin.enqueue_scripts', function () {
    enqueue_style(
        'my-plugin-admin',                    // Handle
        plugin_url('my-plugin', 'css/admin.css'), // URL
        [],                                    // Dependencies
        '1.0.0',                              // Version
        'all'                                  // Media
    );
});
```

### Enqueueing Scripts

```php
add_action('wp_enqueue_scripts', function () {
    enqueue_script(
        'my-plugin-frontend',                 // Handle
        plugin_url('my-plugin', 'js/app.js'), // URL
        ['jquery'],                           // Dependencies
        '1.0.0',                              // Version
        true                                   // Load in footer
    );
});
```

### Adding Inline Styles/Scripts

```php
add_action('wp_head', function () {
    add_inline_style('my-custom-css', '
        .my-class {
            color: red;
        }
    ');
    
    add_inline_script('my-custom-js', '
        console.log("Plugin loaded");
    ');
});
```

---

## Settings API

### Saving Settings

```php
update_plugin_setting('my-plugin', 'api_key', 'abc123');
update_plugin_setting('my-plugin', 'enabled', true);
```

### Retrieving Settings

```php
$apiKey = plugin_setting('my-plugin', 'api_key', 'default-value');
$enabled = plugin_setting('my-plugin', 'enabled', false);
```

### Creating Settings Page

```php
add_filter('admin.menu', function ($menu) {
    $menu[] = [
        'title' => 'My Plugin Settings',
        'url' => route('admin.my-plugin.settings'),
        'icon' => 'fa-cog',
    ];
    return $menu;
});
```

---

## Best Practices

### 1. Naming Conventions

- **Plugin Slug**: Use lowercase with hyphens (`my-awesome-plugin`)
- **Hooks**: Prefix with plugin slug (`my_plugin.custom_hook`)
- **Database Tables**: Prefix with plugin slug (`my_plugin_data`)
- **CSS Classes**: Prefix with plugin slug (`.my-plugin-button`)

### 2. Security

```php
// Always validate and sanitize input
$value = sanitize_text_field($_POST['field']);

// Check user permissions
if (!current_user_can('manage_options')) {
    abort(403);
}

// Use nonces for forms
wp_nonce_field('my_plugin_action');
```

### 3. Performance

- Cache expensive operations
- Use database indexes
- Minimize hook callbacks
- Lazy load assets (only when needed)

### 4. Error Handling

```php
try {
    // Your code
} catch (\Exception $e) {
    \Log::error('My Plugin Error: ' . $e->getMessage());
    // Handle gracefully
}
```

### 5. Uninstallation

Clean up on plugin removal:

```php
// In plugin.php
register_deactivation_hook(__FILE__, function () {
    // Clean up temporary data
});

register_uninstall_hook(__FILE__, function () {
    // Remove all plugin data
    Schema::dropIfExists('my_plugin_data');
});
```

---

## Helper Functions Reference

### Plugin Helpers

- `do_action($hook, ...$args)` - Execute action hooks
- `apply_filters($hook, $value, ...$args)` - Apply filter hooks
- `add_action($hook, $callback, $priority)` - Register action
- `add_filter($hook, $callback, $priority)` - Register filter

### Plugin Paths

- `plugin_url($slug, $path)` - Get URL to plugin file
- `plugin_path($slug, $path)` - Get absolute path to plugin file
- `get_plugin_data($slug)` - Get plugin metadata

### Settings

- `plugin_setting($slug, $key, $default)` - Get setting
- `update_plugin_setting($slug, $key, $value)` - Update setting

### Assets

- `enqueue_style($handle, $src, $deps, $version, $media)` - Enqueue CSS
- `enqueue_script($handle, $src, $deps, $version, $inFooter)` - Enqueue JS
- `add_inline_style($handle, $css)` - Add inline CSS
- `add_inline_script($handle, $js)` - Add inline JS

---

## Example: Complete Plugin

See `plugins/demo-plugin/` for a complete working example demonstrating:
- Hook registration
- Filter usage
- Settings management
- Asset enqueueing
- Custom routes
- Database integration

---

## Support

For questions or issues:
- Documentation: https://demolimo.com/docs/plugins
- Forum: https://demolimo.com/forum
- GitHub: https://github.com/demolimo/plugins

Happy plugin development! 🚀
