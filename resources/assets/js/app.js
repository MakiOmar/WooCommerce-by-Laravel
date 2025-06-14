// WooCommerce Order Dashboard JavaScript

// Flash message auto-hide
document.addEventListener('DOMContentLoaded', function() {
    const flashMessages = document.querySelectorAll('[x-data]');
    flashMessages.forEach(message => {
        setTimeout(() => {
            message.style.display = 'none';
        }, 3000);
    });
});

// Date range picker initialization
if (document.getElementById('start_date') && document.getElementById('end_date')) {
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');

    startDate.addEventListener('change', function() {
        endDate.min = this.value;
    });

    endDate.addEventListener('change', function() {
        startDate.max = this.value;
    });
}

// Meta key value filter visibility
if (document.getElementById('meta_key') && document.getElementById('meta_value')) {
    const metaKey = document.getElementById('meta_key');
    const metaValue = document.getElementById('meta_value');
    const metaValueContainer = metaValue.closest('div');

    metaKey.addEventListener('change', function() {
        metaValueContainer.style.display = this.value ? 'block' : 'none';
    });

    // Initial state
    metaValueContainer.style.display = metaKey.value ? 'block' : 'none';
}

// Table row hover effect
document.querySelectorAll('.table-body tr').forEach(row => {
    row.addEventListener('mouseover', function() {
        this.classList.add('bg-gray-50');
    });

    row.addEventListener('mouseout', function() {
        this.classList.remove('bg-gray-50');
    });
}); 