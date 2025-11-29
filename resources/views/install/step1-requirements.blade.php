@extends('install.layout', ['currentStep' => 1])

@section('title', 'Requirements Check')

@section('content')
    <div class="card">
        <h2 class="card-title">System Requirements Check</h2>
        <p class="card-subtitle">Let's make sure your server meets all the requirements</p>

        <h3 style="margin-top: 30px; margin-bottom: 15px;">PHP Requirements</h3>
        <table>
            <thead>
                <tr>
                    <th>Requirement</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($requirements as $name => $passed)
                    <tr>
                        <td>{{ $name }}</td>
                        <td>
                            @if($passed)
                                <i class="fas fa-check-circle status-icon status-pass"></i>
                            @else
                                <i class="fas fa-times-circle status-icon status-fail"></i>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h3 style="margin-top: 30px; margin-bottom: 15px;">File Permissions</h3>
        <table>
            <thead>
                <tr>
                    <th>Directory</th>
                    <th>Writable</th>
                </tr>
            </thead>
            <tbody>
                @foreach($permissions as $dir => $writable)
                    <tr>
                        <td><code>{{ $dir }}</code></td>
                        <td>
                            @if($writable)
                                <i class="fas fa-check-circle status-icon status-pass"></i>
                            @else
                                <i class="fas fa-times-circle status-icon status-fail"></i>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="btn-group">
            <a href="{{ route('install.step2') }}" class="btn btn-primary" @if(!$allPassed) disabled @endif>
                Continue <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
@endsection