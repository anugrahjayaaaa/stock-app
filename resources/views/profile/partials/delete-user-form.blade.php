<section>
    <h5 class="text-danger mb-1">Delete Account</h5>
    <p class="text-muted small">
        {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
    </p>

    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal"
            data-action="{{ route('profile.destroy') }}">
        {{ __('Delete Account') }}
    </button>

    <!-- Reuse the shared delete modal; we add the hidden _method field via JS hook here -->
    <form id="delete-account-method" method="POST" class="d-none">
        @csrf
        @method('delete')
    </form>
    <script>
        document.addEventListener('show.bs.modal', (e) => {
            if (e.target.id !== 'deleteModal') return;
            const btn = document.activeElement;
            const action = btn && btn.getAttribute('data-action');
            if (action && action.indexOf('profile.destroy') !== -1) {
                const form = document.getElementById('deleteModalForm');
                form.setAttribute('action', action);
                // ensure DELETE method for account deletion
                let m = form.querySelector('input[name="_method"]');
                if (!m) { m = document.createElement('input'); m.type='hidden'; m.name='_method'; form.appendChild(m); }
                m.value = 'DELETE';
            }
        });
    </script>
</section>
