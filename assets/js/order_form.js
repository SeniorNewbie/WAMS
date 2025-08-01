// assets/js/order_form.js

let orderItems = [];
let itemCounter = 0;
const maxItems = 20;

$(document).ready(function() {
    // Inicjalizacja Material Components
    initializeMDC();
    
    // Obsługa dodawania pozycji
    $('#addPosition').click(function() {
        if (orderItems.length >= maxItems) {
            showNotification('warning', `Maksymalna liczba pozycji to ${maxItems}`);
            return;
        }
        
        if (validateItemForm()) {
            addOrderItem();
        }
    });
    
    // Obsługa wysyłania zamówienia
    $('#orderForm').on('submit', function(e) {
        e.preventDefault();
        
        if (orderItems.length === 0) {
            showNotification('error', 'Dodaj przynajmniej jedną pozycję do zamówienia');
            return;
        }
        
        // Potwierdzenie
        if (!confirm('Czy na pewno chcesz złożyć to zamówienie?')) {
            return;
        }
        
        submitOrder();
    });
    
    // Obsługa zmiany koloru profili
    $('input[name="profile_color"]').on('change', function() {
        if ($(this).val() === 'RAL (dowolny)') {
            showRALInput();
        } else {
            hideRALInput();
        }
    });
});

// Walidacja formularza pozycji
function validateItemForm() {
    const requiredFields = [
        'product', 'width', 'height', 'quantity', 
        'profile_system', 'profile_color', 'net_color', 'window_rabbet'
    ];
    
    let isValid = true;
    let errors = [];
    
    requiredFields.forEach(function(field) {
        const value = $(`[name="${field}"]`).val();
        if (!value || value.trim() === '') {
            isValid = false;
            errors.push(getFieldLabel(field));
            
            // Podświetl błędne pole
            const fieldElement = $(`[name="${field}"]`).closest('.mdc-text-field, .mdc-select');
            fieldElement.addClass('mdc-text-field--invalid');
        }
    });
    
    // Walidacja wartości numerycznych
    const width = parseInt($('[name="width"]').val());
    const height = parseInt($('[name="height"]').val());
    const quantity = parseInt($('[name="quantity"]').val());
    
    if (width < 100 || width > 3000) {
        isValid = false;
        errors.push('Szerokość musi być między 100 a 3000 mm');
    }
    
    if (height < 100 || height > 3000) {
        isValid = false;
        errors.push('Wysokość musi być między 100 a 3000 mm');
    }
    
    if (quantity < 1 || quantity > 100) {
        isValid = false;
        errors.push('Ilość musi być między 1 a 100');
    }
    
    if (!isValid) {
        showNotification('error', 'Wypełnij wszystkie wymagane pola: ' + errors.join(', '));
    }
    
    return isValid;
}

// Dodawanie pozycji do zamówienia
function addOrderItem() {
    itemCounter++;
    
    const item = {
        id: itemCounter,
        product: $('[name="product"]').val(),
        productName: $('.mdc-select__selected-text').first().text(),
        width: parseInt($('[name="width"]').val()),
        height: parseInt($('[name="height"]').val()),
        quantity: parseInt($('[name="quantity"]').val()),
        profile_system: $('[name="profile_system"]').val(),
        profile_color: $('[name="profile_color"]').val(),
        net_color: $('[name="net_color"]').val(),
        window_rabbet: parseInt($('[name="window_rabbet"]').val()),
        hook_size: $('[name="hook_size"]').val() || null,
        riveting: $('[name="riveting"]').val() || null,
        force_hook: $('[name="force_hook"]').val() || null,
        force_hook_count: $('[name="force_hook_count"]').val() || null,
        horizontal_bar: $('[name="horizontal_bar"]').is(':checked'),
        bar_height: $('[name="bar_height"]').val() || null,
        supplier_note: $('[name="supplier_note"]').val() || null,
        total_width: $('[name="total_width"]').val() || null,
        total_height: $('[name="total_height"]').val() || null,
        customer_label: $('[name="customer_label"]').val() || null,
        self_assembly: $('[name="self_assembly"]').val() === 'Tak',
        corner_type: $('[name="corner_type"]').val() || null,
        powder_color: $('[name="powder_color"]').val() || null,
        corner_color_ral: $('[name="corner_color_ral"]').val() || null,
        price: calculateItemPrice()
    };
    
    orderItems.push(item);
    renderOrderItems();
    clearItemForm();
    updateTotal();
    
    // Włącz przycisk zamówienia
    $('#submitOrder').prop('disabled', false);
    
    showNotification('success', 'Pozycja dodana do zamówienia');
}

// Obliczanie ceny pozycji (przykładowa kalkulacja)
function calculateItemPrice() {
    const width = parseInt($('[name="width"]').val()) || 0;
    const height = parseInt($('[name="height"]').val()) || 0;
    const quantity = parseInt($('[name="quantity"]').val()) || 1;
    
    // Przykładowa kalkulacja - w rzeczywistości pobierana z cennika
    const area = (width * height) / 1000000; // m²
    const pricePerM2 = 150; // zł/m²
    const basePrice = area * pricePerM2;
    
    // Dodatki za specjalne opcje
    let additionalCost = 0;
    if ($('[name="profile_color"]').val() === 'RAL (dowolny)') {
        additionalCost += 50;
    }
    
    return (basePrice + additionalCost) * quantity;
}

// Renderowanie listy pozycji
function renderOrderItems() {
    const container = $('#itemsList');
    container.empty();
    
    if (orderItems.length === 0) {
        container.html('<p class="text-center text-muted">Brak pozycji w zamówieniu</p>');
        return;
    }
    
    const table = $(`
        <table class="data-table position-table">
            <thead>
                <tr>
                    <th>Nr</th>
                    <th>Produkt</th>
                    <th>Wymiary</th>
                    <th>Ilość</th>
                    <th>Cena netto</th>
                    <th>Akcje</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    `);
    
    orderItems.forEach(function(item, index) {
        const row = $(`
            <tr>
                <td>${index + 1}</td>
                <td>${item.productName}</td>
                <td>${item.width} x ${item.height} mm</td>
                <td>${item.quantity}</td>
                <td>${formatPrice(item.price)}</td>
                <td>
                    <button type="button" class="action-button secondary" onclick="editItem(${item.id})">
                        <i class="material-icons">edit</i>
                        Edytuj
                    </button>
                    <button type="button" class="action-button danger" onclick="removeItem(${item.id})">
                        <i class="material-icons">delete</i>
                        Usuń
                    </button>
                </td>
            </tr>
        `);
        
        table.find('tbody').append(row);
    });
    
    container.html(table);
}

// Czyszczenie formularza
function clearItemForm() {
    // Reset pól tekstowych
    $('input[type="text"], input[type="number"], textarea').not('[name="csrf_token"]').val('');
    
    // Reset selectów
    $('.mdc-select__selected-text').text('');
    $('.mdc-select input[type="hidden"]').val('');
    
    // Usuń klasy błędów
    $('.mdc-text-field--invalid').removeClass('mdc-text-field--invalid');
    
    // Przywróć domyślną ilość
    $('[name="quantity"]').val('1');
    
    // Reinicjalizuj komponenty
    initializeMDC();
}

// Aktualizacja sumy
function updateTotal() {
    let total = 0;
    orderItems.forEach(function(item) {
        total += item.price;
    });
    
    $('#totalAmount').text(formatPrice(total));
}

// Formatowanie ceny
function formatPrice(price) {
    return price.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' zł';
}

// Usuwanie pozycji
window.removeItem = function(itemId) {
    if (!confirm('Czy na pewno chcesz usunąć tę pozycję?')) {
        return;
    }
    
    orderItems = orderItems.filter(item => item.id !== itemId);
    renderOrderItems();
    updateTotal();
    
    if (orderItems.length === 0) {
        $('#submitOrder').prop('disabled', true);
    }
    
    showNotification('info', 'Pozycja została usunięta');
};

// Edycja pozycji
window.editItem = function(itemId) {
    const item = orderItems.find(i => i.id === itemId);
    if (!item) return;
    
    // Wypełnij formularz danymi pozycji
    $('[name="product"]').val(item.product);
    $('[name="width"]').val(item.width);
    $('[name="height"]').val(item.height);
    $('[name="quantity"]').val(item.quantity);
    $('[name="profile_system"]').val(item.profile_system);
    $('[name="profile_color"]').val(item.profile_color);
    $('[name="net_color"]').val(item.net_color);
    $('[name="window_rabbet"]').val(item.window_rabbet);
    $('[name="customer_label"]').val(item.customer_label);
    
    // Aktualizuj wyświetlane wartości w selectach
    updateSelectDisplays();
    
    // Usuń pozycję z listy (będzie dodana ponownie po edycji)
    orderItems = orderItems.filter(i => i.id !== itemId);
    renderOrderItems();
    updateTotal();
    
    // Przewiń do formularza
    $('html, body').animate({
        scrollTop: $('#orderForm').offset().top - 100
    }, 500);
    
    showNotification('info', 'Edytuj pozycję i dodaj ponownie');
};

// Aktualizacja wyświetlanych wartości w selectach
function updateSelectDisplays() {
    $('.mdc-select').each(function() {
        const select = $(this);
        const value = select.find('input[type="hidden"]').val();
        const text = select.find(`[data-value="${value}"]`).text();
        select.find('.mdc-select__selected-text').text(text);
    });
}

// Wysyłanie zamówienia
function submitOrder() {
    const formData = {
        csrf_token: $('[name="csrf_token"]').val(),
        order_comment: $('#order_comment').val(),
        items: orderItems
    };
    
    $.ajax({
        url: '../api/order_submit.php',
        method: 'POST',
        data: JSON.stringify(formData),
        contentType: 'application/json',
        beforeSend: function() {
            $('#submitOrder').prop('disabled', true).html(
                '<span class="loading-spinner"></span> Przetwarzanie zamówienia...'
            );
        },
        success: function(response) {
            if (response.success) {
                showNotification('success', 'Zamówienie zostało złożone pomyślnie');
                
                // Wyczyść formularz
                orderItems = [];
                renderOrderItems();
                updateTotal();
                $('#order_comment').val('');
                
                // Przekieruj do listy zamówień
                setTimeout(function() {
                    window.location.href = 'orders.php';
                }, 2000);
            } else {
                showNotification('error', response.message || 'Błąd podczas składania zamówienia');
                $('#submitOrder').prop('disabled', false).html(
                    '<i class="material-icons mdc-button__icon">shopping_cart</i>' +
                    '<span class="mdc-button__label">Zamawiam</span>'
                );
            }
        },
        error: function() {
            showNotification('error', 'Błąd połączenia z serwerem');
            $('#submitOrder').prop('disabled', false).html(
                '<i class="material-icons mdc-button__icon">shopping_cart</i>' +
                '<span class="mdc-button__label">Zamawiam</span>'
            );
        }
    });
}

// Pobieranie etykiety pola
function getFieldLabel(fieldName) {
    const labels = {
        'product': 'Produkt',
        'width': 'Szerokość',
        'height': 'Wysokość',
        'quantity': 'Ilość',
        'profile_system': 'System profili',
        'profile_color': 'Kolor profili',
        'net_color': 'Kolor siatki',
        'window_rabbet': 'Przylga okna'
    };
    
    return labels[fieldName] || fieldName;
}

// Pokazanie pola RAL
function showRALInput() {
    const ralField = $(`
        <div class="mdc-text-field mdc-text-field--outlined" id="ral-field">
            <input type="text" id="powder_color" name="powder_color" class="mdc-text-field__input" placeholder="np. RAL 7016">
            <div class="mdc-notched-outline">
                <div class="mdc-notched-outline__leading"></div>
                <div class="mdc-notched-outline__notch">
                    <label for="powder_color" class="mdc-floating-label">Kolor proszku lub RAL</label>
                </div>
                <div class="mdc-notched-outline__trailing"></div>
            </div>
        </div>
    `);
    
    $('[name="profile_color"]').closest('.mdc-select').after(ralField);
    new mdc.textField.MDCTextField(ralField[0]);
}

// Ukrycie pola RAL
function hideRALInput() {
    $('#ral-field').remove();
}