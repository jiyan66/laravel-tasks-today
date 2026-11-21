@if (session('success'))
    <div class="notice notice-success" role="status">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="notice notice-error" role="alert"><p>Please review the following:</p><ul>
        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul></div>
@endif
