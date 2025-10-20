/**
 * JavaScript para Gestión de Usuarios
 */

let paginaActual = 1;
let totalPaginas = 1;

// Cargar usuarios al iniciar
document.addEventListener('DOMContentLoaded', function() {
    cargarUsuarios();
});

// Cargar lista de usuarios
async function cargarUsuarios(pagina = 1) {
    paginaActual = pagina;
    const busqueda = document.getElementById('busquedaUsuario').value;
    const tipo = document.getElementById('filtroTipo').value;
    const activo = document.getElementById('filtroActivo').value;
    
    try {
        const response = await fetch(`../controllers/UsuarioController.php?action=listar&pagina=${pagina}&busqueda=${encodeURIComponent(busqueda)}&tipo=${tipo}&activo=${activo}`);
        const data = await response.json();
        
        if (data.success) {
            mostrarUsuarios(data.data);
            mostrarPaginacion(data.paginacion);
        } else {
            showAlert('error', 'Error al cargar usuarios');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Error de conexión al cargar usuarios');
    }
}

// Mostrar usuarios en la tabla
function mostrarUsuarios(usuarios) {
    const tbody = document.getElementById('tbody-usuarios');
    
    if (usuarios.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center">
                    <i class="fas fa-inbox"></i> No se encontraron usuarios
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = usuarios.map(usuario => {
        const badgeColor = {
            'admin': 'badge-danger',
            'vendedor': 'badge-primary',
            'almacen': 'badge-warning',
            'visualizador': 'badge-secondary'
        }[usuario.tipo_usuario] || 'badge-secondary';
        
        return `
        <tr>
            <td>${escapeHtml(usuario.nombre)}</td>
            <td>${escapeHtml(usuario.email)}</td>
            <td>
                <span class="badge ${badgeColor}">
                    ${ucFirst(usuario.tipo_usuario)}
                </span>
            </td>
            <td>
                <span class="badge ${usuario.activo == 1 ? 'badge-success' : 'badge-danger'}">
                    ${usuario.activo == 1 ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td>${formatearFecha(usuario.fecha_creacion)}</td>
            <td>
                <div class="btn-group">
                    <button onclick="editarUsuario(${usuario.id})" class="btn btn-sm btn-primary" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="eliminarUsuario(${usuario.id}, '${escapeHtml(usuario.nombre)}')" class="btn btn-sm btn-danger" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
        `;
    }).join('');
}

// Mostrar paginación
function mostrarPaginacion(paginacion) {
    totalPaginas = paginacion.total_paginas;
    const container = document.getElementById('paginacion-usuarios');
    
    if (totalPaginas <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = '<div class="pagination">';
    
    html += `<button class="btn btn-sm ${paginaActual === 1 ? 'disabled' : ''}" 
             onclick="cargarUsuarios(${paginaActual - 1})" 
             ${paginaActual === 1 ? 'disabled' : ''}>
             <i class="fas fa-chevron-left"></i>
             </button>`;
    
    for (let i = 1; i <= totalPaginas; i++) {
        if (i === 1 || i === totalPaginas || (i >= paginaActual - 2 && i <= paginaActual + 2)) {
            html += `<button class="btn btn-sm ${i === paginaActual ? 'btn-primary' : ''}" 
                     onclick="cargarUsuarios(${i})">${i}</button>`;
        } else if (i === paginaActual - 3 || i === paginaActual + 3) {
            html += '<span>...</span>';
        }
    }
    
    html += `<button class="btn btn-sm ${paginaActual === totalPaginas ? 'disabled' : ''}" 
             onclick="cargarUsuarios(${paginaActual + 1})"
             ${paginaActual === totalPaginas ? 'disabled' : ''}>
             <i class="fas fa-chevron-right"></i>
             </button>`;
    
    html += '</div>';
    container.innerHTML = html;
}

// Abrir modal para nuevo usuario
function abrirModalUsuario() {
    document.getElementById('modalUsuarioTitle').textContent = 'Nuevo Usuario';
    document.getElementById('formUsuario').reset();
    document.getElementById('usuario_id').value = '';
    document.getElementById('activo').checked = true;
    document.getElementById('password').required = true;
    document.getElementById('password-required').style.display = 'inline';
    openModal('modalUsuario');
}

// Editar usuario
async function editarUsuario(id) {
    try {
        const response = await fetch(`../controllers/UsuarioController.php?action=obtener&id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('modalUsuarioTitle').textContent = 'Editar Usuario';
            document.getElementById('usuario_id').value = data.data.id;
            document.getElementById('nombre').value = data.data.nombre;
            document.getElementById('email').value = data.data.email;
            document.getElementById('tipo_usuario').value = data.data.tipo_usuario;
            document.getElementById('activo').checked = data.data.activo == 1;
            document.getElementById('password').value = '';
            document.getElementById('password').required = false;
            document.getElementById('password-required').style.display = 'none';
            openModal('modalUsuario');
        } else {
            showAlert('error', data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Error al cargar el usuario');
    }
}

// Guardar usuario
async function guardarUsuario(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const id = document.getElementById('usuario_id').value;
    formData.append('action', id ? 'actualizar' : 'crear');
    
    try {
        const response = await fetch('../controllers/UsuarioController.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('success', data.message);
            closeModal('modalUsuario');
            cargarUsuarios(paginaActual);
        } else {
            showAlert('error', data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Error al guardar el usuario');
    }
}

// Eliminar usuario
async function eliminarUsuario(id, nombre) {
    const confirmacion = await Swal.fire({
        title: '¿Estás seguro?',
        text: `¿Deseas eliminar al usuario "${nombre}"?`,
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
            
            const response = await fetch('../controllers/UsuarioController.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showAlert('success', data.message);
                cargarUsuarios(paginaActual);
            } else {
                showAlert('error', data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('error', 'Error al eliminar el usuario');
        }
    }
}

// Limpiar filtros
function limpiarFiltros() {
    document.getElementById('busquedaUsuario').value = '';
    document.getElementById('filtroTipo').value = '';
    document.getElementById('filtroActivo').value = '1';
    cargarUsuarios(1);
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

// Capitalize first letter
function ucFirst(text) {
    if (!text) return '';
    return text.charAt(0).toUpperCase() + text.slice(1);
}
