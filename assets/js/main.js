// assets/js/main.js

// Globalne zmienne
let notificationCheckInterval;
let debugConsole;

// Inicjalizacja po załadowaniu DOM
$(document).ready(function() {
    // Inicjalizacja komponentów Material Design
    initializeMDC();
    
    // Sprawdzanie powiadomień co 30 sekund
    checkNotifications();
    notificationCheckInterval = setInterval(checkNotifications, 30000);
    
    // Inicjalizacja debug console dla admina
    if ($('body').hasClass('admin')) {
        initDebugConsole();
    }
    
    // Obsługa formularzy AJAX
    initializeAjaxForms();
    
    // Obsługa tabel z danymi
    initializeDataTables();
});

// Inicjalizacja Material Design Components
function initializeMDC() {
    // Text fields
    document.querySelectorAll('.mdc-text-field').forEach(function(el) {
        new mdc.textField.MDCTextField(el);
    });
    
    // Buttons
    document.querySelectorAll('.mdc-button').forEach(function(el) {
        new mdc.ripple.MDCRipple(el);
    });
    
    // Select
    document.querySelectorAll('.mdc-select').forEach(function(el) {
        new mdc.select.MDCSelect(el);
    });
    
    // Checkbox
    document.querySelectorAll('.mdc-checkbox').forEach(function(el) {
        new mdc.checkbox.MDCCheckbox(el);
    });
    
    // Data tables
    document.querySelectorAll('.mdc-data-table').forEach(function(el) {
        new mdc.dataTable.MDCDataTable(el);
    });
}

// Sprawdzanie powiadomień
function checkNotifications() {
    $.ajax({
        url: '/api/notifications.php',
        method: 'GET',
        data: { action: 'check' },
        success: function(response) {
            if (response.count > 0) {
                $('.notification-badge').text(response.count).show();
            } else {
                $('.notification-badge').hide();
            }
        }
    });
}

// Ładowanie listy powiadomień
function loadNotifications() {
    $.ajax({
        url: '/api/notifications.php',
        method: 'GET',
        data: { action: 'list' },
        beforeSend: function() {
            $('#notifications-content').html('<div class="text-center p-4"><div class="loading-spinner"></div></div>');
        },
        success: function(response) {
            $('#notifications-content').html(response);
            
            // Oznacz jako przeczytane
            markNotificationsAsRead();
        },
        error: function() {
            $('#notifications-content').html('<div class="text-center p-4">Błąd ładowania powiadomień</div>');
        }
    });
}

// Oznaczanie powiadomień jako przeczytane
function markNotificationsAsRead() {
    $.ajax({
        url: '/api/notifications.php',
        method: 'POST',
        data: { action: 'mark_read' },
        success: function() {
            $('.notification-badge').hide();
        }
    });
}

// Inicjalizacja formularzy AJAX
function initializeAjaxForms() {
    $('form.ajax-form').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.text();
        
        // Walidacja
        if (!validateForm(form)) {
            return false;
        }
        
        // Wyślij formularz
        $.ajax({
            url: form.attr('action'),
            method: form.attr('method'),
            data: new FormData(this),
            processData: false,
            contentType: false,
            beforeSend: function() {
                submitBtn.prop('disabled', true).html('<span class="loading-spinner"></span> Przetwarzanie...');
            },
            success: function(response) {
                if (response.success) {
                    showNotification('success', response.message);
                    
                    if (response.redirect) {
                        setTimeout(function() {
                            window.location.href = response.redirect;
                        }, 1500);
                    }
                    
                    if (form.hasClass('reset-on-success')) {
                        form[0].reset();
                    }
                } else {
                    showNotification('error', response.message || 'Wystąpił błąd');
                    
                    if (response.errors) {
                        showFormErrors(form, response.errors);
                    }
                }
            },
            error: function() {
                showNotification('error', 'Błąd połączenia z serwerem');
            },
            complete: function() {
                submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });
}

// Walidacja formularza
function validateForm(form) {
    let isValid = true;
    
    // Usuń poprzednie błędy
    form.find('.error-message').remove();
    form.find('.mdc-text-field--invalid').removeClass('mdc-text-field--invalid');
    
    // Sprawdź wymagane pola
    form.find('[required]').each(function() {
        const field = $(this);
        if (!field.val().trim()) {
            const container = field.closest('.mdc-text-field');
            container.addClass('mdc-text-field--invalid');
            container.after('<span class="error-message">To pole jest wymagane</span>');
            isValid = false;
        }
    });
    
    // Walidacja email
    form.find('input[type="email"]').each(function() {
        const field = $(this);
        const email = field.val();
        if (email && !isValidEmail(email)) {
            const container = field.closest('.mdc-text-field');
            container.addClass('mdc-text-field--invalid');
            container.after('<span class="error-message">Nieprawidłowy adres email</span>');
            isValid = false;
        }
    });
    
    // Walidacja NIP
    form.find('input[name="nip"]').each(function() {
        const field = $(this);
        const nip = field.val();
        if (nip && !isValidNIP(nip)) {
            const container = field.closest('.mdc-text-field');
            container.addClass('mdc-text-field--invalid');
            container.after('<span class="error-message">Nieprawidłowy NIP</span>');
            isValid = false;
        }
    });
    
    return isValid;
}

// Wyświetlanie błędów formularza
function showFormErrors(form, errors) {
    Object.keys(errors).forEach(function(field) {
        const input = form.find(`[name="${field}"]`);
        const container = input.closest('.mdc-text-field');
        container.addClass('mdc-text-field--invalid');
        container.after(`<span class="error-message">${errors[field]}</span>`);
    });
}

// Wyświetlanie powiadomień
function showNotification(type, message) {
    const notification = $(`
        <div class="notification notification--${type}">
            <i class="material-icons">${getNotificationIcon(type)}</i>
            <span>${message}</span>
        </div>
    `);
    
    $('body').append(notification);
    
    setTimeout(function() {
        notification.addClass('notification--show');
    }, 100);
    
    setTimeout(function() {
        notification.removeClass('notification--show');
        setTimeout(function() {
            notification.remove();
        }, 300);
    }, 3000);
}

// Ikony dla powiadomień
function getNotificationIcon(type) {
    const icons = {
        'success': 'check_circle',
        'error': 'error',
        'warning': 'warning',
        'info': 'info'
    };
    return icons[type] || 'info';
}

// Walidacja email
function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Walidacja NIP
function isValidNIP(nip) {
    nip = nip.replace(/[^0-9]/g, '');
    
    if (nip.length !== 10) {
        return false;
    }
    
    const weights = [6, 5, 7, 2, 3, 4, 5, 6, 7];
    let sum = 0;
    
    for (let i = 0; i < 9; i++) {
        sum += parseInt(nip[i]) * weights[i];
    }
    
    const control = sum % 11;
    const controlDigit = (control === 10) ? 0 : control;
    
    return controlDigit === parseInt(nip[9]);
}

// Inicjalizacja tabel z danymi
function initializeDataTables() {
    $('.data-table').each(function() {
        const table = $(this);
        
        // Sortowanie
        table.find('th[data-sortable]').on('click', function() {
            const th = $(this);
            const column = th.data('column');
            const currentOrder = th.data('order') || 'asc';
            const newOrder = currentOrder === 'asc' ? 'desc' : 'asc';
            
            // Usuń sortowanie z innych kolumn
            table.find('th').removeClass('sorted-asc sorted-desc');
            
            // Dodaj klasę sortowania
            th.addClass('sorted-' + newOrder);
            th.data('order', newOrder);
            
            // Sortuj dane
            sortTable(table, column, newOrder);
        });
        
        // Filtrowanie
        const searchInput = $(`#${table.data('search-input')}`);
        if (searchInput.length) {
            searchInput.on('keyup', function() {
                filterTable(table, $(this).val());
            });
        }
    });
}

// Sortowanie tabeli
function sortTable(table, column, order) {
    const tbody = table.find('tbody');
    const rows = tbody.find('tr').toArray();
    
    rows.sort(function(a, b) {
        const aValue = $(a).find(`td[data-column="${column}"]`).text();
        const bValue = $(b).find(`td[data-column="${column}"]`).text();
        
        if (order === 'asc') {
            return aValue.localeCompare(bValue, 'pl', { numeric: true });
        } else {
            return bValue.localeCompare(aValue, 'pl', { numeric: true });
        }
    });
    
    tbody.empty();
    rows.forEach(function(row) {
        tbody.append(row);
    });
}

// Filtrowanie tabeli
function filterTable(table, searchTerm) {
    const rows = table.find('tbody tr');
    const term = searchTerm.toLowerCase();
    
    rows.each(function() {
        const row = $(this);
        const text = row.text().toLowerCase();
        
        if (text.includes(term)) {
            row.show();
        } else {
            row.hide();
        }
    });
    
    // Pokaż komunikat jeśli brak wyników
    const visibleRows = rows.filter(':visible');
    if (visibleRows.length === 0) {
        if (!table.find('.no-results').length) {
            table.find('tbody').append(`
                <tr class="no-results">
                    <td colspan="100%" class="text-center">Brak wyników</td>
                </tr>
            `);
        }
    } else {
        table.find('.no-results').remove();
    }
}

// Debug console dla admina
function initDebugConsole() {
    debugConsole = $(`
        <div class="debug-console" id="debug-console">
            <div class="debug-console-header">
                <span>Debug Console</span>
                <button class="mdc-icon-button material-icons" id="toggle-debug">minimize</button>
            </div>
            <div class="debug-console-content" id="debug-content"></div>
        </div>
    `);
    
    $('body').append(debugConsole);
    
    // Minimalizuj/maksymalizuj
    $('#toggle-debug').on('click', function() {
        debugConsole.toggleClass('minimized');
        $(this).text(debugConsole.hasClass('minimized') ? 'maximize' : 'minimize');
    });
    
    // Ładuj logi
    loadDebugLogs();
    setInterval(loadDebugLogs, 5000);
}

// Ładowanie logów debug
function loadDebugLogs() {
    $.ajax({
        url: '/api/debug_logs.php',
        method: 'GET',
        success: function(logs) {
            const content = $('#debug-content');
            content.empty();
            
            logs.forEach(function(log) {
                content.append(`
                    <div class="debug-entry ${log.level.toLowerCase()}">
                        <strong>[${log.time}] ${log.level}:</strong> ${log.message}
                        ${log.file ? `<br><small>${log.file}:${log.line}</small>` : ''}
                    </div>
                `);
            });
            
            // Przewiń do dołu
            content.scrollTop(content[0].scrollHeight);
        }
    });
}

// Funkcje pomocnicze dla dynamicznego ładowania treści
window.loadContent = function(url, container) {
    $(container).load(url, function(response, status, xhr) {
        if (status === "error") {
            $(container).html(`
                <div class="error-container">
                    <i class="material-icons">error_outline</i>
                    <h3>Błąd ładowania</h3>
                    <p>Nie udało się załadować zawartości.</p>
                </div>
            `);
        } else {
            // Reinicjalizuj komponenty MDC
            initializeMDC();
        }
    });
};

// Obsługa plików
window.handleFileUpload = function(input, previewContainer) {
    const files = input.files;
    const preview = $(previewContainer);
    
    preview.empty();
    
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const reader = new FileReader();
        
        reader.onload = function(e) {
            if (file.type.startsWith('image/')) {
                preview.append(`
                    <div class="file-preview">
                        <img src="${e.target.result}" alt="${file.name}">
                        <span class="file-name">${file.name}</span>
                    </div>
                `);
            } else {
                preview.append(`
                    <div class="file-preview">
                        <i class="material-icons">insert_drive_file</i>
                        <span class="file-name">${file.name}</span>
                    </div>
                `);
            }
        };
        
        reader.readAsDataURL(file);
    }
};