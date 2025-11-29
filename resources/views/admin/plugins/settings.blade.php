@extends('layouts.admin')

@section('page-title', 'Plugin Settings - ' . $plugin->name)

@section('content')
    <div class="plugin-settings">
        <div class="plugin-header">
            <h2>{{ $plugin->name }} Settings</h2>
            <a href="{{ route('admin.plugins.index') }}" class="btn btn-secondary">← Back to Plugins</a>
        </div>

        <div class="settings-container">
            <form action="{{ route('admin.plugins.updateSettings', $plugin) }}" method="POST">
                @csrf @method('PUT')

                @if($plugin->settings && count($plugin->settings) > 0)
                    @foreach($plugin->settings as $key => $value)
                        <div class="form-group">
                            <label>{{ ucwords(str_replace('_', ' ', $key)) }}</label>
                            @if(is_bool($value))
                                <input type="checkbox" name="{{ $key }}" value="1" {{ $value ? 'checked' : '' }}>
                            @elseif(is_array($value))
                                <textarea name="{{ $key }}" rows="4"
                                    class="form-control">{{ json_encode($value, JSON_PRETTY_PRINT) }}</textarea>
                            @else
                                <input type="text" name="{{ $key }}" value="{{ $value }}" class="form-control">
                            @endif
                        </div>
                    @endforeach
                @else
                    <p class="text-center">This plugin has no configurable settings.</p>
                @endif

                @if($plugin->settings && count($plugin->settings) > 0)
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </div>
                @endif
            </form>

            <div class="plugin-priority">
                <h3>Plugin Priority</h3>
                <form action="{{ route('admin.plugins.updatePriority', $plugin) }}" method="POST" class="priority-form">
                    @csrf @method('PUT')
                    <input type="number" name="priority" value="{{ $plugin->priority }}" min="1" max="100"
                        class="form-control">
                    <button type="submit" class="btn btn-primary">Update Priority</button>
                </form>
                <small>Lower numbers load first (1-100)</small>
            </div>
        </div>
    </div>

    <style>
        .plugin-settings {
            padding: 20px
        }

        .plugin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px
        }

        .settings-container {
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .1)
        }

        .form-group {
            margin-bottom: 20px
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151
        }

        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem
        }

        .form-control:focus {
            outline: none;
            border-color: #4f46e5
        }

        .form-actions {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb
        }

        .plugin-priority {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #e5e7eb
        }

        .plugin-priority h3 {
            margin: 0 0 15px
        }

        .priority-form {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 10px
        }

        .priority-form input {
            max-width: 150px
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none
        }

        .btn-primary {
            background: #4f46e5;
            color: #fff
        }

        .btn-secondary {
            background: #6b7280;
            color: #fff
        }

        .text-center {
            text-align: center;
            padding: 40px;
            color: #6b7280
        }
    </style>
@endsection