@extends('layouts.admin')

@section('title', 'Feature Flags')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Feature Flags</h1>
            <div class="d-flex gap-2">
                <button class="btn btn-success" onclick="bulkAction('enable')">
                    <i class="fas fa-check-circle me-2"></i> Enable Selected
                </button>
                <button class="btn btn-warning" onclick="bulkAction('disable')">
                    <i class="fas fa-times-circle me-2"></i> Disable Selected
                </button>
                <form action="{{ route('admin.features.seed') }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-primary">
                        <i class="fas fa-sync me-2"></i> Seed Features
                    </button>
                </form>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Total Features</h6>
                        <h2 class="mb-0">{{ $stats['total_features'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Enabled</h6>
                        <h2 class="mb-0">{{ $stats['enabled_features'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Disabled</h6>
                        <h2 class="mb-0">{{ $stats['disabled_features'] }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Feature Categories -->
        @foreach(['core' => 'Core Features', 'social' => 'Social Features', 'monetization' => 'Monetization', 'content' => 'Content Features', 'analytics' => 'Analytics', 'marketing' => 'Marketing'] as $key => $label)
            @if($categories[$key]->isNotEmpty())
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ $label }}</h5>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" class="form-check-input category-checkbox"
                                            data-category="{{ $key }}">
                                    </th>
                                    <th>Feature</th>
                                    <th>Description</th>
                                    <th width="100">Status</th>
                                    <th width="100">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categories[$key] as $feature)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input feature-checkbox" value="{{ $feature->id }}">
                                        </td>
                                        <td>
                                            <strong>{{ $feature->name }}</strong>
                                            <br><small class="text-muted">{{ $feature->key }}</small>
                                        </td>
                                        <td>{{ $feature->description }}</td>
                                        <td>
                                            <span class="badge bg-{{ $feature->is_enabled ? 'success' : 'danger' }}">
                                                {{ $feature->is_enabled ? 'Enabled' : 'Disabled' }}
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-{{ $feature->is_enabled ? 'warning' : 'success' }}"
                                                onclick="toggleFeature({{ $feature->id }})">
                                                <i class="fas fa-{{ $feature->is_enabled ? 'toggle-on' : 'toggle-off' }}"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    @push('scripts')
        <script>
            function toggleFeature(id) {
                fetch(`/admin/features/${id}/toggle`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        }
                    });
            }

            function bulkAction(action) {
                const selected = Array.from(document.querySelectorAll('.feature-checkbox:checked')).map(cb => cb.value);

                if (selected.length === 0) {
                    alert('Please select at least one feature');
                    return;
                }

                fetch('/admin/features/bulk-toggle', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        feature_ids: selected,
                        action: action
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        }
                    });
            }

            // Category checkbox select all
            document.querySelectorAll('.category-checkbox').forEach(cb => {
                cb.addEventListener('change', function () {
                    const category = this.dataset.category;
                    const checkboxes = this.closest('.card').querySelectorAll('.feature-checkbox');
                    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
                });
            });
        </script>
    @endpush
@endsection