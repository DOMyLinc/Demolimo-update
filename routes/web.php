<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\User\PlaylistController;
use App\Http\Controllers\User\SocialController;
use App\Http\Controllers\User\DiscoveryController;
use App\Http\Controllers\StudioController;

// Installation Routes
require __DIR__ . '/install.php';
use App\Http\Controllers\Admin\GenreController;
use App\Http\Controllers\Admin\FeatureFlagController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

use App\Http\Controllers\Auth\SocialAuthController;

Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('auth.redirect');
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('auth.callback');

// Installer Routes
Route::get('/installer', [App\Http\Controllers\InstallerController::class, 'index'])->name('installer.index');
Route::get('/installer/database', [App\Http\Controllers\InstallerController::class, 'database'])->name('installer.database');
Route::post('/installer/database', [App\Http\Controllers\InstallerController::class, 'setupDatabase'])->name('installer.database.post');
Route::get('/installer/admin', [App\Http\Controllers\InstallerController::class, 'admin'])->name('installer.admin');
Route::post('/installer/admin', [App\Http\Controllers\InstallerController::class, 'setupAdmin'])->name('installer.admin.post');
Route::get('/installer/features', [App\Http\Controllers\InstallerController::class, 'features'])->name('installer.features');
Route::post('/installer/features', [App\Http\Controllers\InstallerController::class, 'setupFeatures'])->name('installer.features.post');
Route::get('/installer/settings', [App\Http\Controllers\InstallerController::class, 'settings'])->name('installer.settings');
Route::post('/installer/settings', [App\Http\Controllers\InstallerController::class, 'setupSettings'])->name('installer.settings.post');


Route::get('/', [HomeController::class, 'index'])->name('home');

// Search Routes
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/api/search', [SearchController::class, 'api'])->name('api.search');
Route::get('/api/search/autocomplete', [SearchController::class, 'autocomplete'])->name('api.search.autocomplete');


// Admin Routes
Route::middleware(['auth:sanctum', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // User Management
    Route::get('/users', [App\Http\Controllers\Admin\UserManagementController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [App\Http\Controllers\Admin\UserManagementController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [App\Http\Controllers\Admin\UserManagementController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [App\Http\Controllers\Admin\UserManagementController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [App\Http\Controllers\Admin\UserManagementController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{user}/ban', [App\Http\Controllers\Admin\UserManagementController::class, 'ban'])->name('users.ban');
    Route::post('/users/{user}/unban', [App\Http\Controllers\Admin\UserManagementController::class, 'unban'])->name('users.unban');
    Route::post('/users/{user}/verify', [App\Http\Controllers\Admin\UserManagementController::class, 'verify'])->name('users.verify');
    Route::put('/users/{user}/limit', [App\Http\Controllers\Admin\UserManagementController::class, 'updateLimit'])->name('users.update_limit');
    Route::post('/users/{user}/add-followers', [App\Http\Controllers\Admin\UserManagementController::class, 'addFollowers'])->name('users.addFollowers');
    Route::post('/users/bulk-action', [App\Http\Controllers\Admin\UserManagementController::class, 'bulkAction'])->name('users.bulk');

    // Admin Management
    Route::resource('admins', App\Http\Controllers\Admin\AdminManagementController::class);

    // Announcements
    Route::resource('announcements', App\Http\Controllers\Admin\AnnouncementController::class);
    Route::post('/announcements/{announcement}/toggle', [App\Http\Controllers\Admin\AnnouncementController::class, 'toggleActive'])->name('announcements.toggle');

    // System Settings
    Route::get('/settings/system', [App\Http\Controllers\Admin\SystemSettingsController::class, 'index'])->name('settings.system');
    Route::post('/settings/system', [App\Http\Controllers\Admin\SystemSettingsController::class, 'update'])->name('settings.update');

    // Featured Content
    Route::resource('featured', App\Http\Controllers\Admin\FeaturedContentController::class)->except(['show', 'edit', 'update']);
    Route::post('/featured/reorder', [App\Http\Controllers\Admin\FeaturedContentController::class, 'reorder'])->name('featured.reorder');

    // Newsletters
    Route::resource('newsletters', App\Http\Controllers\Admin\NewsletterController::class)->except(['edit', 'update']);
    Route::post('/newsletters/{newsletter}/send', [App\Http\Controllers\Admin\NewsletterController::class, 'send'])->name('newsletters.send');

    // Song Battles Management
    Route::get('/song-battles', [App\Http\Controllers\Admin\SongBattleController::class, 'index'])->name('song_battles.index');
    Route::get('/song-battles/{songBattle}', [App\Http\Controllers\Admin\SongBattleController::class, 'show'])->name('song_battles.show');
    Route::delete('/song-battles/{songBattle}', [App\Http\Controllers\Admin\SongBattleController::class, 'destroy'])->name('song_battles.destroy');
    Route::post('/song-battles/{songBattle}/complete', [App\Http\Controllers\Admin\SongBattleController::class, 'complete'])->name('song_battles.complete');
    Route::post('/song-battles/{songBattle}/reopen', [App\Http\Controllers\Admin\SongBattleController::class, 'reopen'])->name('song_battles.reopen');

    // Song Battle Rewards
    Route::get('/song-battle-rewards', [App\Http\Controllers\Admin\SongBattleRewardController::class, 'index'])->name('song_battle_rewards.index');
    Route::get('/song-battle-rewards/create', [App\Http\Controllers\Admin\SongBattleRewardController::class, 'create'])->name('song_battle_rewards.create');
    Route::post('/song-battle-rewards', [App\Http\Controllers\Admin\SongBattleRewardController::class, 'store'])->name('song_battle_rewards.store');
    Route::get('/song-battle-rewards/{reward}', [App\Http\Controllers\Admin\SongBattleRewardController::class, 'show'])->name('song_battle_rewards.show');
    Route::get('/song-battle-rewards/{reward}/edit', [App\Http\Controllers\Admin\SongBattleRewardController::class, 'edit'])->name('song_battle_rewards.edit');
    Route::put('/song-battle-rewards/{reward}', [App\Http\Controllers\Admin\SongBattleRewardController::class, 'update'])->name('song_battle_rewards.update');
    Route::post('/song-battle-rewards/{reward}/award', [App\Http\Controllers\Admin\SongBattleRewardController::class, 'award'])->name('song_battle_rewards.award');
    Route::delete('/song-battle-rewards/{reward}', [App\Http\Controllers\Admin\SongBattleRewardController::class, 'destroy'])->name('song_battle_rewards.destroy');
    Route::get('/song-battle-rewards/get-winner', [App\Http\Controllers\Admin\SongBattleRewardController::class, 'getWinner'])->name('song_battle_rewards.getWinner');

    // Donations Management
    Route::get('/donations', [App\Http\Controllers\Admin\DonationManagementController::class, 'donations'])->name('donations.index');
    Route::get('/donations/{donation}', [App\Http\Controllers\Admin\DonationManagementController::class, 'showDonation'])->name('donations.show');
    Route::post('/donations/{donation}/process', [App\Http\Controllers\Admin\DonationManagementController::class, 'processDonation'])->name('donations.process');
    Route::post('/donations/{donation}/refund', [App\Http\Controllers\Admin\DonationManagementController::class, 'refundDonation'])->name('donations.refund');

    // Tips Management
    Route::get('/tips', [App\Http\Controllers\Admin\DonationManagementController::class, 'tips'])->name('tips.index');
    Route::get('/tips/{tip}', [App\Http\Controllers\Admin\DonationManagementController::class, 'showTip'])->name('tips.show');
    Route::post('/tips/{tip}/process', [App\Http\Controllers\Admin\DonationManagementController::class, 'processTip'])->name('tips.process');
    Route::post('/tips/{tip}/refund', [App\Http\Controllers\Admin\DonationManagementController::class, 'refundTip'])->name('tips.refund');

    // Gifts Management
    Route::get('/gifts', [App\Http\Controllers\Admin\DonationManagementController::class, 'gifts'])->name('gifts.index');
    Route::get('/gifts/create', [App\Http\Controllers\Admin\DonationManagementController::class, 'createGift'])->name('gifts.create');
    Route::post('/gifts', [App\Http\Controllers\Admin\DonationManagementController::class, 'storeGift'])->name('gifts.store');
    Route::post('/gifts/{gift}/send', [App\Http\Controllers\Admin\DonationManagementController::class, 'sendGift'])->name('gifts.send');
    Route::delete('/gifts/{gift}', [App\Http\Controllers\Admin\DonationManagementController::class, 'deleteGift'])->name('gifts.delete');

    // Donation Settings
    Route::get('/donation-settings', [App\Http\Controllers\Admin\DonationManagementController::class, 'settings'])->name('donation_settings.index');
    Route::put('/donation-settings/user/{user}', [App\Http\Controllers\Admin\DonationManagementController::class, 'updateUserSettings'])->name('donation_settings.updateUser');
    Route::put('/donation-settings/global', [App\Http\Controllers\Admin\DonationManagementController::class, 'updateGlobalSettings'])->name('donation_settings.updateGlobal');

    // Donation Analytics
    Route::get('/donation-analytics', [App\Http\Controllers\Admin\DonationManagementController::class, 'analytics'])->name('donation_analytics');

    // Payment Gateways Management
    Route::get('/payment-gateways', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'index'])->name('payment_gateways.index');
    Route::get('/payment-gateways/create', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'create'])->name('payment_gateways.create');
    Route::post('/payment-gateways', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'store'])->name('payment_gateways.store');
    Route::get('/payment-gateways/{gateway}', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'show'])->name('payment_gateways.show');
    Route::get('/payment-gateways/{gateway}/edit', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'edit'])->name('payment_gateways.edit');
    Route::put('/payment-gateways/{gateway}', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'update'])->name('payment_gateways.update');
    Route::delete('/payment-gateways/{gateway}', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'destroy'])->name('payment_gateways.destroy');
    Route::post('/payment-gateways/{gateway}/toggle', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'toggleStatus'])->name('payment_gateways.toggle');

    // Fee Settings
    Route::get('/fee-settings', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'feeSettings'])->name('fee_settings.index');
    Route::get('/fee-settings/create', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'createFeeSetting'])->name('fee_settings.create');
    Route::post('/fee-settings', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'storeFeeSetting'])->name('fee_settings.store');
    Route::put('/fee-settings/{feeSetting}', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'updateFeeSetting'])->name('fee_settings.update');
    Route::delete('/fee-settings/{feeSetting}', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'deleteFeeSetting'])->name('fee_settings.delete');

    // Manual Payments
    Route::get('/manual-payments', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'manualPayments'])->name('manual_payments.index');
    Route::get('/manual-payments/{verification}', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'showManualPayment'])->name('manual_payments.show');
    Route::post('/manual-payments/{verification}/approve', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'approveManualPayment'])->name('manual_payments.approve');
    Route::post('/manual-payments/{verification}/reject', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'rejectManualPayment'])->name('manual_payments.reject');

    // Payment Transactions
    Route::get('/transactions', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'transactions'])->name('transactions.index');
    Route::get('/transactions/{transaction}', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'showTransaction'])->name('transactions.show');
    Route::post('/transactions/{transaction}/refund', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'refundTransaction'])->name('transactions.refund');

    // Payment Analytics
    Route::get('/payment-analytics', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'analytics'])->name('payment_analytics');

    // Sales Management
    Route::get('/sales/tracks', [App\Http\Controllers\Admin\SalesManagementController::class, 'trackSales'])->name('sales.tracks');
    Route::get('/sales/tracks/{sale}', [App\Http\Controllers\Admin\SalesManagementController::class, 'showTrackSale'])->name('sales.tracks.show');
    Route::get('/sales/albums', [App\Http\Controllers\Admin\SalesManagementController::class, 'albumSales'])->name('sales.albums');
    Route::get('/sales/albums/{sale}', [App\Http\Controllers\Admin\SalesManagementController::class, 'showAlbumSale'])->name('sales.albums.show');
    Route::get('/sales/analytics', [App\Http\Controllers\Admin\SalesManagementController::class, 'analytics'])->name('sales.analytics');

    // Track Management
    Route::get('/tracks', [App\Http\Controllers\Admin\TrackManagementController::class, 'index'])->name('tracks.index');
    Route::get('/tracks/{track}', [App\Http\Controllers\Admin\TrackManagementController::class, 'show'])->name('tracks.show');
    Route::delete('/tracks/{track}', [App\Http\Controllers\Admin\TrackManagementController::class, 'destroy'])->name('tracks.destroy');
    Route::post('/tracks/{track}/approve', [App\Http\Controllers\Admin\TrackManagementController::class, 'approve'])->name('tracks.approve');
    Route::post('/tracks/{track}/reject', [App\Http\Controllers\Admin\TrackManagementController::class, 'reject'])->name('tracks.reject');
    Route::post('/tracks/{track}/add-interactions', [App\Http\Controllers\Admin\TrackManagementController::class, 'addInteractions'])->name('tracks.addInteractions');

    // Playlist Management
    Route::resource('playlists', App\Http\Controllers\Admin\PlaylistController::class);
    Route::post('/playlists/{playlist}/toggle-visibility', [App\Http\Controllers\Admin\PlaylistController::class, 'toggleVisibility'])->name('playlists.toggleVisibility');

    // Music Distribution Management
    Route::get('/distributions', [App\Http\Controllers\Admin\DistributionController::class, 'index'])->name('distributions.index');
    Route::get('/distributions/{distribution}', [App\Http\Controllers\Admin\DistributionController::class, 'show'])->name('distributions.show');
    Route::post('/distributions/{distribution}/approve', [App\Http\Controllers\Admin\DistributionController::class, 'approve'])->name('distributions.approve');
    Route::post('/distributions/{distribution}/reject', [App\Http\Controllers\Admin\DistributionController::class, 'reject'])->name('distributions.reject');
    Route::put('/distributions/{distribution}/status', [App\Http\Controllers\Admin\DistributionController::class, 'updateStatus'])->name('distributions.updateStatus');
    Route::delete('/distributions/{distribution}', [App\Http\Controllers\Admin\DistributionController::class, 'destroy'])->name('distributions.destroy');
    Route::get('/distribution-platforms', [App\Http\Controllers\Admin\DistributionController::class, 'platforms'])->name('distributions.platforms');
    Route::get('/distribution-analytics', [App\Http\Controllers\Admin\DistributionController::class, 'analytics'])->name('distributions.analytics');
    Route::get('/distribution-earnings', [App\Http\Controllers\Admin\DistributionController::class, 'earnings'])->name('distributions.earnings');

    // Gift Management
    Route::resource('gifts', App\Http\Controllers\Admin\GiftController::class);
    Route::get('/gifts-analytics', [App\Http\Controllers\Admin\GiftController::class, 'analytics'])->name('gifts.analytics');

    // DAW Sound Library Management
    Route::resource('daw-sounds', App\Http\Controllers\Admin\DawSoundController::class);
    Route::get('/daw-sounds-categories', [App\Http\Controllers\Admin\DawSoundController::class, 'categories'])->name('daw-sounds.categories');

    // Analytics
    Route::get('/analytics', [App\Http\Controllers\Admin\AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/revenue', [App\Http\Controllers\Admin\AnalyticsController::class, 'revenue'])->name('analytics.revenue');

    // Subscriptions
    Route::get('/subscriptions', [App\Http\Controllers\Admin\SubscriptionManagementController::class, 'index'])->name('subscriptions.index');

    // Events Management
    Route::get('/events', [App\Http\Controllers\Admin\EventManagementController::class, 'index'])->name('events.index');
    Route::get('/events/{event}', [App\Http\Controllers\Admin\EventManagementController::class, 'show'])->name('events.show');
    Route::put('/events/{event}/status', [App\Http\Controllers\Admin\EventManagementController::class, 'updateStatus'])->name('events.updateStatus');
    Route::post('/events/{event}/toggle-featured', [App\Http\Controllers\Admin\EventManagementController::class, 'toggleFeatured'])->name('events.toggleFeatured');
    Route::delete('/events/{event}', [App\Http\Controllers\Admin\EventManagementController::class, 'destroy'])->name('events.destroy');
    Route::post('/events/check-in', [App\Http\Controllers\Admin\EventManagementController::class, 'checkIn'])->name('events.checkIn');

    // Feature Permissions
    Route::get('/permissions', [App\Http\Controllers\Admin\FeaturePermissionController::class, 'index'])->name('permissions.index');
    Route::get('/permissions/create', [App\Http\Controllers\Admin\FeaturePermissionController::class, 'create'])->name('permissions.create');
    Route::post('/permissions', [App\Http\Controllers\Admin\FeaturePermissionController::class, 'store'])->name('permissions.store');
    Route::get('/permissions/{permission}/edit', [App\Http\Controllers\Admin\FeaturePermissionController::class, 'edit'])->name('permissions.edit');
    Route::put('/permissions/{permission}', [App\Http\Controllers\Admin\FeaturePermissionController::class, 'update'])->name('permissions.update');
    Route::post('/permissions/grant', [App\Http\Controllers\Admin\FeaturePermissionController::class, 'grantToUser'])->name('permissions.grant');
    Route::post('/permissions/revoke', [App\Http\Controllers\Admin\FeaturePermissionController::class, 'revokeFromUser'])->name('permissions.revoke');

    // Platform Settings
    Route::get('/settings/platform', [App\Http\Controllers\Admin\PlatformSettingsController::class, 'index'])->name('settings.platform');
    Route::put('/settings/platform', [App\Http\Controllers\Admin\PlatformSettingsController::class, 'update'])->name('settings.platform.update');
    Route::get('/settings/platform/create', [App\Http\Controllers\Admin\PlatformSettingsController::class, 'create'])->name('settings.platform.create');
    Route::post('/settings/platform', [App\Http\Controllers\Admin\PlatformSettingsController::class, 'store'])->name('settings.platform.store');
    Route::delete('/settings/platform/{setting}', [App\Http\Controllers\Admin\PlatformSettingsController::class, 'destroy'])->name('settings.platform.destroy');
    Route::post('/settings/toggle-maintenance', [App\Http\Controllers\Admin\PlatformSettingsController::class, 'toggleMaintenance'])->name('settings.toggleMaintenance');
    Route::post('/settings/toggle-registration', [App\Http\Controllers\Admin\PlatformSettingsController::class, 'toggleRegistration'])->name('settings.toggleRegistration');

    // Settings
    Route::get('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');

    // Landing Page
    Route::get('/landing', [App\Http\Controllers\Admin\LandingPageController::class, 'index'])->name('landing.index');
    Route::put('/landing/{key}', [App\Http\Controllers\Admin\LandingPageController::class, 'update'])->name('landing.update');

    // Zipcode Management
    Route::get('/zipcodes', [App\Http\Controllers\Admin\ZipcodeManagementController::class, 'index'])->name('zipcodes.index');
    Route::get('/zipcodes/create', [App\Http\Controllers\Admin\ZipcodeManagementController::class, 'create'])->name('zipcodes.create');
    Route::post('/zipcodes', [App\Http\Controllers\Admin\ZipcodeManagementController::class, 'store'])->name('zipcodes.store');
    Route::get('/zipcodes/{zipcode}', [App\Http\Controllers\Admin\ZipcodeManagementController::class, 'show'])->name('zipcodes.show');
    Route::get('/zipcodes/{zipcode}/edit', [App\Http\Controllers\Admin\ZipcodeManagementController::class, 'edit'])->name('zipcodes.edit');
    Route::put('/zipcodes/{zipcode}', [App\Http\Controllers\Admin\ZipcodeManagementController::class, 'update'])->name('zipcodes.update');
    Route::delete('/zipcodes/{zipcode}', [App\Http\Controllers\Admin\ZipcodeManagementController::class, 'destroy'])->name('zipcodes.destroy');
    Route::post('/zipcodes/{zipcode}/toggle-status', [App\Http\Controllers\Admin\ZipcodeManagementController::class, 'toggleStatus'])->name('zipcodes.toggleStatus');
    Route::put('/zipcodes/{zipcode}/settings', [App\Http\Controllers\Admin\ZipcodeManagementController::class, 'updateSettings'])->name('zipcodes.updateSettings');
    Route::post('/zipcodes/{zipcode}/remove-user', [App\Http\Controllers\Admin\ZipcodeManagementController::class, 'removeUser'])->name('zipcodes.removeUser');

    // Blockchain Valuation System
    Route::get('/blockchain', [App\Http\Controllers\Admin\BlockchainController::class, 'index'])->name('blockchain.index');
    Route::get('/blockchain/settings', [App\Http\Controllers\Admin\BlockchainController::class, 'settings'])->name('blockchain.settings');
    Route::put('/blockchain/settings', [App\Http\Controllers\Admin\BlockchainController::class, 'updateSettings'])->name('blockchain.updateSettings');
    Route::post('/blockchain/recalculate-all', [App\Http\Controllers\Admin\BlockchainController::class, 'recalculateAll'])->name('blockchain.recalculateAll');
    Route::get('/blockchain/track/{track}', [App\Http\Controllers\Admin\BlockchainController::class, 'trackValuation'])->name('blockchain.trackValuation');
    Route::delete('/plugins/{plugin}', [App\Http\Controllers\Admin\PluginController::class, 'uninstall'])->name('plugins.uninstall');
    Route::get('/plugins/{plugin}/settings', [App\Http\Controllers\Admin\PluginController::class, 'settings'])->name('plugins.settings');
    Route::put('/plugins/{plugin}/settings', [App\Http\Controllers\Admin\PluginController::class, 'updateSettings'])->name('plugins.updateSettings');
    Route::put('/plugins/{plugin}/priority', [App\Http\Controllers\Admin\PluginController::class, 'updatePriority'])->name('plugins.updatePriority');

    // Boost & Manipulation Tools
    Route::get('/boost', [App\Http\Controllers\Admin\BoostController::class, 'index'])->name('boost.index');
    Route::post('/boost/track/{track}', [App\Http\Controllers\Admin\BoostController::class, 'boostTrack'])->name('boost.track');
    Route::post('/boost/user/{user}', [App\Http\Controllers\Admin\BoostController::class, 'boostUser'])->name('boost.user');
    Route::post('/boost/event/{event}', [App\Http\Controllers\Admin\BoostController::class, 'boostEvent'])->name('boost.event');
    Route::post('/boost/auto', [App\Http\Controllers\Admin\BoostController::class, 'autoBoost'])->name('boost.auto');
    Route::post('/boost/reset', [App\Http\Controllers\Admin\BoostController::class, 'resetMetrics'])->name('boost.reset');

    // Fake Data Generator
    Route::get('/fake-data', [App\Http\Controllers\Admin\FakeDataController::class, 'index'])->name('fake-data.index');
    Route::post('/fake-data/users', [App\Http\Controllers\Admin\FakeDataController::class, 'generateUsers'])->name('fake-data.users');
    Route::post('/fake-data/tracks', [App\Http\Controllers\Admin\FakeDataController::class, 'generateTracks'])->name('fake-data.tracks');
    Route::post('/fake-data/interactions', [App\Http\Controllers\Admin\FakeDataController::class, 'generateInteractions'])->name('fake-data.interactions');
    Route::delete('/fake-data', [App\Http\Controllers\Admin\FakeDataController::class, 'deleteFakeData'])->name('fake-data.delete');

    // Analytics
    Route::get('/analytics', [App\Http\Controllers\Admin\AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/revenue', [App\Http\Controllers\Admin\AnalyticsController::class, 'revenue'])->name('analytics.revenue');


    // Verification Management
    Route::get('/verification', [App\Http\Controllers\Admin\VerificationController::class, 'index'])->name('verification.index');
    Route::get('/verification/{request}', [App\Http\Controllers\Admin\VerificationController::class, 'show'])->name('verification.show');
    Route::post('/verification/{request}/approve', [App\Http\Controllers\Admin\VerificationController::class, 'approve'])->name('verification.approve');
    Route::post('/verification/{request}/reject', [App\Http\Controllers\Admin\VerificationController::class, 'reject'])->name('verification.reject');


    // Feature Flags
    Route::get('/features', [App\Http\Controllers\Admin\FeatureFlagController::class, 'index'])->name('features.index');
    Route::post('/features/{feature}/toggle', [App\Http\Controllers\Admin\FeatureFlagController::class, 'toggle'])->name('features.toggle');
    Route::post('/features/bulk-toggle', [App\Http\Controllers\Admin\FeatureFlagController::class, 'bulkToggle'])->name('features.bulkToggle');
    Route::post('/features/seed', [App\Http\Controllers\Admin\FeatureFlagController::class, 'seed'])->name('features.seed');

    // Database Management
    Route::get('/database', [App\Http\Controllers\Admin\DatabaseController::class, 'index'])->name('database.index');
    Route::post('/database/backup', [App\Http\Controllers\Admin\DatabaseController::class, 'backup'])->name('database.backup');
    Route::post('/database/optimize', [App\Http\Controllers\Admin\DatabaseController::class, 'optimize'])->name('database.optimize');

    // Cache Management
    Route::get('/cache', [App\Http\Controllers\Admin\CacheSettingsController::class, 'index'])->name('cache.index');
    Route::post('/cache/clear', [App\Http\Controllers\Admin\CacheSettingsController::class, 'clear'])->name('cache.clear');
    Route::post('/cache/clear-all', [App\Http\Controllers\Admin\CacheSettingsController::class, 'clearAll'])->name('cache.clearAll');

    // Security Settings
    Route::get('/security', [App\Http\Controllers\Admin\SecuritySettingsController::class, 'index'])->name('security.index');
    Route::put('/security', [App\Http\Controllers\Admin\SecuritySettingsController::class, 'update'])->name('security.update');

    // API Management
    Route::get('/api-management', [App\Http\Controllers\Admin\ApiManagementController::class, 'index'])->name('api.index');
    Route::post('/api-management/keys', [App\Http\Controllers\Admin\ApiManagementController::class, 'generateKey'])->name('api.generateKey');

    // Moderation System
    Route::get('/moderation', [App\Http\Controllers\Admin\ModerationController::class, 'index'])->name('moderation.index');
    Route::get('/moderation/queue', [App\Http\Controllers\Admin\ModerationController::class, 'queue'])->name('moderation.queue');
    Route::post('/moderation/track/{track}/approve', [App\Http\Controllers\Admin\ModerationController::class, 'approveTrack'])->name('moderation.track.approve');
    Route::post('/moderation/track/{track}/reject', [App\Http\Controllers\Admin\ModerationController::class, 'rejectTrack'])->name('moderation.track.reject');
    Route::post('/moderation/event/{event}/approve', [App\Http\Controllers\Admin\ModerationController::class, 'approveEvent'])->name('moderation.event.approve');
    Route::post('/moderation/event/{event}/reject', [App\Http\Controllers\Admin\ModerationController::class, 'rejectEvent'])->name('moderation.event.reject');
    Route::post('/moderation/bulk-approve', [App\Http\Controllers\Admin\ModerationController::class, 'bulkApprove'])->name('moderation.bulkApprove');
    Route::post('/moderation/auto-approve-all', [App\Http\Controllers\Admin\ModerationController::class, 'autoApproveAll'])->name('moderation.autoApproveAll');
    Route::put('/moderation/settings', [App\Http\Controllers\Admin\ModerationController::class, 'updateSettings'])->name('moderation.updateSettings');
    Route::get('/moderation/history', [App\Http\Controllers\Admin\ModerationController::class, 'history'])->name('moderation.history');

    // Reports
    Route::get('/moderation/reports', [App\Http\Controllers\Admin\ReportController::class, 'index'])->name('moderation.reports.index');
    Route::get('/moderation/reports/{report}', [App\Http\Controllers\Admin\ReportController::class, 'show'])->name('moderation.reports.show');
    Route::post('/moderation/reports/{report}/resolve', [App\Http\Controllers\Admin\ReportController::class, 'resolve'])->name('moderation.reports.resolve');

    // Auto-Moderation Rules
    Route::resource('moderation/rules', App\Http\Controllers\Admin\AutoModerationController::class, ['as' => 'moderation']);

    // Wallet & Points Management
    Route::get('/wallet', [App\Http\Controllers\Admin\WalletManagementController::class, 'index'])->name('wallet.index');
    Route::get('/wallet/packages', [App\Http\Controllers\Admin\WalletManagementController::class, 'packages'])->name('wallet.packages');
    Route::get('/wallet/packages/create', [App\Http\Controllers\Admin\WalletManagementController::class, 'createPackage'])->name('wallet.packages.create');
    Route::post('/wallet/packages', [App\Http\Controllers\Admin\WalletManagementController::class, 'storePackage'])->name('wallet.packages.store');
    Route::get('/wallet/packages/{package}/edit', [App\Http\Controllers\Admin\WalletManagementController::class, 'editPackage'])->name('wallet.packages.edit');
    Route::put('/wallet/packages/{package}', [App\Http\Controllers\Admin\WalletManagementController::class, 'updatePackage'])->name('wallet.packages.update');
    Route::delete('/wallet/packages/{package}', [App\Http\Controllers\Admin\WalletManagementController::class, 'deletePackage'])->name('wallet.packages.delete');
    Route::get('/wallet/user/{user}', [App\Http\Controllers\Admin\WalletManagementController::class, 'userWallet'])->name('wallet.user');
    Route::post('/wallet/user/{user}/add-balance', [App\Http\Controllers\Admin\WalletManagementController::class, 'addBalance'])->name('wallet.addBalance');
    Route::post('/wallet/user/{user}/deduct-balance', [App\Http\Controllers\Admin\WalletManagementController::class, 'deductBalance'])->name('wallet.deductBalance');
    Route::post('/wallet/user/{user}/add-points', [App\Http\Controllers\Admin\WalletManagementController::class, 'addPoints'])->name('wallet.addPoints');
    Route::post('/wallet/user/{user}/deduct-points', [App\Http\Controllers\Admin\WalletManagementController::class, 'deductPoints'])->name('wallet.deductPoints');
    Route::get('/wallet/withdrawals', [App\Http\Controllers\Admin\WalletManagementController::class, 'withdrawals'])->name('wallet.withdrawals');
    Route::post('/wallet/withdrawals/{withdrawal}/approve', [App\Http\Controllers\Admin\WalletManagementController::class, 'approveWithdrawal'])->name('wallet.withdrawals.approve');
    Route::post('/wallet/withdrawals/{withdrawal}/reject', [App\Http\Controllers\Admin\WalletManagementController::class, 'rejectWithdrawal'])->name('wallet.withdrawals.reject');
    Route::get('/wallet/settings', [App\Http\Controllers\Admin\WalletManagementController::class, 'settings'])->name('wallet.settings');
    Route::put('/wallet/settings', [App\Http\Controllers\Admin\WalletManagementController::class, 'updateSettings'])->name('wallet.settings.update');

    // Advanced Settings
    Route::get('/settings/advanced', [App\Http\Controllers\Admin\AdvancedSettingsController::class, 'index'])->name('settings.advanced');
    Route::get('/settings/cache', [App\Http\Controllers\Admin\AdvancedSettingsController::class, 'cacheSettings'])->name('settings.cache');
    Route::post('/settings/cache/clear', [App\Http\Controllers\Admin\AdvancedSettingsController::class, 'clearCache'])->name('settings.cache.clear');
    Route::post('/settings/cache/optimize', [App\Http\Controllers\Admin\AdvancedSettingsController::class, 'optimizeCache'])->name('settings.cache.optimize');
    Route::get('/settings/upload-limits', [App\Http\Controllers\Admin\AdvancedSettingsController::class, 'uploadLimits'])->name('settings.uploadLimits');
    Route::put('/settings/upload-limits', [App\Http\Controllers\Admin\AdvancedSettingsController::class, 'updateUploadLimits'])->name('settings.uploadLimits.update');
    Route::get('/settings/monetization-requests', [App\Http\Controllers\Admin\AdvancedSettingsController::class, 'monetizationRequests'])->name('settings.monetizationRequests');
    Route::post('/settings/monetization-requests/{monetizationRequest}/approve', [App\Http\Controllers\Admin\AdvancedSettingsController::class, 'approveMonetization'])->name('settings.monetization.approve');
    Route::post('/settings/monetization-requests/{monetizationRequest}/reject', [App\Http\Controllers\Admin\AdvancedSettingsController::class, 'rejectMonetization'])->name('settings.monetization.reject');
    Route::get('/settings/system-logs', [App\Http\Controllers\Admin\AdvancedSettingsController::class, 'systemLogs'])->name('settings.systemLogs');
    Route::delete('/settings/system-logs', [App\Http\Controllers\Admin\AdvancedSettingsController::class, 'clearLogs'])->name('settings.systemLogs.clear');
    Route::get('/settings/database', [App\Http\Controllers\Admin\AdvancedSettingsController::class, 'databaseMaintenance'])->name('settings.database');
    Route::post('/settings/database/optimize', [App\Http\Controllers\Admin\AdvancedSettingsController::class, 'optimizeDatabase'])->name('settings.database.optimize');
    Route::get('/settings/email', [App\Http\Controllers\Admin\AdvancedSettingsController::class, 'emailSettings'])->name('settings.email');
    Route::get('/settings/api', [App\Http\Controllers\Admin\AdvancedSettingsController::class, 'apiSettings'])->name('settings.api');
    Route::post('/settings/api/generate', [App\Http\Controllers\Admin\AdvancedSettingsController::class, 'generateApiKey'])->name('settings.api.generate');
    Route::delete('/settings/api/{apiKey}', [App\Http\Controllers\Admin\AdvancedSettingsController::class, 'revokeApiKey'])->name('settings.api.revoke');
    Route::get('/settings/backup', [App\Http\Controllers\Admin\AdvancedSettingsController::class, 'backupSettings'])->name('settings.backup');
    Route::post('/settings/backup/create', [App\Http\Controllers\Admin\AdvancedSettingsController::class, 'createBackup'])->name('settings.backup.create');

    // Email Management
    Route::get('/email', [App\Http\Controllers\Admin\EmailManagementController::class, 'index'])->name('email.index');
    Route::get('/email/templates', [App\Http\Controllers\Admin\EmailManagementController::class, 'templates'])->name('email.templates');
    Route::get('/email/templates/create', [App\Http\Controllers\Admin\EmailManagementController::class, 'createTemplate'])->name('email.templates.create');
    Route::post('/email/templates', [App\Http\Controllers\Admin\EmailManagementController::class, 'storeTemplate'])->name('email.templates.store');
    Route::get('/email/templates/{template}/edit', [App\Http\Controllers\Admin\EmailManagementController::class, 'editTemplate'])->name('email.templates.edit');
    Route::put('/email/templates/{template}', [App\Http\Controllers\Admin\EmailManagementController::class, 'updateTemplate'])->name('email.templates.update');
    Route::delete('/email/templates/{template}', [App\Http\Controllers\Admin\EmailManagementController::class, 'deleteTemplate'])->name('email.templates.delete');
    Route::get('/email/settings', [App\Http\Controllers\Admin\EmailManagementController::class, 'settings'])->name('email.settings');
    Route::put('/email/settings', [App\Http\Controllers\Admin\EmailManagementController::class, 'updateSettings'])->name('email.settings.update');
    Route::get('/email/queue', [App\Http\Controllers\Admin\EmailManagementController::class, 'queue'])->name('email.queue');
    Route::post('/email/queue/process', [App\Http\Controllers\Admin\EmailManagementController::class, 'processQueue'])->name('email.queue.process');
    Route::post('/email/queue/retry', [App\Http\Controllers\Admin\EmailManagementController::class, 'retryFailed'])->name('email.queue.retry');
    Route::delete('/email/queue', [App\Http\Controllers\Admin\EmailManagementController::class, 'clearQueue'])->name('email.queue.clear');
    Route::post('/email/test', [App\Http\Controllers\Admin\EmailManagementController::class, 'testEmail'])->name('email.test');
    Route::post('/email/initialize', [App\Http\Controllers\Admin\EmailManagementController::class, 'initializeTemplates'])->name('email.initialize');

    // Onboarding & Tutorials
    Route::get('/onboarding', [App\Http\Controllers\Admin\OnboardingController::class, 'index'])->name('onboarding.index');
    Route::get('/onboarding/tutorials', [App\Http\Controllers\Admin\OnboardingController::class, 'tutorials'])->name('onboarding.tutorials');
    Route::get('/onboarding/tutorials/create', [App\Http\Controllers\Admin\OnboardingController::class, 'createTutorial'])->name('onboarding.tutorials.create');
    Route::post('/onboarding/tutorials', [App\Http\Controllers\Admin\OnboardingController::class, 'storeTutorial'])->name('onboarding.tutorials.store');
    Route::get('/onboarding/tutorials/{tutorial}/edit', [App\Http\Controllers\Admin\OnboardingController::class, 'editTutorial'])->name('onboarding.tutorials.edit');
    Route::put('/onboarding/tutorials/{tutorial}', [App\Http\Controllers\Admin\OnboardingController::class, 'updateTutorial'])->name('onboarding.tutorials.update');
    Route::delete('/onboarding/tutorials/{tutorial}', [App\Http\Controllers\Admin\OnboardingController::class, 'deleteTutorial'])->name('onboarding.tutorials.delete');
    Route::get('/onboarding/welcome-messages', [App\Http\Controllers\Admin\OnboardingController::class, 'welcomeMessages'])->name('onboarding.welcome');
    Route::get('/onboarding/welcome-messages/create', [App\Http\Controllers\Admin\OnboardingController::class, 'createWelcomeMessage'])->name('onboarding.welcome.create');
    Route::post('/onboarding/welcome-messages', [App\Http\Controllers\Admin\OnboardingController::class, 'storeWelcomeMessage'])->name('onboarding.welcome.store');
    Route::get('/onboarding/welcome-messages/{message}/edit', [App\Http\Controllers\Admin\OnboardingController::class, 'editWelcomeMessage'])->name('onboarding.welcome.edit');
    Route::put('/onboarding/welcome-messages/{message}', [App\Http\Controllers\Admin\OnboardingController::class, 'updateWelcomeMessage'])->name('onboarding.welcome.update');
    Route::delete('/onboarding/welcome-messages/{message}', [App\Http\Controllers\Admin\OnboardingController::class, 'deleteWelcomeMessage'])->name('onboarding.welcome.delete');
    Route::get('/onboarding/verification-stats', [App\Http\Controllers\Admin\OnboardingController::class, 'verificationStats'])->name('onboarding.verification');
    Route::post('/onboarding/user/{user}/resend-verification', [App\Http\Controllers\Admin\OnboardingController::class, 'resendVerification'])->name('onboarding.resendVerification');
    Route::post('/onboarding/user/{user}/manually-verify', [App\Http\Controllers\Admin\OnboardingController::class, 'manuallyVerify'])->name('onboarding.manuallyVerify');

    // AI Music Generation
    Route::get('/ai-music', [App\Http\Controllers\Admin\AiMusicController::class, 'index'])->name('ai-music.index');
    Route::get('/ai-music/providers', [App\Http\Controllers\Admin\AiMusicController::class, 'providers'])->name('ai-music.providers');
    Route::get('/ai-music/providers/create', [App\Http\Controllers\Admin\AiMusicController::class, 'createProvider'])->name('ai-music.providers.create');
    Route::post('/ai-music/providers', [App\Http\Controllers\Admin\AiMusicController::class, 'storeProvider'])->name('ai-music.providers.store');
    Route::get('/ai-music/providers/{provider}/edit', [App\Http\Controllers\Admin\AiMusicController::class, 'editProvider'])->name('ai-music.providers.edit');
    Route::put('/ai-music/providers/{provider}', [App\Http\Controllers\Admin\AiMusicController::class, 'updateProvider'])->name('ai-music.providers.update');
    Route::delete('/ai-music/providers/{provider}', [App\Http\Controllers\Admin\AiMusicController::class, 'deleteProvider'])->name('ai-music.providers.delete');
    Route::get('/ai-music/models', [App\Http\Controllers\Admin\AiMusicController::class, 'models'])->name('ai-music.models');
    Route::get('/ai-music/models/create', [App\Http\Controllers\Admin\AiMusicController::class, 'createModel'])->name('ai-music.models.create');
    Route::post('/ai-music/models', [App\Http\Controllers\Admin\AiMusicController::class, 'storeModel'])->name('ai-music.models.store');
    Route::get('/ai-music/models/{model}/edit', [App\Http\Controllers\Admin\AiMusicController::class, 'editModel'])->name('ai-music.models.edit');
    Route::put('/ai-music/models/{model}', [App\Http\Controllers\Admin\AiMusicController::class, 'updateModel'])->name('ai-music.models.update');
    Route::delete('/ai-music/models/{model}', [App\Http\Controllers\Admin\AiMusicController::class, 'deleteModel'])->name('ai-music.models.delete');
    Route::get('/ai-music/generations', [App\Http\Controllers\Admin\AiMusicController::class, 'generations'])->name('ai-music.generations');
    Route::get('/ai-music/storage', [App\Http\Controllers\Admin\AiMusicController::class, 'storageSettings'])->name('ai-music.storage');
    Route::get('/ai-music/storage/create', [App\Http\Controllers\Admin\AiMusicController::class, 'createStorage'])->name('ai-music.storage.create');
    Route::post('/ai-music/storage', [App\Http\Controllers\Admin\AiMusicController::class, 'storeStorage'])->name('ai-music.storage.store');
    Route::get('/ai-music/storage/{storage}/edit', [App\Http\Controllers\Admin\AiMusicController::class, 'editStorage'])->name('ai-music.storage.edit');
    Route::put('/ai-music/storage/{storage}', [App\Http\Controllers\Admin\AiMusicController::class, 'updateStorage'])->name('ai-music.storage.update');
    Route::delete('/ai-music/storage/{storage}', [App\Http\Controllers\Admin\AiMusicController::class, 'deleteStorage'])->name('ai-music.storage.delete');

    // Genre Management
    Route::resource('genres', GenreController::class);
    Route::post('genres/{genre}/toggle', [GenreController::class, 'toggle'])->name('genres.toggle');
    Route::post('genres/reorder', [GenreController::class, 'reorder'])->name('genres.reorder');
    Route::get('genres/update-counts', [GenreController::class, 'updateCounts'])->name('genres.update-counts');

    // Feature Flags
    Route::get('features', [FeatureFlagController::class, 'index'])->name('features.index');
    Route::post('features/{feature}/toggle', [FeatureFlagController::class, 'toggle'])->name('features.toggle');
    Route::post('features/bulk-toggle', [FeatureFlagController::class, 'bulkToggle'])->name('features.bulk-toggle');
    Route::put('features/{feature}', [FeatureFlagController::class, 'update'])->name('features.update');
    Route::get('features/seed', [FeatureFlagController::class, 'seed'])->name('features.seed');

    // Security Settings
    Route::get('security', [App\Http\Controllers\Admin\SecuritySettingsController::class, 'index'])->name('security.index');
    Route::put('security', [App\Http\Controllers\Admin\SecuritySettingsController::class, 'update'])->name('security.update');

    // Storage Settings
    Route::get('storage', [App\Http\Controllers\Admin\StorageSettingsController::class, 'index'])->name('storage.index');
    Route::put('storage', [App\Http\Controllers\Admin\StorageSettingsController::class, 'update'])->name('storage.update');
    Route::post('storage/{provider}/configure', [App\Http\Controllers\Admin\StorageSettingsController::class, 'configure'])->name('storage.configure');

    // Theme Management
    Route::get('themes', [App\Http\Controllers\Admin\ThemeController::class, 'index'])->name('themes.index');
    Route::post('themes/{theme}/activate', [App\Http\Controllers\Admin\ThemeController::class, 'activate'])->name('themes.activate');
    Route::get('themes/{themeKey}/preview', [App\Http\Controllers\Admin\ThemeController::class, 'preview'])->name('themes.preview');
    Route::post('themes/{theme}/customize', [App\Http\Controllers\Admin\ThemeController::class, 'customize'])->name('themes.customize');
    Route::post('themes/{theme}/reset', [App\Http\Controllers\Admin\ThemeController::class, 'reset'])->name('themes.reset');

    // Dynamic Theme Settings
    Route::get('settings/theme', [App\Http\Controllers\Admin\ThemeSettingsController::class, 'index'])->name('settings.theme');
    Route::post('settings/theme', [App\Http\Controllers\Admin\ThemeSettingsController::class, 'update'])->name('settings.theme.update');

    // Admin Track Trials
    Route::resource('track-trials', App\Http\Controllers\Admin\TrackTrialController::class, ['as' => 'admin']);
    Route::get('creators', [App\Http\Controllers\Admin\TrackTrialController::class, 'users'])->name('admin.creators.index');
    Route::post('creators/{user}/toggle', [App\Http\Controllers\Admin\TrackTrialController::class, 'toggleCreator'])->name('admin.creators.toggle');
    Route::post('creators/{user}/title', [App\Http\Controllers\Admin\TrackTrialController::class, 'updateCreatorTitle'])->name('admin.creators.title');

    // Language Manager
    Route::get('languages', [App\Http\Controllers\Admin\LanguageController::class, 'index'])->name('languages.index');
    Route::post('languages', [App\Http\Controllers\Admin\LanguageController::class, 'store'])->name('languages.store');
    Route::get('languages/{language}/translations', [App\Http\Controllers\Admin\LanguageController::class, 'translations'])->name('languages.translations');
    Route::post('languages/translations/{translation}', [App\Http\Controllers\Admin\LanguageController::class, 'updateTranslation'])->name('languages.update-translation');
    Route::post('languages/{language}/default', [App\Http\Controllers\Admin\LanguageController::class, 'setDefault'])->name('languages.default');
    Route::post('languages/{language}/toggle', [App\Http\Controllers\Admin\LanguageController::class, 'toggleActive'])->name('languages.toggle');

    // General Settings
    Route::get('settings/general', [App\Http\Controllers\Admin\GeneralSettingsController::class, 'index'])->name('settings.general');
    Route::post('settings/general', [App\Http\Controllers\Admin\GeneralSettingsController::class, 'update'])->name('settings.general.update');

    // System Configuration (FFMPEG, Features, etc.)
    Route::get('system', [App\Http\Controllers\Admin\SystemConfigurationController::class, 'index'])->name('system.index');
    Route::put('system', [App\Http\Controllers\Admin\SystemConfigurationController::class, 'update'])->name('system.update');
    Route::post('system/test-ffmpeg', [App\Http\Controllers\Admin\SystemConfigurationController::class, 'testFFMPEG'])->name('system.test-ffmpeg');
    Route::post('system/download-ffmpeg', [App\Http\Controllers\Admin\SystemConfigurationController::class, 'downloadFFMPEG'])->name('system.download-ffmpeg');

    // Radio Stations Management
    Route::resource('radio', App\Http\Controllers\Admin\RadioStationController::class);
    Route::post('radio/{radio}/tracks', [App\Http\Controllers\Admin\RadioStationController::class, 'addTrack'])->name('radio.add-track');
    Route::delete('radio/{radio}/tracks/{playlist}', [App\Http\Controllers\Admin\RadioStationController::class, 'removeTrack'])->name('radio.remove-track');
    Route::post('radio/{radio}/reorder', [App\Http\Controllers\Admin\RadioStationController::class, 'reorderPlaylist'])->name('radio.reorder');
    Route::get('radio/{radio}/analytics', [App\Http\Controllers\Admin\RadioStationController::class, 'analytics'])->name('radio.analytics');

    // Feature Access Management (Pro vs Free)
    Route::get('features', [App\Http\Controllers\Admin\FeatureAccessController::class, 'index'])->name('features.index');
    Route::put('features/{feature}', [App\Http\Controllers\Admin\FeatureAccessController::class, 'update'])->name('features.update');
    Route::post('features/{feature}/toggle-beta', [App\Http\Controllers\Admin\FeatureAccessController::class, 'toggleBeta'])->name('features.toggle-beta');
    Route::post('features/{feature}/toggle-enabled', [App\Http\Controllers\Admin\FeatureAccessController::class, 'toggleEnabled'])->name('features.toggle-enabled');

    // Security Settings
    Route::get('security', [App\Http\Controllers\Admin\SecuritySettingsController::class, 'index'])->name('security.index');
    Route::put('security', [App\Http\Controllers\Admin\SecuritySettingsController::class, 'update'])->name('security.update');
    Route::post('security/test-recaptcha', [App\Http\Controllers\Admin\SecuritySettingsController::class, 'testRecaptcha'])->name('security.test-recaptcha');
    Route::post('security/clear-lockouts', [App\Http\Controllers\Admin\SecuritySettingsController::class, 'clearLoginLockouts'])->name('security.clear-lockouts');

    // Storage Settings
    Route::get('storage', [App\Http\Controllers\Admin\StorageSettingsController::class, 'index'])->name('storage.index');
    Route::put('storage', [App\Http\Controllers\Admin\StorageSettingsController::class, 'update'])->name('storage.update');
    Route::post('storage/test-connection', [App\Http\Controllers\Admin\StorageSettingsController::class, 'testConnection'])->name('storage.test-connection');
    Route::get('storage/stats', [App\Http\Controllers\Admin\StorageSettingsController::class, 'getStorageStats'])->name('storage.stats');

    // Social Login Providers
    Route::get('social', [App\Http\Controllers\Admin\SocialProviderController::class, 'index'])->name('social.index');
    Route::put('social/{provider}', [App\Http\Controllers\Admin\SocialProviderController::class, 'update'])->name('social.update');
    Route::post('social/{provider}/toggle', [App\Http\Controllers\Admin\SocialProviderController::class, 'toggle'])->name('social.toggle');
    Route::post('social/{provider}/test', [App\Http\Controllers\Admin\SocialProviderController::class, 'test'])->name('social.test');

    // Cache Settings
    Route::get('cache', [App\Http\Controllers\Admin\CacheSettingsController::class, 'index'])->name('cache.index');
    Route::put('cache', [App\Http\Controllers\Admin\CacheSettingsController::class, 'update'])->name('cache.update');
    Route::post('cache/test-redis', [App\Http\Controllers\Admin\CacheSettingsController::class, 'testRedis'])->name('cache.test-redis');
    Route::post('cache/clear', [App\Http\Controllers\Admin\CacheSettingsController::class, 'clearCache'])->name('cache.clear');
    Route::post('cache/optimize', [App\Http\Controllers\Admin\CacheSettingsController::class, 'optimizeCache'])->name('cache.optimize');
    Route::get('cache/stats', [App\Http\Controllers\Admin\CacheSettingsController::class, 'getStats'])->name('cache.stats');

    // Theme Management
    Route::get('themes', [App\Http\Controllers\Admin\ThemeController::class, 'index'])->name('themes.index');
    Route::post('themes/{theme}/activate', [App\Http\Controllers\Admin\ThemeController::class, 'activate'])->name('themes.activate');
    Route::post('themes/{theme}/set-default', [App\Http\Controllers\Admin\ThemeController::class, 'setDefault'])->name('themes.set-default');
    Route::get('themes/{theme}/preview', [App\Http\Controllers\Admin\ThemeController::class, 'preview'])->name('themes.preview');
    Route::put('themes/{theme}', [App\Http\Controllers\Admin\ThemeController::class, 'update'])->name('themes.update');

    // Database Management
    Route::get('database', [App\Http\Controllers\Admin\DatabaseController::class, 'index'])->name('database.index');
    Route::put('database/{database}', [App\Http\Controllers\Admin\DatabaseController::class, 'update'])->name('database.update');
    Route::post('database/{database}/test', [App\Http\Controllers\Admin\DatabaseController::class, 'testConnection'])->name('database.test');
    Route::post('database/{database}/set-primary', [App\Http\Controllers\Admin\DatabaseController::class, 'setPrimary'])->name('database.set-primary');
    Route::get('database/monitor', [App\Http\Controllers\Admin\DatabaseController::class, 'monitorHealth'])->name('database.monitor');
    Route::post('database/failover', [App\Http\Controllers\Admin\DatabaseController::class, 'forceFailover'])->name('database.failover');
    Route::get('database/stats', [App\Http\Controllers\Admin\DatabaseController::class, 'getStats'])->name('database.stats');

    // Store Management
    Route::resource('store', App\Http\Controllers\Admin\StoreManagementController::class);
    Route::get('store/orders', [App\Http\Controllers\Admin\StoreManagementController::class, 'orders'])->name('store.orders');
    Route::get('store/orders/{order}', [App\Http\Controllers\Admin\StoreManagementController::class, 'showOrder'])->name('store.orders.show');
    Route::post('store/orders/{order}/ship', [App\Http\Controllers\Admin\StoreManagementController::class, 'shipOrder'])->name('store.orders.ship');

    // Advertising Management
    Route::resource('advertising', App\Http\Controllers\Admin\AdvertisingController::class);
    Route::get('advertising/analytics', [App\Http\Controllers\Admin\AdvertisingController::class, 'analytics'])->name('advertising.analytics');
    Route::post('advertising/{ad}/approve', [App\Http\Controllers\Admin\AdvertisingController::class, 'approve'])->name('advertising.approve');
    Route::post('advertising/{ad}/reject', [App\Http\Controllers\Admin\AdvertisingController::class, 'reject'])->name('advertising.reject');

    // Banner Management
    Route::resource('banners', App\Http\Controllers\Admin\BannerController::class);
    Route::post('banners/{banner}/publish', [App\Http\Controllers\Admin\BannerController::class, 'publish'])->name('banners.publish');
    Route::get('banners/{banner}/analytics', [App\Http\Controllers\Admin\BannerController::class, 'analytics'])->name('banners.analytics');

    // Boost Packages
    Route::resource('boost-packages', App\Http\Controllers\Admin\BoostPackageController::class);

    // Translations
    Route::get('translations', [App\Http\Controllers\Admin\TranslationController::class, 'index'])->name('translations.index');
    Route::put('translations/{translationKey}', [App\Http\Controllers\Admin\TranslationController::class, 'update'])->name('translations.update');
    Route::post('translations/bulk-update', [App\Http\Controllers\Admin\TranslationController::class, 'bulkUpdate'])->name('translations.bulk-update');
    Route::get('translations/export', [App\Http\Controllers\Admin\TranslationController::class, 'export'])->name('translations.export');
    Route::post('translations/import', [App\Http\Controllers\Admin\TranslationController::class, 'import'])->name('translations.import');
    Route::post('translations/clear-cache', [App\Http\Controllers\Admin\TranslationController::class, 'clearCache'])->name('translations.clear-cache');
});

// Mobile API Routes (Public)
Route::prefix('api/v1')->group(function () {
    Route::get('/tracks', [App\Http\Controllers\Api\ApiController::class, 'tracks']);
    Route::get('/tracks/{id}', [App\Http\Controllers\Api\ApiController::class, 'track']);
    Route::get('/albums', [App\Http\Controllers\Api\ApiController::class, 'albums']);
    Route::get('/albums/{id}', [App\Http\Controllers\Api\ApiController::class, 'album']);
    Route::get('/artists', [App\Http\Controllers\Api\ApiController::class, 'artists']);
    Route::get('/artists/{id}', [App\Http\Controllers\Api\ApiController::class, 'artist']);
    Route::get('/playlists', [App\Http\Controllers\Api\ApiController::class, 'playlists']);
    Route::get('/playlists/{id}', [App\Http\Controllers\Api\ApiController::class, 'playlist']);
    Route::get('/search', [App\Http\Controllers\Api\ApiController::class, 'search']);
});


// Search Routes (Public)
Route::get('/search', [App\Http\Controllers\SearchController::class, 'index'])->name('search.index');
Route::get('/api/search', [App\Http\Controllers\SearchController::class, 'api'])->name('search.api');
Route::get('/api/search/autocomplete', [App\Http\Controllers\SearchController::class, 'autocomplete'])->name('search.autocomplete');

// Social Login Routes (Public)
Route::get('auth/{provider}', [App\Http\Controllers\Auth\SocialLoginController::class, 'redirect'])->name('social.redirect');
Route::get('auth/{provider}/callback', [App\Http\Controllers\Auth\SocialLoginController::class, 'callback'])->name('social.callback');

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::post('/track-trials/{trial}/upload', [App\Http\Controllers\User\TrackTrialController::class, 'store'])->name('track-trials.store');

    // Notifications
    Route::get('/notifications', [App\Http\Controllers\User\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/api/notifications/unread', [App\Http\Controllers\User\NotificationController::class, 'unread'])->name('notifications.unread');
    Route::post('/notifications/{id}/read', [App\Http\Controllers\User\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [App\Http\Controllers\User\NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
    Route::delete('/notifications/{id}', [App\Http\Controllers\User\NotificationController::class, 'delete'])->name('notifications.delete');
    Route::delete('/notifications', [App\Http\Controllers\User\NotificationController::class, 'deleteAll'])->name('notifications.deleteAll');

    // Artist Verification
    Route::get('/verification', [App\Http\Controllers\User\VerificationController::class, 'index'])->name('verification.index');
    Route::get('/verification/request', [App\Http\Controllers\User\VerificationController::class, 'create'])->name('verification.create');
    Route::post('/verification/request', [App\Http\Controllers\User\VerificationController::class, 'store'])->name('verification.store');

    // Tracks
    Route::resource('tracks', App\Http\Controllers\User\TrackController::class);
    Route::get('/tracks/create', [App\Http\Controllers\User\TrackController::class, 'create'])->name('tracks.create');
    Route::post('/tracks/{track}/share/feed', [App\Http\Controllers\User\TrackController::class, 'shareToFeed'])->name('tracks.share.feed');
    Route::post('/tracks/{track}/share/zipcode', [App\Http\Controllers\User\TrackController::class, 'shareToZipcode'])->name('tracks.share.zipcode');

    // User Ads & Boosts
    Route::get('/promote', [App\Http\Controllers\User\BoostController::class, 'index'])->name('user.boost.index');
    Route::get('/promote/ad/create', [App\Http\Controllers\User\BoostController::class, 'createAd'])->name('user.boost.create-ad');
    Route::post('/promote/ad', [App\Http\Controllers\User\BoostController::class, 'storeAd'])->name('user.boost.store-ad');
    Route::get('/promote/boost/create', [App\Http\Controllers\User\BoostController::class, 'createBoost'])->name('user.boost.create-boost');
    Route::post('/promote/boost', [App\Http\Controllers\User\BoostController::class, 'storeBoost'])->name('user.boost.store-boost');

    // Radio Stations (User)
    Route::get('/radio', [App\Http\Controllers\User\RadioController::class, 'index'])->name('radio.index');
    Route::get('/radio/{slug}', [App\Http\Controllers\User\RadioController::class, 'show'])->name('radio.show');
    Route::post('/radio/{slug}/listen', [App\Http\Controllers\User\RadioController::class, 'listen'])->name('radio.listen');
    Route::post('/radio/{slug}/disconnect', [App\Http\Controllers\User\RadioController::class, 'disconnect'])->name('radio.disconnect');
    Route::get('/radio/{slug}/current-track', [App\Http\Controllers\User\RadioController::class, 'getCurrentTrack'])->name('radio.current-track');
    Route::get('/radio/{slug}/embed', [App\Http\Controllers\User\RadioController::class, 'embed'])->name('radio.embed');

    // My Radio Stations (User Created)
    Route::resource('my-radio', App\Http\Controllers\User\MyRadioController::class);
    Route::post('my-radio/{myRadio}/tracks', [App\Http\Controllers\User\MyRadioController::class, 'addTrack'])->name('my-radio.add-track');
    Route::delete('my-radio/{myRadio}/tracks/{playlist}', [App\Http\Controllers\User\MyRadioController::class, 'removeTrack'])->name('my-radio.remove-track');

    // My Podcasts (User Created)
    Route::resource('my-podcasts', App\Http\Controllers\User\MyPodcastController::class);
    Route::get('my-podcasts/{myPodcast}/episodes/create', [App\Http\Controllers\User\MyPodcastController::class, 'createEpisode'])->name('my-podcasts.create-episode');
    Route::post('my-podcasts/{myPodcast}/episodes', [App\Http\Controllers\User\MyPodcastController::class, 'storeEpisode'])->name('my-podcasts.store-episode');
    Route::delete('my-podcasts/{myPodcast}/episodes/{episode}', [App\Http\Controllers\User\MyPodcastController::class, 'deleteEpisode'])->name('my-podcasts.delete-episode');

    // Flash Albums (Artist Created) - PRO ONLY & BETA
    Route::middleware(['feature.access:flash_album'])->group(function () {
        Route::resource('flash-albums', App\Http\Controllers\User\FlashAlbumController::class);
        Route::post('flash-albums/build-from-album', [App\Http\Controllers\User\FlashAlbumController::class, 'buildFromAlbum'])->name('flash-albums.build-from-album');
        Route::get('flash-albums/{flashAlbum}/preview', [App\Http\Controllers\User\FlashAlbumController::class, 'preview'])->name('flash-albums.preview');
    });

    // Flash Album Shop (Browse & Purchase)
    Route::get('shop/flash-albums', [App\Http\Controllers\User\FlashAlbumShopController::class, 'index'])->name('shop.flash-albums.index');
    Route::get('shop/flash-albums/{slug}', [App\Http\Controllers\User\FlashAlbumShopController::class, 'show'])->name('shop.flash-albums.show');
    Route::post('shop/flash-albums/{flashAlbum}/purchase', [App\Http\Controllers\User\FlashAlbumShopController::class, 'purchase'])->name('shop.flash-albums.purchase');
    Route::get('shop/flash-albums/orders/{order}/payment', [App\Http\Controllers\User\FlashAlbumShopController::class, 'payment'])->name('flash-albums.payment');
    Route::post('shop/flash-albums/orders/{order}/payment', [App\Http\Controllers\User\FlashAlbumShopController::class, 'processPayment'])->name('flash-albums.process-payment');
    Route::get('shop/flash-albums/orders/{order}/confirmation', [App\Http\Controllers\User\FlashAlbumShopController::class, 'orderConfirmation'])->name('flash-albums.order-confirmation');
    Route::get('shop/flash-albums/my-orders', [App\Http\Controllers\User\FlashAlbumShopController::class, 'myOrders'])->name('flash-albums.my-orders');
    Route::post('shop/flash-albums/download', [App\Http\Controllers\User\FlashAlbumShopController::class, 'downloadDigitalCopy'])->name('flash-albums.download-digital');

    // Feed
    Route::get('/feed', [App\Http\Controllers\User\FeedController::class, 'index'])->name('feed');

    // Subscriptions
    Route::get('/subscription', [App\Http\Controllers\User\SubscriptionController::class, 'index'])->name('subscription.index');
    Route::post('/subscription', [App\Http\Controllers\User\SubscriptionController::class, 'store'])->name('subscription.store');

    // Zipcodes
    Route::get('/zipcodes', [App\Http\Controllers\User\ZipcodeController::class, 'index'])->name('zipcodes.index');
    Route::post('/zipcodes/purchase', [App\Http\Controllers\User\ZipcodeController::class, 'purchase'])->name('zipcodes.purchase');
    Route::get('/zipcodes/{zipcode}/manage', [App\Http\Controllers\User\ZipcodeController::class, 'manage'])->name('zipcodes.manage');
    Route::post('/zipcodes/{zipcode}/join', [App\Http\Controllers\User\ZipcodeController::class, 'join'])->name('zipcodes.join');

    // Zipcode Groups
    Route::get('/zipcodes/{zipcode}/groups/create', [App\Http\Controllers\User\ZipcodeGroupController::class, 'create'])->name('zipcodes.groups.create');
    Route::post('/zipcodes/{zipcode}/groups', [App\Http\Controllers\User\ZipcodeGroupController::class, 'store'])->name('zipcodes.groups.store');
    Route::get('/zipcodes/{zipcode}/groups/{group}', [App\Http\Controllers\User\ZipcodeGroupController::class, 'show'])->name('zipcodes.groups.show');
    Route::post('/zipcodes/{zipcode}/groups/{group}/join', [App\Http\Controllers\User\ZipcodeGroupController::class, 'join'])->name('zipcodes.groups.join');
    Route::post('/zipcodes/{zipcode}/groups/{group}/leave', [App\Http\Controllers\User\ZipcodeGroupController::class, 'leave'])->name('zipcodes.groups.leave');

    // Sound Bank
    Route::get('/api/sounds', [App\Http\Controllers\SoundBankController::class, 'index'])->name('sounds.index');

    // Artist Requests
    Route::get('/artist/apply', [App\Http\Controllers\User\ArtistRequestController::class, 'create'])->name('artist.request.create');
    Route::post('/artist/apply', [App\Http\Controllers\User\ArtistRequestController::class, 'store'])->name('artist.request.store');

    // Albums
    Route::resource('albums', App\Http\Controllers\User\AlbumController::class)->only(['index', 'create', 'store', 'show']);

    // Events
    Route::get('/events', [App\Http\Controllers\User\EventController::class, 'index'])->name('events.index');
    Route::get('/events/create', [App\Http\Controllers\User\EventController::class, 'create'])->name('events.create');
    Route::post('/events', [App\Http\Controllers\User\EventController::class, 'store'])->name('events.store');
    Route::get('/events/my-events', [App\Http\Controllers\User\EventController::class, 'myEvents'])->name('events.myEvents');
    Route::get('/events/my-tickets', [App\Http\Controllers\User\EventController::class, 'myTickets'])->name('events.myTickets');
    Route::get('/events/{event}', [App\Http\Controllers\User\EventController::class, 'show'])->name('events.show');
    Route::get('/events/{event}/edit', [App\Http\Controllers\User\EventController::class, 'edit'])->name('events.edit');
    Route::put('/events/{event}', [App\Http\Controllers\User\EventController::class, 'update'])->name('events.update');

    // Tickets
    Route::post('/events/{event}/tickets/{ticketType}/purchase', [App\Http\Controllers\User\TicketController::class, 'purchase'])->name('tickets.purchase');
    Route::get('/tickets/{ticket}/download', [App\Http\Controllers\User\TicketController::class, 'downloadTicket'])->name('tickets.download');
    Route::post('/events/{event}/ticket-types', [App\Http\Controllers\User\TicketController::class, 'addTicketType'])->name('tickets.addType');
    Route::put('/ticket-types/{ticketType}', [App\Http\Controllers\User\TicketController::class, 'updateTicketType'])->name('tickets.updateType');

    // Song Battles
    Route::get('/song-battles', [App\Http\Controllers\User\SongBattleController::class, 'index'])->name('song_battles.index');
    Route::get('/song-battles/create', [App\Http\Controllers\User\SongBattleController::class, 'create'])->name('song_battles.create');
    Route::post('/song-battles', [App\Http\Controllers\User\SongBattleController::class, 'store'])->name('song_battles.store');
    Route::get('/song-battles/{songBattle}', [App\Http\Controllers\User\SongBattleController::class, 'show'])->name('song_battles.show');
    Route::post('/song-battles/versions/{version}/vote', [App\Http\Controllers\User\SongBattleController::class, 'vote'])->name('song_battles.vote');
    Route::post('/song-battles/versions/{version}/comment', [App\Http\Controllers\User\SongBattleController::class, 'comment'])->name('song_battles.comment');
    Route::post('/song-battles/versions/{version}/play', [App\Http\Controllers\User\SongBattleController::class, 'registerPlay'])->name('song_battles.play');
    Route::get('/song-battles/hall-of-fame', [App\Http\Controllers\User\SongBattleController::class, 'hallOfFame'])->name('song_battles.hall_of_fame');
    Route::post('/song-battles/{songBattle}/share/feed', [App\Http\Controllers\User\SongBattleController::class, 'shareToFeed'])->name('song_battles.share.feed');
    Route::post('/song-battles/{songBattle}/share/zipcode', [App\Http\Controllers\User\SongBattleController::class, 'shareToZipcode'])->name('song_battles.share.zipcode');

    // Donations & Tips
    Route::get('/donate/{artist}', [App\Http\Controllers\User\DonationController::class, 'showDonationForm'])->name('donations.create');
    Route::post('/donate/{artist}', [App\Http\Controllers\User\DonationController::class, 'donate'])->name('donations.store');
    Route::get('/tip', [App\Http\Controllers\User\DonationController::class, 'showTipForm'])->name('tips.create');
    Route::post('/tip', [App\Http\Controllers\User\DonationController::class, 'tip'])->name('tips.store');

    // My Donations & Tips
    Route::get('/my-donations', [App\Http\Controllers\User\DonationController::class, 'myDonations'])->name('donations.my');
    Route::get('/my-tips', [App\Http\Controllers\User\DonationController::class, 'myTips'])->name('tips.my');

    // Received Donations & Tips
    Route::get('/received-donations', [App\Http\Controllers\User\DonationController::class, 'receivedDonations'])->name('donations.received');
    Route::get('/received-tips', [App\Http\Controllers\User\DonationController::class, 'receivedTips'])->name('tips.received');

    // Donation Settings
    Route::get('/donation-settings', [App\Http\Controllers\User\DonationController::class, 'donationSettings'])->name('donation_settings');
    Route::put('/donation-settings', [App\Http\Controllers\User\DonationController::class, 'updateDonationSettings'])->name('donation_settings.update');

    // Gifts
    Route::get('/my-gifts', [App\Http\Controllers\User\GiftController::class, 'index'])->name('gifts.my');
    Route::post('/gifts/{gift}/claim', [App\Http\Controllers\User\GiftController::class, 'claim'])->name('gifts.claim');

    // Song Battle Rewards
    Route::get('/my-rewards', [App\Http\Controllers\User\RewardController::class, 'index'])->name('rewards.my');
    Route::post('/rewards/{reward}/claim', [App\Http\Controllers\User\RewardController::class, 'claim'])->name('rewards.claim');

    // Purchases
    Route::get('/purchases', [App\Http\Controllers\User\PurchaseController::class, 'index'])->name('purchases.index');

    // Track Purchase
    Route::get('/purchase/track/{track}', [App\Http\Controllers\User\PurchaseController::class, 'showTrackPurchase'])->name('purchases.track');
    Route::post('/purchase/track/{track}', [App\Http\Controllers\User\PurchaseController::class, 'purchaseTrack'])->name('purchases.track.buy');
    Route::get('/purchase/track/{sale}/success', [App\Http\Controllers\User\PurchaseController::class, 'success'])->name('purchases.success');
    Route::get('/download/track/{token}', [App\Http\Controllers\User\PurchaseController::class, 'downloadTrack'])->name('purchases.download.track');

    // Album Purchase
    Route::get('/purchase/album/{album}', [App\Http\Controllers\User\PurchaseController::class, 'showAlbumPurchase'])->name('purchases.album');
    Route::post('/purchase/album/{album}', [App\Http\Controllers\User\PurchaseController::class, 'purchaseAlbum'])->name('purchases.album.buy');
    Route::get('/purchase/album/{sale}/success', [App\Http\Controllers\User\PurchaseController::class, 'successAlbum'])->name('purchases.success.album');
    Route::get('/download/album/{token}', [App\Http\Controllers\User\PurchaseController::class, 'downloadAlbum'])->name('purchases.download.album');

    // Pending Payment
    Route::get('/purchase/pending/{transaction}', [App\Http\Controllers\User\PurchaseController::class, 'pending'])->name('purchases.pending');

    // Music Distribution (User)
    Route::get('/distribute', [App\Http\Controllers\User\DistributionController::class, 'index'])->name('distribution.index');
    Route::get('/distribute/create', [App\Http\Controllers\User\DistributionController::class, 'create'])->name('distribution.create');
    Route::post('/distribute', [App\Http\Controllers\User\DistributionController::class, 'store'])->name('distribution.store');
    Route::get('/distribute/{distribution}', [App\Http\Controllers\User\DistributionController::class, 'show'])->name('distribution.show');
    Route::get('/distribution/earnings', [App\Http\Controllers\User\DistributionController::class, 'earnings'])->name('distribution.earnings');

    // Gift System (User)
    Route::get('/gifts', [App\Http\Controllers\User\GiftController::class, 'index'])->name('gifts.index');
    Route::post('/tracks/{track}/gift', [App\Http\Controllers\User\GiftController::class, 'send'])->name('gifts.send');
    Route::get('/gifts/received', [App\Http\Controllers\User\GiftController::class, 'received'])->name('gifts.received');
    Route::get('/gifts/earnings', [App\Http\Controllers\User\GiftController::class, 'earnings'])->name('gifts.earnings');

    // Reactions System
    Route::post('/posts/{post}/react', [App\Http\Controllers\User\ReactionController::class, 'togglePostReaction'])->name('posts.react');
    Route::get('/posts/{post}/reactions', [App\Http\Controllers\User\ReactionController::class, 'getPostReactions'])->name('posts.reactions');
    Route::post('/comments/{comment}/react', [App\Http\Controllers\User\ReactionController::class, 'toggleCommentReaction'])->name('comments.react');
    Route::get('/comments/{comment}/reactions', [App\Http\Controllers\User\ReactionController::class, 'getCommentReactions'])->name('comments.reactions');

    // Playlist Routes
    Route::resource('playlists', PlaylistController::class);
    Route::post('/playlists/{playlist}/tracks', [PlaylistController::class, 'addTrack'])->name('playlists.addTrack');
    Route::delete('/playlists/{playlist}/tracks/{track}', [PlaylistController::class, 'removeTrack'])->name('playlists.removeTrack');
    Route::post('/playlists/{playlist}/reorder', [PlaylistController::class, 'reorderTracks'])->name('playlists.reorder');
    Route::post('/playlists/{playlist}/follow', [PlaylistController::class, 'follow'])->name('playlists.follow');
    Route::delete('/playlists/{playlist}/follow', [PlaylistController::class, 'unfollow'])->name('playlists.unfollow');

    // Social Routes
    Route::get('/social/feed', [SocialController::class, 'feed'])->name('social.feed');
    Route::post('/users/{user}/follow', [SocialController::class, 'follow'])->name('users.follow');
    Route::delete('/users/{user}/follow', [SocialController::class, 'unfollow'])->name('users.unfollow');
    Route::get('/social/followers', [SocialController::class, 'followers'])->name('social.followers');
    Route::get('/social/following', [SocialController::class, 'following'])->name('social.following');
    Route::post('/social/posts', [SocialController::class, 'createPost'])->name('social.posts.create');
    Route::delete('/social/posts/{post}', [SocialController::class, 'deletePost'])->name('social.posts.delete');
    Route::get('/social/activity', [SocialController::class, 'activity'])->name('social.activity');

    // Discovery Routes
    Route::get('/discovery', [DiscoveryController::class, 'index'])->name('discovery.index');
    Route::get('/discovery/genre/{genre}', [DiscoveryController::class, 'genre'])->name('discovery.genre');
    Route::get('/discovery/trending', [DiscoveryController::class, 'trending'])->name('discovery.trending');
    Route::get('/discovery/new-releases', [DiscoveryController::class, 'newReleases'])->name('discovery.new-releases');
    Route::get('/discovery/charts', [DiscoveryController::class, 'charts'])->name('discovery.charts');
    Route::get('/discovery/for-you', [DiscoveryController::class, 'forYou'])->name('discovery.for-you');

    // AI Music Routes
    Route::get('/ai-music', [App\Http\Controllers\User\AiMusicController::class, 'index'])->name('ai-music.index');
    Route::post('/ai-music/generate', [App\Http\Controllers\User\AiMusicController::class, 'generate'])->name('ai-music.generate');
    Route::get('/ai-music/history', [App\Http\Controllers\User\AiMusicController::class, 'history'])->name('ai-music.history');

    // Blockchain Routes
    Route::get('/blockchain/portfolio', [App\Http\Controllers\User\BlockchainController::class, 'index'])->name('blockchain.portfolio');
    Route::post('/blockchain/invest/{track}', [App\Http\Controllers\User\BlockchainController::class, 'invest'])->name('blockchain.invest');
    Route::post('/blockchain/sell/{investment}', [App\Http\Controllers\User\BlockchainController::class, 'sell'])->name('blockchain.sell');

    // Podcast Routes
    Route::get('/podcasts', [App\Http\Controllers\User\PodcastController::class, 'index'])->name('podcasts.index');
    Route::get('/podcasts/{podcast}', [App\Http\Controllers\User\PodcastController::class, 'show'])->name('podcasts.show');
    Route::get('/my-podcasts', [App\Http\Controllers\User\PodcastController::class, 'myPodcasts'])->name('podcasts.my');
    Route::get('/podcasts/create', [App\Http\Controllers\User\PodcastController::class, 'create'])->name('podcasts.create');
    Route::post('/podcasts', [App\Http\Controllers\User\PodcastController::class, 'store'])->name('podcasts.store');
    Route::get('/podcasts/{podcast}/episodes/create', [App\Http\Controllers\User\PodcastController::class, 'createEpisode'])->name('podcasts.episodes.create');
    Route::post('/podcasts/{podcast}/episodes', [App\Http\Controllers\User\PodcastController::class, 'storeEpisode'])->name('podcasts.episodes.store');
    Route::post('/podcasts/{podcast}/subscribe', [App\Http\Controllers\User\PodcastController::class, 'subscribe'])->name('podcasts.subscribe');
    Route::delete('/podcasts/{podcast}/subscribe', [App\Http\Controllers\User\PodcastController::class, 'unsubscribe'])->name('podcasts.unsubscribe');

    // Live Streaming Routes
    Route::get('/livestreams', [App\Http\Controllers\User\LiveStreamController::class, 'index'])->name('livestreams.index');
    Route::get('/livestreams/{stream}', [App\Http\Controllers\User\LiveStreamController::class, 'show'])->name('livestreams.show');
    Route::get('/livestreams/create', [App\Http\Controllers\User\LiveStreamController::class, 'create'])->name('livestreams.create');
    Route::post('/livestreams', [App\Http\Controllers\User\LiveStreamController::class, 'store'])->name('livestreams.store');
    Route::post('/livestreams/{stream}/start', [App\Http\Controllers\User\LiveStreamController::class, 'start'])->name('livestreams.start');
    Route::post('/livestreams/{stream}/end', [App\Http\Controllers\User\LiveStreamController::class, 'end'])->name('livestreams.end');
    Route::post('/livestreams/{stream}/messages', [App\Http\Controllers\User\LiveStreamController::class, 'sendMessage'])->name('livestreams.messages.send');
    Route::get('/livestreams/{stream}/messages', [App\Http\Controllers\User\LiveStreamController::class, 'getMessages'])->name('livestreams.messages.get');

    // Store / Merch Routes
    Route::get('/store', [App\Http\Controllers\User\StoreController::class, 'index'])->name('store.index');
    Route::get('/store/category/{category}', [App\Http\Controllers\User\StoreController::class, 'category'])->name('store.category');
    Route::get('/store/product/{product}', [App\Http\Controllers\User\StoreController::class, 'show'])->name('store.show');
    Route::get('/store/cart', [App\Http\Controllers\User\StoreController::class, 'cart'])->name('store.cart');
    Route::post('/store/cart/{product}', [App\Http\Controllers\User\StoreController::class, 'addToCart'])->name('store.addToCart');
    Route::put('/store/cart/{cartItem}', [App\Http\Controllers\User\StoreController::class, 'updateCart'])->name('store.updateCart');
    Route::delete('/store/cart/{cartItem}', [App\Http\Controllers\User\StoreController::class, 'removeFromCart'])->name('store.removeFromCart');
    Route::get('/store/checkout', [App\Http\Controllers\User\StoreController::class, 'checkout'])->name('store.checkout');
    Route::post('/store/checkout', [App\Http\Controllers\User\StoreController::class, 'processCheckout'])->name('store.processCheckout');
    Route::get('/store/orders', [App\Http\Controllers\User\StoreController::class, 'orders'])->name('store.orders');
    Route::get('/store/orders/{order}', [App\Http\Controllers\User\StoreController::class, 'orderDetails'])->name('store.orderDetails');
    Route::post('/store/product/{product}/review', [App\Http\Controllers\User\StoreController::class, 'addReview'])->name('store.addReview');

    // Fan Clubs Routes
    Route::get('/fan-clubs', [App\Http\Controllers\User\FanClubController::class, 'index'])->name('fan-clubs.index');
    Route::get('/fan-clubs/create', [App\Http\Controllers\User\FanClubController::class, 'create'])->name('fan-clubs.create');
    Route::post('/fan-clubs', [App\Http\Controllers\User\FanClubController::class, 'store'])->name('fan-clubs.store');
    Route::get('/fan-clubs/{fanClub}', [App\Http\Controllers\User\FanClubController::class, 'show'])->name('fan-clubs.show');
    Route::post('/fan-clubs/{fanClub}/join', [App\Http\Controllers\User\FanClubController::class, 'join'])->name('fan-clubs.join');
    Route::post('/fan-clubs/{fanClub}/leave', [App\Http\Controllers\User\FanClubController::class, 'leave'])->name('fan-clubs.leave');

    // Radio Routes
    Route::get('/radio', [App\Http\Controllers\User\RadioController::class, 'index'])->name('radio.index');
    Route::get('/radio/{slug}', [App\Http\Controllers\User\RadioController::class, 'show'])->name('radio.show');
    Route::post('/radio/{slug}/listen', [App\Http\Controllers\User\RadioController::class, 'listen'])->name('radio.listen');
    Route::post('/radio/{slug}/disconnect', [App\Http\Controllers\User\RadioController::class, 'disconnect'])->name('radio.disconnect');
    Route::get('/radio/{slug}/current-track', [App\Http\Controllers\User\RadioController::class, 'getCurrentTrack'])->name('radio.currentTrack');
    Route::get('/radio/{slug}/embed', [App\Http\Controllers\User\RadioController::class, 'embed'])->name('radio.embed');

    // User Products
    Route::resource('products', App\Http\Controllers\User\ProductController::class);

    // User Audio Ads
    Route::resource('ads', App\Http\Controllers\User\AudioAdController::class);
});

// Studio
Route::get('/studio', [\App\Http\Controllers\StudioController::class, 'index'])->name('studio')->middleware(['auth', 'verified']);
