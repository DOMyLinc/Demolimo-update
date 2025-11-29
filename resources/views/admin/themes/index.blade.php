@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">🎨 Theme Management</h3>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <div class="alert alert-info">
                            <strong>Active Theme:</strong> {{ $activeTheme->display_name }} ({{ $activeTheme->version }})
                        </div>

                        <div class="row">
                            @foreach($themes as $theme)
                                <div class="col-md-6 mb-4">
                                    <div class="card {{ $theme->is_active ? 'border-success' : '' }}">
                                        <div class="card-header"
                                            style="background: linear-gradient(135deg, {{ $theme->color_scheme['primary'] ?? '#000' }}, {{ $theme->color_scheme['secondary'] ?? '#333' }}); color: white;">
                                            <h4>{{ $theme->display_name }}</h4>
                                            <small>v{{ $theme->version }} by {{ $theme->author }}</small>
                                        </div>
                                        <div class="card-body">
                                            <p>{{ $theme->description }}</p>

                                            <h6>Color Scheme:</h6>
                                            <div class="d-flex mb-3">
                                                @foreach($theme->color_scheme ?? [] as $name => $color)
                                                    <div class="text-center mr-2">
                                                        <div
                                                            style="width: 40px; height: 40px; background-color: {{ $color }}; border: 1px solid #ddd; border-radius: 4px;">
                                                        </div>
                                                        <small>{{ ucfirst($name) }}</small>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <h6>Features:</h6>
                                            <div class="mb-3">
                                                @foreach($theme->features ?? [] as $feature)
                                                    <span
                                                        class="badge badge-info">{{ str_replace('_', ' ', ucfirst($feature)) }}</span>
                                                @endforeach
                                            </div>

                                            <div class="mb-3">
                                                @if($theme->is_active)
                                                    <span class="badge badge-success">✓ Active</span>
                                                @endif
                                                @if($theme->is_default)
                                                    <span class="badge badge-primary">★ Default</span>
                                                @endif
                                                @if($theme->supports_landing_page)
                                                    <span class="badge badge-secondary">Landing Page</span>
                                                @endif
                                            </div>

                                            <div class="btn-group" role="group">
                                                @if(!$theme->is_active)
                                                    <form action="{{ route('admin.themes.activate', $theme) }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success btn-sm">Activate</button>
                                                    </form>
                                                @endif

                                                @if(!$theme->is_default)
                                                    <form action="{{ route('admin.themes.set-default', $theme) }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-primary btn-sm">Set as Default</button>
                                                    </form>
                                                @endif

                                                <a href="{{ route('admin.themes.preview', $theme) }}"
                                                    class="btn btn-info btn-sm" target="_blank">Preview</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection