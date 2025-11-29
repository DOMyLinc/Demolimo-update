<!-- Dynamic Theme CSS -->
<link rel="stylesheet" href="{{ route('theme.css') }}">

<style>
    /* Theme-aware styles */
    .bg-primary {
        background-color: var(--color-primary) !important;
    }

    .bg-secondary {
        background-color: var(--color-secondary) !important;
    }

    .bg-accent {
        background-color: var(--color-accent) !important;
    }

    .text-primary {
        color: var(--color-primary) !important;
    }

    .text-secondary {
        color: var(--color-secondary) !important;
    }

    .border-primary {
        border-color: var(--color-primary) !important;
    }

    .gradient-primary {
        background: linear-gradient(135deg, var(--color-gradient-start) 0%, var(--color-gradient-end) 100%) !important;
    }

    /* Button styles */
    .btn-primary {
        background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
        color: var(--color-text);
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }

    /* Card styles */
    .theme-card {
        background: var(--color-surface);
        border: 1px solid var(--color-border);
        color: var(--color-text);
    }

    /* Link styles */
    a.theme-link {
        color: var(--color-primary);
    }

    a.theme-link:hover {
        color: var(--color-secondary);
    }

    /* Success/Warning/Error */
    .alert-success {
        background-color: var(--color-success);
        opacity: 0.2;
        border-color: var(--color-success);
    }

    .alert-warning {
        background-color: var(--color-warning);
        opacity: 0.2;
        border-color: var(--color-warning);
    }

    .alert-error {
        background-color: var(--color-error);
        opacity: 0.2;
        border-color: var(--color-error);
    }
</style>