/**
 * JavaScript para Gestión de Comerciales
 */

let paginaActual = 1;
let totalPaginas = 1;

// Cargar comerciales al iniciar
document.addEventListener('DOMContentLoaded', function() {
    cargarComerciales();
});

// Cargar lista de comerciales
async function cargarComerciales(pagina = 1) {
    paginaActual = pagina;
    const busqueda = document.getElementById('busquedaComercial').value;
    const activo = document.getElementById('filtroActivo').value;
    
    try {
        const response = await fetch(`../controllers/ComercialController.php?action=listar&pagina=${pagina}&busqueda=${encodeURIComponent(busqueda)}&activo=${activo}`);
        const data = await response.json();
        
        if (data.success) {
            mostrarComerciales(data.data);
            mostrarPaginacion(data.paginacion);
        } else {
            showAlert('error', 'Error al cargar comerciales');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Error de conexión al cargar comerciales');
    }
}

// Mostrar comerciales en la tabla
function mostrarComerciales(comerciales) {
    const tbody = document.getElementById('tbody-comerciales');
    
    if (comerciales.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center">
                    <i class="fas fa-inbox"></i> No se encontraron comerciales
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = comerciales.map(comercial => `
        <tr>
            <td>${escapeHtml(comercial.numero_identificacion)}</td>
            <td>${escapeHtml(comercial.nombre)}</td>
            <td>
                <span class="badge ${comercial.activo == 1 ? 'badge-success' : 'badge-danger'}">
                    ${comercial.activo == 1 ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td>${formatearFecha(comercial.fecha_creacion)}</td>
            <td>
                <div class="btn-group">
                    <button onclick="editarComercial(${comercial.id})" class="btn btn-sm btn-primary" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="eliminarComercial(${comercial.id}, '${escapeHtml(comercial.nombre)}')" class="btn btn-sm btn-danger" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Mostrar paginación
function mostrarPaginacion(paginacion) {
    totalPaginas = paginacion.total_paginas;
    const container = document.getElementById('paginacion-comerciales');
    
    if (totalPaginas <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = '<div class="pagination">';
    
    // Botón anterior
    html += `<button class="btn btn-sm ${paginaActual === 1 ? 'disabled' : ''}" 
             onclick="cargarComerciales(${paginaActual - 1})" 
             ${paginaActual === 1 ? 'disabled' : ''}>
             <i class="fas fa-chevron-left"></i>
             </button>`;
    
    // Páginas
    for (let i = 1; i <= totalPaginas; i++) {
        if (i === 1 || i === totalPaginas || (i >= paginaActual - 2 && i <= paginaActual + 2)) {
            html += `<button class="btn btn-sm ${i === paginaActual ? 'btn-primary' : ''}" 
                     onclick="cargarComerciales(${i})">${i}</button>`;
        } else if (i === paginaActual - 3 || i === paginaActual + 3) {
            html += '<span>...</span>';
        }
    }
    
    // Botón siguiente
    html += `<button class="btn btn-sm ${paginaActual === totalPaginas ? 'disabled' : ''}" 
             onclick="cargarComerciales(${paginaActual + 1})"
             ${paginaActual === totalPaginas ? 'disabled' : ''}>
             <i class="fas fa-chevron-right"></i>
             </button>`;
    
    html += '</div>';
    container.innerHTML = html;
}

// Abrir modal para nuevo comercial
function abrirModalComercial() {
    document.getElementById('modalComercialTitle').textContent = 'Nuevo Comercial';
    document.getElementById('formComercial').reset();
    document.getElementById('comercial_id').value = '';
    document.getElementById('activo').checked = true;
    openModal('modalComercial');
}

// Editar comercial
async function editarComercial(id) {
    try {
        const response = await fetch(`../controllers/ComercialController.php?action=obtener&id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('modalComercialTitle').textContent = 'Editar Comercial';
            document.getElementById('comercial_id').value = data.data.id;
            document.getElementById('numero_identificacion').value = data.data.numero_identificacion;
            document.getElementById('nombre').value = data.data.nombre;
            document.getElementById('activo').checked = data.data.activo == 1;
            openModal('modalComercial');
        } else {
            showAlert('error', data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Error al cargar el comercial');
    }
}

// Guardar comercial
async function guardarComercial(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const id = document.getElementById('comercial_id').value;
    formData.append('action', id ? 'actualizar' : 'crear');
    
    try {
        const response = await fetch('../controllers/ComercialController.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('success', data.message);
            closeModal('modalComercial');
            cargarComerciales(paginaActual);
        } else {
            showAlert('error', data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Error al guardar el comercial');
    }
}

// Eliminar comercial
async function eliminarComercial(id, nombre) {
    const confirmacion = await Swal.fire({
        title: '¿Estás seguro?',
        text: `¿Deseas eliminar al comercial "${nombre}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    });
    
    if (confirmacion.isConfirmed) {
        try {
            const formData = new FormData();
            formData.append('action', 'eliminar');
            formData.append('id', id);
            
            const response = await fetch('../controllers/ComercialController.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showAlert('success', data.message);
                cargarComerciales(paginaActual);
            } else {
                showAlert('error', data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('error', 'Error al eliminar el comercial');
        }
    }
}

// Limpiar filtros
function limpiarFiltros() {
    document.getElementById('busquedaComercial').value = '';
    document.getElementById('filtroActivo').value = '1';
    cargarComerciales(1);
}

// Formatear fecha
function formatearFecha(fecha) {
    if (!fecha) return '-';
    const date = new Date(fecha);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
