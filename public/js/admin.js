/**
 * Amigos TMS — Admin JavaScript
 * Sidebar toggle, flash message auto-dismiss, confirm delete, submenu toggle
 */
document.addEventListener('DOMContentLoaded', function () {

    // ─── Sidebar Toggle ────────────────────────────────────────────
    const sidebar      = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarClose  = document.getElementById('sidebarClose');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const mainContent   = document.getElementById('mainContent');

    // Mobile sidebar toggle
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            if (window.innerWidth < 992) {
                // Mobile: slide in/out
                sidebar.classList.toggle('show');
                sidebarOverlay.classList.toggle('show');
            } else {
                // Desktop: collapse/expand
                document.body.classList.toggle('sidebar-collapsed');
                // Persist state
                localStorage.setItem('sidebar-collapsed', document.body.classList.contains('sidebar-collapsed'));
            }
        });
    }

    if (sidebarClose) {
        sidebarClose.addEventListener('click', function () {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function () {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });
    }

    // Restore sidebar state
    if (localStorage.getItem('sidebar-collapsed') === 'true' && window.innerWidth >= 992) {
        document.body.classList.add('sidebar-collapsed');
    }

    // ─── Submenu Toggle ────────────────────────────────────────────
    document.querySelectorAll('.nav-item > .nav-link').forEach(function (link) {
        const submenu = link.nextElementSibling;
        if (submenu && submenu.classList.contains('nav-submenu')) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                submenu.classList.toggle('show');

                // Close other submenus
                document.querySelectorAll('.nav-submenu.show').forEach(function (other) {
                    if (other !== submenu) {
                        other.classList.remove('show');
                    }
                });
            });
        }
    });

    // ─── Flash Message Auto-Dismiss ────────────────────────────────
    document.querySelectorAll('.flash-alert').forEach(function (alert) {
        setTimeout(function () {
            var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) {
                bsAlert.close();
            }
        }, 5000);
    });

    // ─── Confirm Delete Modal ──────────────────────────────────────
    document.querySelectorAll('[data-confirm-delete]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();

            var url = this.getAttribute('data-url') || this.getAttribute('href');
            var message = this.getAttribute('data-message') || 'Are you sure you want to delete this item? This action cannot be undone.';

            document.getElementById('confirmDeleteForm').setAttribute('action', url);
            document.getElementById('confirmDeleteMessage').textContent = message;

            var modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
            modal.show();
        });
    });

    // ─── Loading State for Forms ───────────────────────────────────
    document.querySelectorAll('form[data-loading]').forEach(function (form) {
        form.addEventListener('submit', function () {
            var btn = form.querySelector('[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="loading-spinner me-2"></span>Processing...';
            }
        });
    });

    // ─── Tooltips Initialization ───────────────────────────────────
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
