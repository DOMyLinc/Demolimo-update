@extends('layouts.admin')

@section('title', 'Auto-Moderation Rules')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Auto-Moderation Rules</h1>
            <a href="{{ route('admin.moderation.rules.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Rule
            </a>
        </div>

        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="rulesTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Content Type</th>
                                <th>Action</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rules as $rule)
                                <tr>
                                    <td>{{ $rule->name }}</td>
                                    <td>{{ ucfirst($rule->content_type) }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $rule->action)) }}</td>
                                    <td>{{ $rule->priority }}</td>
                                    <td>
                                        <span class="badge badge-{{ $rule->is_active ? 'success' : 'secondary' }}">
                                            {{ $rule->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.moderation.rules.edit', $rule) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.moderation.rules.destroy', $rule) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm"
                                                onclick="return confirm('Are you sure?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $rules->links() }}
            </div>
        </div>
    </div>
@endsection