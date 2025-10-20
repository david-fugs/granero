/**
 * JavaScript para Gestión de Mozos
 */

let paginaActual = 1;
let totalPaginas = 1;

// Cargar mozos al iniciar
document.addEventListener('DOMContentLoaded', function() {
    cargarMozos();
});

// Cargar lista de mozos
async function cargarMozos(pagina = 1) {
    paginaActual = pagina;
    const busqueda = document.getElementById('busquedaMozo').value;
    const activo = document.getElementById('filtroActivo').value;
    
    try {
        const response = await fetch(`../controllers/MozoController.php?action=listar&pagina=${pagina}&busqueda=${encodeURIComponent(busqueda)}&activo=${activo}`);
        const data = await response.json();
        
        if (data.success) {
            mostrarMozos(data.data);
            mostrarPaginacion(data.paginacion);
        } else {
            showAlert('error', 'Error al cargar mozos');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Error de conexión al cargar mozos');
    }
}

// Mostrar mozos en la tabla
function mostrarMozos(mozos) {
    const tbody = document.getElementById('tbody-mozos');
    
    if (mozos.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center">
                    <i class="fas fa-inbox"></i> No se encontraron mozos
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = mozos.map(mozo => `
        <tr>
            <td>${escapeHtml(mozo.numero_identificacion)}</td>
            <td>${escapeHtml(mozo.nombre)}</td>
            <td>
                <span class="badge ${mozo.activo == 1 ? 'badge-success' : 'badge-danger'}">
                    ${mozo.activo == 1 ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td>${formatearFecha(mozo.fecha_creacion)}</td>
            <td>
                <div class="btn-group">
                    <button onclick="editarMozo(${mozo.id})" class="btn btn-sm btn-primary" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="eliminarMozo(${mozo.id}, '${escapeHtml(mozo.nombre)}')" class="btn btn-sm btn-danger" title="Eliminar">
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
    const container = document.getElementById('paginacion-mozos');
    
    if (totalPaginas <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = '<div class="pagination">';
    
    // Botón anterior
    html += `<button class="btn btn-sm ${paginaActual === 1 ? 'disabled' : ''}" 
             onclick="cargarMozos(${paginaActual - 1})" 
             ${paginaActual === 1 ? 'disabled' : ''}>
             <i class="fas fa-chevron-left"></i>
             </button>`;
    
    // Páginas
    for (let i = 1; i <= totalPaginas; i++) {
        if (i === 1 || i === totalPaginas || (i >= paginaActual - 2 && i <= paginaActual + 2)) {
            html += `<button class="btn btn-sm ${i === paginaActual ? 'btn-primary' : ''}" 
                     onclick="cargarMozos(${i})">${i}</button>`;
        } else if (i === paginaActual - 3 || i === paginaActual + 3) {
            html += '<span>...</span>';
        }
    }
    
    // Botón siguiente
    html += `<button class="btn btn-sm ${paginaActual === totalPaginas ? 'disabled' : ''}" 
             onclick="cargarMozos(${paginaActual + 1})"
             ${paginaActual === totalPaginas ? 'disabled' : ''}>
             <i class="fas fa-chevron-right"></i>
             </button>`;
    
    html += '</div>';
    container.innerHTML = html;
}

// Abrir modal para nuevo mozo
function abrirModalMozo() {
    document.getElementById('modalMozoTitle').textContent = 'Nuevo Mozo';
    document.getElementById('formMozo').reset();
    document.getElementById('mozo_id').value = '';
    document.getElementById('activo_mozo').checked = true;
    openModal('modalMozo');
}

// Editar mozo
async function editarMozo(id) {
    try {
        const response = await fetch(`../controllers/MozoController.php?action=obtener&id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('modalMozoTitle').textContent = 'Editar Mozo';
            document.getElementById('mozo_id').value = data.data.id;
            document.getElementById('numero_identificacion_mozo').value = data.data.numero_identificacion;
            document.getElementById('nombre_mozo').value = data.data.nombre;
            document.getElementById('activo_mozo').checked = data.data.activo == 1;
            openModal('modalMozo');
        } else {
            showAlert('error', data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Error al cargar el mozo');
    }
}

// Guardar mozo
async function guardarMozo(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const id = document.getElementById('mozo_id').value;
    formData.append('action', id ? 'actualizar' : 'crear');
    
    try {
        const response = await fetch('../controllers/MozoController.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('success', data.message);
            closeModal('modalMozo');
            cargarMozos(paginaActual);
        } else {
            showAlert('error', data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Error al guardar el mozo');
    }
}

// Eliminar mozo
async function eliminarMozo(id, nombre) {
    const confirmacion = await Swal.fire({
        title: '¿Estás seguro?',
        text: `¿Deseas eliminar al mozo "${nombre}"?`,
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
            
            const response = await fetch('../controllers/MozoController.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showAlert('success', data.message);
                cargarMozos(paginaActual);
            } else {
                showAlert('error', data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('error', 'Error al eliminar el mozo');
        }
    }
}

// Limpiar filtros
function limpiarFiltros() {
    document.getElementById('busquedaMozo').value = '';
    document.getElementById('filtroActivo').value = '1';
    cargarMozos(1);
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
