// Loading spinner functions
function showLoading() {
    document.getElementById('loading-spinner').style.display = 'block';
}

function hideLoading() {
    document.getElementById('loading-spinner').style.display = 'none';
}

// Alert functions
function showAlert(message, type = 'success') {
    const alertContainer = document.getElementById('alert-container');
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    alertContainer.appendChild(alert);

    // Auto dismiss after 5 seconds
    setTimeout(() => {
        alert.classList.remove('show');
        setTimeout(() => alert.remove(), 300);
    }, 5000);
}

function showError(message) {
    showAlert(message, 'danger');
}

function showSuccess(message) {
    showAlert(message, 'success');
}

// Form submission with loading state
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', async (e) => {
        if (form.classList.contains('ajax-form')) {
            e.preventDefault();
            showLoading();
            
            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: form.method,
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.error) {
                    showError(data.error);
                } else {
                    showSuccess(data.message || 'Operation successful');
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else if (form.dataset.reload) {
                        window.location.reload();
                    }
                }
            } catch (error) {
                showError('An error occurred. Please try again.');
            } finally {
                hideLoading();
            }
        }
    });
});

// Image preview
document.querySelectorAll('.image-input').forEach(input => {
    input.addEventListener('change', function() {
        const preview = document.querySelector(this.dataset.preview);
        if (preview && this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = e => preview.src = e.target.result;
            reader.readAsDataURL(this.files[0]);
        }
    });
});

// Table search functionality
document.querySelectorAll('.table-search').forEach(input => {
    input.addEventListener('input', function() {
        const table = document.querySelector(this.dataset.table);
        const searchText = this.value.toLowerCase();
        
        table.querySelectorAll('tbody tr').forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchText) ? '' : 'none';
        });
    });
});

// Delete confirmation
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        if (!confirm('Are you sure you want to delete this item?')) {
            e.preventDefault();
        }
    });
});

// Print functionality
function printElement(elementId) {
    const element = document.getElementById(elementId);
    const originalContents = document.body.innerHTML;
    
    document.body.innerHTML = element.innerHTML;
    window.print();
    document.body.innerHTML = originalContents;
}

// Tooltips initialization
const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});

// Responsive sidebar
const sidebarToggle = document.querySelector('.sidebar-toggle');
if (sidebarToggle) {
    sidebarToggle.addEventListener('click', () => {
        document.body.classList.toggle('sidebar-collapsed');
    });
}

// Export table to CSV
function exportTableToCSV(tableId, filename = 'data.csv') {
    const table = document.getElementById(tableId);
    const rows = table.querySelectorAll('tr');
    const csv = [];
    
    for (const row of rows) {
        const cells = row.querySelectorAll('td, th');
        const rowData = Array.from(cells).map(cell => {
            let data = cell.textContent;
            data = data.replace(/"/g, '""');
            return `"${data}"`;
        });
        csv.push(rowData.join(','));
    }
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.setAttribute('download', filename);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Initialize datepicker
document.addEventListener('DOMContentLoaded', function() {
    const dateInputs = document.querySelectorAll('.datepicker');
    dateInputs.forEach(input => {
        new bootstrap.Datepicker(input, {
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true
        });
    });
});
