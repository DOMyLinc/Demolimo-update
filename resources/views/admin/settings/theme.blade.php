@extends('layouts.admin')

@section('title', 'Theme Settings')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Theme Customizer</h1>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Global Color Scheme</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.settings.theme.update') }}" method="POST">
                            @csrf

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Primary Color (Brand)</label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color"
                                                name="theme_primary_color" value="{{ $settings['theme_primary_color'] }}"
                                                title="Choose your color">
                                            <input type="text" class="form-control"
                                                value="{{ $settings['theme_primary_color'] }}" readonly>
                                        </div>
                                        <small class="text-muted">Used for buttons, links, and active states.</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Secondary Color (Gradient)</label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color"
                                                name="theme_secondary_color"
                                                value="{{ $settings['theme_secondary_color'] }}" title="Choose your color">
                                            <input type="text" class="form-control"
                                                value="{{ $settings['theme_secondary_color'] }}" readonly>
                                        </div>
                                        <small class="text-muted">Used for gradients and accents.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Background Color</label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color"
                                                name="theme_background_color"
                                                value="{{ $settings['theme_background_color'] }}" title="Choose your color">
                                            <input type="text" class="form-control"
                                                value="{{ $settings['theme_background_color'] }}" readonly>
                                        </div>
                                        <small class="text-muted">Main page background.</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Panel Background</label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color"
                                                name="theme_panel_background_color"
                                                value="{{ $settings['theme_panel_background_color'] }}"
                                                title="Choose your color">
                                            <input type="text" class="form-control"
                                                value="{{ $settings['theme_panel_background_color'] }}" readonly>
                                        </div>
                                        <small class="text-muted">Background for cards and sidebars.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Text Main Color</label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color"
                                                name="theme_text_main_color"
                                                value="{{ $settings['theme_text_main_color'] }}" title="Choose your color">
                                            <input type="text" class="form-control"
                                                value="{{ $settings['theme_text_main_color'] }}" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Text Muted Color</label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color"
                                                name="theme_text_muted_color"
                                                value="{{ $settings['theme_text_muted_color'] }}" title="Choose your color">
                                            <input type="text" class="form-control"
                                                value="{{ $settings['theme_text_muted_color'] }}" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label>Glassmorphism Opacity (0.0 - 1.0)</label>
                                <input type="range" class="form-range" name="theme_glass_opacity" min="0" max="1" step="0.1"
                                    value="{{ $settings['theme_glass_opacity'] }}">
                                <div class="d-flex justify-content-between">
                                    <small>Transparent</small>
                                    <small>Solid</small>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="lavaSwitch"
                                        name="theme_enable_lava" value="1" {{ $settings['theme_enable_lava'] ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="lavaSwitch">Enable Animated Lava
                                        Background</label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg btn-block">
                                <i class="fas fa-save mr-2"></i> Save Theme Settings
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Live Preview</h6>
                    </div>
                    <div class="card-body text-center">
                        <div class="p-4 mb-3 rounded"
                            style="background: {{ $settings['theme_background_color'] }}; color: {{ $settings['theme_text_main_color'] }}; border: 1px solid #ddd;">
                            <h5 style="color: {{ $settings['theme_text_main_color'] }}">Preview Title</h5>
                            <p style="color: {{ $settings['theme_text_muted_color'] }}">This is how your text will look.</p>
                            <button class="btn"
                                style="background: linear-gradient(135deg, {{ $settings['theme_primary_color'] }}, {{ $settings['theme_secondary_color'] }}); color: #fff;">Primary
                                Button</button>
                        </div>
                        <p class="small text-muted">Note: Changes will apply globally to the User Panel and Admin Panel.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection