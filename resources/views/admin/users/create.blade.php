@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <h1 class="h3 mb-4">Nuevo usuario</h1>
    <div id="alert-box"></div>
    @include('admin.users._form')
@endsection

@push('scripts')
    <script src="{{ asset('js/admin-api.js') }}"></script>
    <script>
        document.getElementById('password').required = true;

        document.getElementById('user-form').addEventListener('submit', async e => {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target));

            try {
                await apiFetch('/admin/users', {
                    method: 'POST',
                    body: JSON.stringify(data)
                });
                window.location.href = "{{ route('admin.users.index') }}";
            } catch (err) {
                showError(err);
            }
        });
    </script>
@endpush
