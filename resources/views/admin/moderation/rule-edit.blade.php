@extends('layouts.admin')

@section('title', 'Edit Moderation Rule')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Edit Moderation Rule</h1>
            <a href="{{ route('admin.moderation.rules.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Rules
            </a>
        </div>

        <div class="card shadow mb-4">
            <div class="card-body">
                <form action="{{ route('admin.moderation.rules.update', $rule) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="name">Rule Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ $rule->name }}" required>
                    </div>

                    <div class="form-group">
                        <label for="content_type">Content Type</label>
                        <select class="form-control" id="content_type" name="content_type" required>
                            <option value="track" {{ $rule->content_type == 'track' ? 'selected' : '' }}>Track</option>
                            <option value="comment" {{ $rule->content_type == 'comment' ? 'selected' : '' }}>Comment</option>
                            <option value="user" {{ $rule->content_type == 'user' ? 'selected' : '' }}>User</option>
                            <option value="event" {{ $rule->content_type == 'event' ? 'selected' : '' }}>Event</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="conditions">Conditions (JSON)</label>
                        <textarea class="form-control" id="conditions" name="conditions" rows="5"
                            required>{{ json_encode($rule->conditions) }}</textarea>
                        <small class="form-text text-muted">Enter valid JSON conditions.</small>
                    </div>

                    <div class="form-group">
                        <label for="action">Action</label>
                        <select class="form-control" id="action" name="action" required>
                            <option value="auto_reject" {{ $rule->action == 'auto_reject' ? 'selected' : '' }}>Auto Reject
                            </option>
                            <option value="flag_for_review" {{ $rule->action == 'flag_for_review' ? 'selected' : '' }}>Flag
                                for Review</option>
                            <option value="shadow_ban" {{ $rule->action == 'shadow_ban' ? 'selected' : '' }}>Shadow Ban
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="priority">Priority</label>
                        <input type="number" class="form-control" id="priority" name="priority"
                            value="{{ $rule->priority }}">
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ $rule->is_active ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Active</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Rule</button>
                </form>
            </div>
        </div>
    </div>
@endsection