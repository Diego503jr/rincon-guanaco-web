@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <h1 class="h3 mb-4">Editar usuario</h1>
    <div id="alert-box"></div>
    @include('admin.users._form')
@endsection

@push('scripts')
    <script src="{{ asset('/js/admin-api.js') }}"></script>
    <script>
        const userId = {{ (int) $id }};
        const form = document.getElementById('user-form');
        document.getElementById('password-hint').textContent = 'Déjala vacía para conservar la actual.';

        (async () => {
            try {
                const user = await apiFetch(`/admin/users/${userId}`);
                ['name', 'email', 'phone', 'country_of_origin', 'role', 'traveler_type', 'status']
                .forEach(field => {
                    form.elements[field].value = user[field] ?? '';
                });
            } catch (err) {
                showError(err);
            }
        })();

        form.addEventListener('submit', async e => {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(form));
            if (!data.password) {
                delete data.password;
                delete data.password_confirmation;
            }

            try {
                await apiFetch(`/admin/users/${userId}`, {
                    method: 'PUT',
                    body: JSON.stringify(data)
                });
                window.location.href = "{{ route('admin.users.index') }}";
            } catch (err) {
                showError(err);
            }
        });
    </script>
@endpush
