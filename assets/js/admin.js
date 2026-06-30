/**
 * ============================================================
 * Admin JavaScript - Aplikasi Pengaduan Sarana Sekolah
 * UKK SMK RPL Paket 3 Tahun 2026
 * ============================================================
 */

document.addEventListener('DOMContentLoaded', function() {

    // ============================================================
    // Form Validation Enhancement
    // ============================================================
    document.querySelectorAll('form[required]').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const requiredInputs = this.querySelectorAll('[required]');
            let valid = true;
            requiredInputs.forEach(function(input) {
                if (!input.value.trim()) {
                    input.classList.add('is-invalid');
                    valid = false;
                } else {
                    input.classList.remove('is-invalid');
                }
            });
            if (!valid) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Form Belum Lengkap',
                    text: 'Silakan isi semua field yang wajib diisi.',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });
    });

    // ============================================================
    // Tooltip Initialization
    // ============================================================
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    if (tooltipTriggerList.length > 0) {
        [...tooltipTriggerList].map(function(el) {
            return new bootstrap.Tooltip(el);
        });
    }

    // ============================================================
    // Popover Initialization
    // ============================================================
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    if (popoverTriggerList.length > 0) {
        [...popoverTriggerList].map(function(el) {
            return new bootstrap.Popover(el);
        });
    }

    // ============================================================
    // Auto Calculation
    // ============================================================
    document.querySelectorAll('.auto-calc').forEach(function(input) {
        input.addEventListener('input', function() {
            // Custom calculation logic can be added here
        });
    });

    // ============================================================
    // Responsive Table
    // ============================================================
    function makeTablesResponsive() {
        document.querySelectorAll('.table-responsive table').forEach(function(table) {
            const wrapper = table.closest('.table-responsive');
            if (wrapper) {
                const isOverflowing = table.scrollWidth > wrapper.clientWidth;
                if (isOverflowing) {
                    wrapper.style.overflowX = 'auto';
                }
            }
        });
    }

    makeTablesResponsive();
    window.addEventListener('resize', makeTablesResponsive);

    // ============================================================
    // Keyboard Shortcuts
    // ============================================================
    document.addEventListener('keydown', function(e) {
        // Ctrl + S to submit form
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            const form = document.querySelector('form');
            if (form) {
                const submitBtn = form.querySelector('[type="submit"]');
                if (submitBtn) {
                    submitBtn.click();
                }
            }
        }

        // Escape to close modals
        if (e.key === 'Escape') {
            const openModals = document.querySelectorAll('.modal.show');
            openModals.forEach(function(modal) {
                const instance = bootstrap.Modal.getInstance(modal);
                if (instance) {
                    instance.hide();
                }
            });
        }
    });

    // ============================================================
    // Notification Toast
    // ============================================================
    function showToast(icon, title, message) {
        Swal.fire({
            icon: icon,
            title: title,
            text: message,
            timer: 3000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    }
    window.showToast = showToast;

    // ============================================================
    // Bulk Action Select
    // ============================================================
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.checkbox-item');
            checkboxes.forEach(function(cb) {
                cb.checked = selectAllCheckbox.checked;
            });
        });
    }

    // ============================================================
    // Print Table
    // ============================================================
    const printBtns = document.querySelectorAll('.btn-print-table');
    printBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const tableId = this.getAttribute('data-table');
            const table = document.getElementById(tableId);
            if (table) {
                const newWin = window.open('', '_blank');
                newWin.document.write(`
                    <html>
                        <head>
                            <title>Print</title>
                            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
                            <style>
                                @media print {
                                    body { padding: 20px; }
                                    .no-print { display: none !important; }
                                }
                            </style>
                        </head>
                        <body>
                            ${table.outerHTML}
                            <div class="no-print text-center mt-3">
                                <button onclick="window.print()" class="btn btn-primary">Print</button>
                            </div>
                        </body>
                    </html>
                `);
                newWin.document.close();
            }
        });
    });

});
