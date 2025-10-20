/**
 * GRANERO - Sistema de Control de Inventario
 * JavaScript Principal - Mobile First
 */

// Configuración global
const Config = {
    baseUrl: 'http://localhost/granero/',
    apiUrl: 'http://localhost/granero/controllers/',
    itemsPerPage: 25,
    dateFormat: 'DD/MM/YYYY'
};

// ==================== SIDEBAR & MENU ====================
document.addEventListener('DOMContentLoaded', function() {
    initSidebar();
    initModals();
    initTooltips();
});

function initSidebar() {
    const menuToggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    const sidebarClose = document.querySelector('.sidebar-close');
    const sidebarCollapse = document.querySelector('.sidebar-collapse');
    const mainContent = document.querySelector('.main-content');
    
    // Cargar estado guardado del sidebar
    const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (sidebarCollapsed) {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('sidebar-collapsed');
        if (sidebarCollapse) {
            sidebarCollapse.querySelector('i').classList.remove('fa-chevron-left');
            sidebarCollapse.querySelector('i').classList.add('fa-chevron-right');
            sidebarCollapse.setAttribute('title', 'Expandir menú');
        }
    }
    
    // Toggle para móvil (abrir/cerrar)
    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            mainContent.classList.toggle('sidebar-open');
        });
    }
    
    // Cerrar sidebar en móvil
    if (sidebarClose) {
        sidebarClose.addEventListener('click', function() {
            sidebar.classList.remove('active');
            mainContent.classList.remove('sidebar-open');
        });
    }
    
    // Colapsar/Expandir sidebar en escritorio
    if (sidebarCollapse) {
        sidebarCollapse.addEventListener('click', function() {
            const isCollapsed = sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('sidebar-collapsed');
            
            // Cambiar icono
            const icon = this.querySelector('i');
            if (isCollapsed) {
                icon.classList.remove('fa-chevron-left');
                icon.classList.add('fa-chevron-right');
                this.setAttribute('title', 'Expandir menú');
            } else {
                icon.classList.remove('fa-chevron-right');
                icon.classList.add('fa-chevron-left');
                this.setAttribute('title', 'Colapsar menú');
            }
            
            // Guardar estado en localStorage
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        });
    }
    
    // Cerrar sidebar al hacer clic fuera en móvil
    document.addEventListener('click', function(e) {
        if (window.innerWidth < 1024) {
            if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                sidebar.classList.remove('active');
                mainContent.classList.remove('sidebar-open');
            }
        }
    });
    
    // Marcar enlace activo
    const currentPage = window.location.pathname.split('/').pop();
    const menuLinks = document.querySelectorAll('.sidebar-menu a');
    menuLinks.forEach(link => {
        if (link.getAttribute('href') === currentPage) {
            link.classList.add('active');
        }
    });
}

// ==================== MODALS ====================
function initModals() {
    // Cerrar modal al hacer clic en overlay
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-overlay')) {
            closeAllModals();
        }
    });
    
    // Cerrar modal con botones de cerrar
    const closeButtons = document.querySelectorAll('.modal-close, [data-dismiss="modal"]');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal-overlay');
            if (modal) {
                closeModal(modal.id);
            }
        });
    });
    
    // Cerrar modal con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAllModals();
        }
    });
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

function closeAllModals() {
    const modals = document.querySelectorAll('.modal-overlay');
    modals.forEach(modal => {
        modal.classList.remove('active');
    });
    document.body.style.overflow = '';
}

// ==================== SWEET ALERTS ====================
function showSuccess(title, text = '') {
    Swal.fire({
        icon: 'success',
        title: title,
        text: text,
        confirmButtonColor: '#2563eb',
        confirmButtonText: 'Aceptar'
    });
}

function showError(title, text = '') {
    Swal.fire({
        icon: 'error',
        title: title,
        text: text,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Aceptar'
    });
}

function showWarning(title, text = '') {
    Swal.fire({
        icon: 'warning',
        title: title,
        text: text,
        confirmButtonColor: '#f59e0b',
        confirmButtonText: 'Aceptar'
    });
}

function showInfo(title, text = '') {
    Swal.fire({
        icon: 'info',
        title: title,
        text: text,
        confirmButtonColor: '#3b82f6',
        confirmButtonText: 'Aceptar'
    });
}

// Función showAlert genérica para compatibilidad
function showAlert(type, message, timer = null) {
    const icons = {
        'success': 'success',
        'error': 'error',
        'warning': 'warning',
        'info': 'info'
    };
    
    const config = {
        icon: icons[type] || 'info',
        title: message,
        confirmButtonColor: '#2563eb',
        confirmButtonText: 'Aceptar'
    };
    
    if (timer) {
        config.timer = timer;
        config.showConfirmButton = false;
    }
    
    Swal.fire(config);
}

function confirmDelete(callback) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            callback();
        }
    });
}

function confirmAction(title, text, callback) {
    Swal.fire({
        title: title,
        text: text,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Confirmar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            callback();
        }
    });
}

function showLoading(title = 'Procesando...') {
    Swal.fire({
        title: title,
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
}

function hideLoading() {
    Swal.close();
}

// ==================== AJAX HELPERS ====================
async function ajaxRequest(url, method = 'GET', data = null) {
    try {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            }
        };
        
        if (data && method !== 'GET') {
            options.body = JSON.stringify(data);
        }
        
        const response = await fetch(url, options);
        const result = await response.json();
        
        return result;
    } catch (error) {
        console.error('Error en petición AJAX:', error);
        showError('Error', 'Ha ocurrido un error en la comunicación con el servidor');
        return { success: false, error: error.message };
    }
}

async function ajaxFormData(url, formData) {
    try {
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        return result;
    } catch (error) {
        console.error('Error en petición AJAX:', error);
        showError('Error', 'Ha ocurrido un error en la comunicación con el servidor');
        return { success: false, error: error.message };
    }
}

// ==================== FORM HELPERS ====================
function getFormData(formId) {
    const form = document.getElementById(formId);
    if (!form) return null;
    
    const formData = new FormData(form);
    const data = {};
    
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }
    
    return data;
}

function resetForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.reset();
        // Limpiar errores de validación
        const errors = form.querySelectorAll('.form-error');
        errors.forEach(error => error.remove());
        const invalidInputs = form.querySelectorAll('.is-invalid');
        invalidInputs.forEach(input => input.classList.remove('is-invalid'));
    }
}

function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    let isValid = true;
    const requiredInputs = form.querySelectorAll('[required]');
    
    requiredInputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

// ==================== TABLE HELPERS ====================
function createPagination(totalItems, currentPage, itemsPerPage, containerId) {
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    const container = document.getElementById(containerId);
    
    if (!container) return;
    
    let html = '';
    
    // Botón anterior
    html += `<button class="btn btn-sm" ${currentPage === 1 ? 'disabled' : ''} onclick="changePage(${currentPage - 1})">
                <i class="fas fa-chevron-left"></i>
             </button>`;
    
    // Páginas
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
            html += `<button class="btn btn-sm ${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">${i}</button>`;
        } else if (i === currentPage - 3 || i === currentPage + 3) {
            html += '<span>...</span>';
        }
    }
    
    // Botón siguiente
    html += `<button class="btn btn-sm" ${currentPage === totalPages ? 'disabled' : ''} onclick="changePage(${currentPage + 1})">
                <i class="fas fa-chevron-right"></i>
             </button>`;
    
    container.innerHTML = html;
}

function filterTable(tableId, searchValue) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const rows = table.querySelectorAll('tbody tr');
    const searchLower = searchValue.toLowerCase();
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchLower) ? '' : 'none';
    });
}

// ==================== AUTOCOMPLETE ====================
function initAutocomplete(inputId, dataSource, onSelect) {
    const input = document.getElementById(inputId);
    if (!input) return;
    
    let selectedIndex = -1;
    let suggestions = [];
    
    // Crear contenedor de sugerencias
    const suggestionsContainer = document.createElement('div');
    suggestionsContainer.className = 'autocomplete-suggestions';
    suggestionsContainer.style.cssText = `
        position: absolute;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        max-height: 300px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
        width: ${input.offsetWidth}px;
    `;
    input.parentElement.style.position = 'relative';
    input.parentElement.appendChild(suggestionsContainer);
    
    input.addEventListener('input', async function() {
        const value = this.value.trim();
        
        if (value.length < 2) {
            suggestionsContainer.style.display = 'none';
            return;
        }
        
        // Obtener sugerencias
        if (typeof dataSource === 'function') {
            suggestions = await dataSource(value);
        } else if (Array.isArray(dataSource)) {
            suggestions = dataSource.filter(item => 
                item.toLowerCase().includes(value.toLowerCase())
            );
        }
        
        // Mostrar sugerencias
        if (suggestions.length > 0) {
            suggestionsContainer.innerHTML = suggestions.map((item, index) => {
                const text = typeof item === 'object' ? item.text : item;
                return `<div class="autocomplete-item" data-index="${index}" style="
                    padding: 0.75rem 1rem;
                    cursor: pointer;
                    border-bottom: 1px solid #f3f4f6;
                    transition: background 0.2s;
                ">${text}</div>`;
            }).join('');
            suggestionsContainer.style.display = 'block';
            
            // Event listeners para sugerencias
            suggestionsContainer.querySelectorAll('.autocomplete-item').forEach(item => {
                item.addEventListener('mouseenter', function() {
                    this.style.background = '#f3f4f6';
                });
                item.addEventListener('mouseleave', function() {
                    this.style.background = 'white';
                });
                item.addEventListener('click', function() {
                    const index = parseInt(this.dataset.index);
                    const selected = suggestions[index];
                    input.value = typeof selected === 'object' ? selected.text : selected;
                    suggestionsContainer.style.display = 'none';
                    if (onSelect) onSelect(selected);
                });
            });
        } else {
            suggestionsContainer.style.display = 'none';
        }
    });
    
    // Navegación con teclado
    input.addEventListener('keydown', function(e) {
        const items = suggestionsContainer.querySelectorAll('.autocomplete-item');
        
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
            updateSelection(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedIndex = Math.max(selectedIndex - 1, 0);
            updateSelection(items);
        } else if (e.key === 'Enter' && selectedIndex >= 0) {
            e.preventDefault();
            items[selectedIndex].click();
        } else if (e.key === 'Escape') {
            suggestionsContainer.style.display = 'none';
        }
    });
    
    function updateSelection(items) {
        items.forEach((item, index) => {
            if (index === selectedIndex) {
                item.style.background = '#dbeafe';
            } else {
                item.style.background = 'white';
            }
        });
    }
    
    // Cerrar al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !suggestionsContainer.contains(e.target)) {
            suggestionsContainer.style.display = 'none';
        }
    });
}

// ==================== UTILITIES ====================
function formatCurrency(amount) {
    return new Intl.NumberFormat('es-ES', {
        style: 'currency',
        currency: 'EUR'
    }).format(amount);
}

function formatDate(date, format = 'DD/MM/YYYY') {
    if (!date) return '';
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    
    if (format === 'DD/MM/YYYY') {
        return `${day}/${month}/${year}`;
    } else if (format === 'YYYY-MM-DD') {
        return `${year}-${month}-${day}`;
    }
    return date;
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function initTooltips() {
    // Implementar tooltips si es necesario
}

// ==================== EXPORTAR A EXCEL ====================
function exportToExcel(tableId, filename = 'export.xlsx') {
    const table = document.getElementById(tableId);
    if (!table) {
        showError('Error', 'No se encontró la tabla para exportar');
        return;
    }
    
    // Usar librería XLSX si está disponible
    if (typeof XLSX !== 'undefined') {
        const wb = XLSX.utils.table_to_book(table);
        XLSX.writeFile(wb, filename);
        showSuccess('Exportado', 'El archivo se ha descargado correctamente');
    } else {
        showError('Error', 'Librería de exportación no disponible');
    }
}

// ==================== IMPRIMIR PDF ====================
function printPDF(elementId) {
    const element = document.getElementById(elementId);
    if (!element) {
        showError('Error', 'No se encontró el elemento para imprimir');
        return;
    }
    
    window.print();
}

// ==================== PASSWORD TOGGLE ====================
function togglePassword(buttonElement) {
    const input = buttonElement.previousElementSibling;
    const icon = buttonElement.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
