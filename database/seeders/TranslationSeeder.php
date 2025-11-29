<?php

namespace Database\Seeders;

use App\Models\TranslationKey;
use App\Models\TranslationValue;
use Illuminate\Database\Seeder;

class TranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $translations = [
            // Navigation
            ['key' => 'dashboard', 'group' => 'navigation', 'description' => 'Dashboard menu item', 'en' => 'Dashboard'],
            ['key' => 'profile', 'group' => 'navigation', 'description' => 'Profile menu item', 'en' => 'Profile'],
            ['key' => 'tracks', 'group' => 'navigation', 'description' => 'Tracks menu item', 'en' => 'Tracks'],
            ['key' => 'albums', 'group' => 'navigation', 'description' => 'Albums menu item', 'en' => 'Albums'],
            ['key' => 'playlists', 'group' => 'navigation', 'description' => 'Playlists menu item', 'en' => 'Playlists'],
            ['key' => 'events', 'group' => 'navigation', 'description' => 'Events menu item', 'en' => 'Events'],
            ['key' => 'settings', 'group' => 'navigation', 'description' => 'Settings menu item', 'en' => 'Settings'],
            ['key' => 'logout', 'group' => 'navigation', 'description' => 'Logout menu item', 'en' => 'Logout'],

            // Buttons
            ['key' => 'save', 'group' => 'buttons', 'description' => 'Save button', 'en' => 'Save'],
            ['key' => 'cancel', 'group' => 'buttons', 'description' => 'Cancel button', 'en' => 'Cancel'],
            ['key' => 'delete', 'group' => 'buttons', 'description' => 'Delete button', 'en' => 'Delete'],
            ['key' => 'edit', 'group' => 'buttons', 'description' => 'Edit button', 'en' => 'Edit'],
            ['key' => 'upload', 'group' => 'buttons', 'description' => 'Upload button', 'en' => 'Upload'],
            ['key' => 'download', 'group' => 'buttons', 'description' => 'Download button', 'en' => 'Download'],
            ['key' => 'submit', 'group' => 'buttons', 'description' => 'Submit button', 'en' => 'Submit'],
            ['key' => 'create', 'group' => 'buttons', 'description' => 'Create button', 'en' => 'Create'],
            ['key' => 'update', 'group' => 'buttons', 'description' => 'Update button', 'en' => 'Update'],
            ['key' => 'view', 'group' => 'buttons', 'description' => 'View button', 'en' => 'View'],

            // Messages
            ['key' => 'success', 'group' => 'messages', 'description' => 'Success message', 'en' => 'Success!'],
            ['key' => 'error', 'group' => 'messages', 'description' => 'Error message', 'en' => 'Error!'],
            ['key' => 'saved_successfully', 'group' => 'messages', 'description' => 'Saved successfully message', 'en' => 'Saved successfully!'],
            ['key' => 'deleted_successfully', 'group' => 'messages', 'description' => 'Deleted successfully message', 'en' => 'Deleted successfully!'],
            ['key' => 'updated_successfully', 'group' => 'messages', 'description' => 'Updated successfully message', 'en' => 'Updated successfully!'],
            ['key' => 'created_successfully', 'group' => 'messages', 'description' => 'Created successfully message', 'en' => 'Created successfully!'],

            // Forms
            ['key' => 'name', 'group' => 'forms', 'description' => 'Name field label', 'en' => 'Name'],
            ['key' => 'email', 'group' => 'forms', 'description' => 'Email field label', 'en' => 'Email'],
            ['key' => 'password', 'group' => 'forms', 'description' => 'Password field label', 'en' => 'Password'],
            ['key' => 'confirm_password', 'group' => 'forms', 'description' => 'Confirm password field label', 'en' => 'Confirm Password'],
            ['key' => 'title', 'group' => 'forms', 'description' => 'Title field label', 'en' => 'Title'],
            ['key' => 'description', 'group' => 'forms', 'description' => 'Description field label', 'en' => 'Description'],
            ['key' => 'price', 'group' => 'forms', 'description' => 'Price field label', 'en' => 'Price'],
            ['key' => 'category', 'group' => 'forms', 'description' => 'Category field label', 'en' => 'Category'],

            // Admin Panel
            ['key' => 'users', 'group' => 'admin', 'description' => 'Users section', 'en' => 'Users'],
            ['key' => 'content', 'group' => 'admin', 'description' => 'Content section', 'en' => 'Content'],
            ['key' => 'analytics', 'group' => 'admin', 'description' => 'Analytics section', 'en' => 'Analytics'],
            ['key' => 'banners', 'group' => 'admin', 'description' => 'Banners section', 'en' => 'Banners'],
            ['key' => 'boosts', 'group' => 'admin', 'description' => 'Boosts section', 'en' => 'Boosts'],
            ['key' => 'plugins', 'group' => 'admin', 'description' => 'Plugins section', 'en' => 'Plugins'],
            ['key' => 'flash_albums', 'group' => 'admin', 'description' => 'Flash Albums section', 'en' => 'Flash Albums'],
            ['key' => 'translations', 'group' => 'admin', 'description' => 'Translations section', 'en' => 'Translations'],

            // Common
            ['key' => 'search', 'group' => 'common', 'description' => 'Search placeholder', 'en' => 'Search...'],
            ['key' => 'loading', 'group' => 'common', 'description' => 'Loading text', 'en' => 'Loading...'],
            ['key' => 'no_results', 'group' => 'common', 'description' => 'No results message', 'en' => 'No results found'],
            ['key' => 'confirm', 'group' => 'common', 'description' => 'Confirm action', 'en' => 'Are you sure?'],
        ];

        foreach ($translations as $data) {
            $key = TranslationKey::create([
                'key' => $data['key'],
                'group' => $data['group'],
                'description' => $data['description'] ?? null,
            ]);

            TranslationValue::create([
                'translation_key_id' => $key->id,
                'locale' => 'en',
                'value' => $data['en'],
            ]);
        }
    }
}
