@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Translation Management</h1>
        <div class="d-flex gap-2">
            <form action="{{ route('admin.translations.export') }}" method="GET" class="d-inline">
                <input type="hidden" name="locale" value="{{ $locale }}">
                <button type="submit" class="btn btn-secondary">
                    <i class="fa fa-download"></i> Export
                </button>
            </form>
            <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fa fa-upload"></i> Import
            </button>
            <form action="{{ route('admin.translations.clear-cache') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-warning">
                    <i class="fa fa-sync"></i> Clear Cache
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" action="{{ route('admin.translations.index') }}" class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label">Language</label>
                    <select name="locale" class="form-select" onchange="this.form.submit()">
                        @foreach($locales as $code => $name)
                            <option value="{{ $code }}" {{ $locale === $code ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Group</label>
                    <select name="group" class="form-select" onchange="this.form.submit()">
                        <option value="">All Groups</option>
                        @foreach($groups as $g)
                            <option value="{{ $g }}" {{ $group === $g ? 'selected' : '' }}>
                                {{ ucfirst($g) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Search keys..." value="{{ $search }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa fa-search"></i> Search
                    </button>
                </div>
            </form>

            <!-- Translation Table -->
            <form id="bulkUpdateForm" action="{{ route('admin.translations.bulk-update') }}" method="POST">
                @csrf
                <input type="hidden" name="locale" value="{{ $locale }}">

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 15%;">Key</th>
                                <th style="width: 10%;">Group</th>
                                <th style="width: 25%;">Description</th>
                                <th style="width: 40%;">Translation ({{ $locales[$locale] }})</th>
                                <th style="width: 10%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($keys as $key)
                                @php
                                    $translation = $key->translations->first();
                                @endphp
                                <tr>
                                    <td>
                                        <code>{{ $key->key }}</code>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $key->group }}</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $key->description }}</small>
                                    </td>
                                    <td>
                                        <input type="hidden" name="translations[{{ $loop->index }}][key_id]" value="{{ $key->id }}">
                                        <input type="text" 
                                               name="translations[{{ $loop->index }}][value]" 
                                               class="form-control form-control-sm" 
                                               value="{{ $translation ? $translation->value : '' }}"
                                               placeholder="Enter translation...">
                                    </td>
                                    <td>
                                        <button type="button" 
                                                class="btn btn-sm btn-primary update-single" 
                                                data-key-id="{{ $key->id }}"
                                                data-value-input="translations[{{ $loop->index }}][value]">
                                            <i class="fa fa-save"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        No translation keys found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($keys->count() > 0)
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            Showing {{ $keys->firstItem() }} to {{ $keys->lastItem() }} of {{ $keys->total() }} translations
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-save"></i> Save All Changes
                        </button>
                    </div>
                @endif
            </form>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $keys->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.translations.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Import Translations</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Language</label>
                        <select name="locale" class="form-select" required>
                            @foreach($locales as $code => $name)
                                <option value="{{ $code }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">JSON File</label>
                        <input type="file" name="file" class="form-control" accept=".json" required>
                        <small class="text-muted">Upload a JSON file with translations</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Single translation update
document.querySelectorAll('.update-single').forEach(btn => {
    btn.addEventListener('click', function() {
        const keyId = this.dataset.keyId;
        const valueInput = document.querySelector(`input[name="${this.dataset.valueInput}"]`);
        const value = valueInput.value;
        
        if (!value) {
            alert('Please enter a translation value');
            return;
        }
        
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/translations/${keyId}`;
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'PUT';
        form.appendChild(methodInput);
        
        const localeInput = document.createElement('input');
        localeInput.type = 'hidden';
        localeInput.name = 'locale';
        localeInput.value = '{{ $locale }}';
        form.appendChild(localeInput);
        
        const valueInputSubmit = document.createElement('input');
        valueInputSubmit.type = 'hidden';
        valueInputSubmit.name = 'value';
        valueInputSubmit.value = value;
        form.appendChild(valueInputSubmit);
        
        document.body.appendChild(form);
        form.submit();
    });
});
</script>
@endsection
