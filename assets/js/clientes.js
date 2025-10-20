/**
 * Clientes JavaScript
 */

let currentPage = 1;
let totalPages = 1;

document.addEventListener('DOMContentLoaded', function() {
    cargarClientes();
    sincronizarScrolls();
    
    // Buscar al escribir (con debounce)
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', debounce(function() {
        currentPage = 1;
        cargarClientes();
    }, 500));
});

// Sincronizar scrolls horizontal superior e inferior
function sincronizarScrolls() {
    const scrollTop = document.getElementById('scrollTop');
    const scrollMain = document.getElementById('scrollMain');
    const scrollTopContent = document.getElementById('scrollTopContent');
    const table = document.getElementById('tablaClientes');
    
    // Ajustar ancho del scroll superior
    scrollTopContent.style.width = table.offsetWidth + 'px';
    
    // Sincronizar scroll superior con tabla
    scrollTop.addEventListener('scroll', function() {
        scrollMain.scrollLeft = this.scrollLeft;
    });
    
    // Sincronizar scroll tabla con superior
    scrollMain.addEventListener('scroll', function() {
        scrollTop.scrollLeft = this.scrollLeft;
    });
    
    // Reajustar en resize
    window.addEventListener('resize', function() {
        scrollTopContent.style.width = table.offsetWidth + 'px';
    });
}

// Cargar clientes
async function cargarClientes() {
    try {
        const search = document.getElementById('searchInput').value;
        const activo = document.getElementById('filterActivo').value;
        const transporte = document.getElementById('filterTransporte').value;
        
        const params = new URLSearchParams({
            page: currentPage,
            search: search,
        });
        
        if (activo !== '') params.append('activo', activo);
        if (transporte !== '') params.append('paga_transporte', transporte);
        
        const response = await fetch(`../controllers/ClienteController.php?action=listar&${params}`);
        const data = await response.json();
        
        if (data.success) {
            renderClientes(data.clientes);
            renderPaginacion(data.pagination);
        } else {
            showError('Error', data.message);
        }
    } catch (error) {
        console.error('Error al cargar clientes:', error);
        showError('Error', 'No se pudieron cargar los clientes');
    }
}

// Renderizar clientes en tabla
function renderClientes(clientes) {
    const tbody = document.getElementById('tablaClientesBody');
    
    if (clientes.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="10" class="text-center text-muted">
                    No se encontraron clientes
                </td>
            </tr>
        `;
        return;
    }
    
    let html = '';
    clientes.forEach(cliente => {
        const estadoBadge = cliente.activo == 1 
            ? '<span class="badge badge-success">Activo</span>'
            : '<span class="badge badge-secondary">Inactivo</span>';
        
        const transporteBadge = cliente.paga_transporte === 'si'
            ? '<span class="badge badge-success">Sí</span>'
            : '<span class="badge badge-secondary">No</span>';
        
        html += `
            <tr>
                <td><strong>${cliente.codigo_cliente}</strong></td>
                <td>${cliente.nombre_cliente}</td>
                <td>${cliente.nif || '-'}</td>
                <td>${transporteBadge}</td>
                <td>${formatCurrency(cliente.importe_riesgo)}</td>
                <td class="${parseFloat(cliente.deuda) > 0 ? 'text-danger font-weight-bold' : ''}">
                    ${formatCurrency(cliente.deuda)}
                </td>
                <td>${cliente.plat || '-'}</td>
                <td>${cliente.carga || '-'}</td>
                <td>${estadoBadge}</td>
                <td>
                    <div class="table-actions">
                        <button onclick="editarCliente(${cliente.id})" class="btn btn-sm btn-info" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="eliminarCliente(${cliente.id}, '${cliente.nombre_cliente}')" class="btn btn-sm btn-danger" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    
    // Reajustar scroll superior
    const table = document.getElementById('tablaClientes');
    const scrollTopContent = document.getElementById('scrollTopContent');
    scrollTopContent.style.width = table.offsetWidth + 'px';
}

// Renderizar paginación
function renderPaginacion(pagination) {
    const container = document.getElementById('paginacion');
    totalPages = pagination.pages;
    
    let html = '';
    
    // Botón anterior
    html += `
        <button class="btn btn-sm ${currentPage === 1 ? 'disabled' : ''}" 
                onclick="cambiarPagina(${currentPage - 1})" 
                ${currentPage === 1 ? 'disabled' : ''}>
            <i class="fas fa-chevron-left"></i>
        </button>
    `;
    
    // Páginas
    if (totalPages <= 7) {
        for (let i = 1; i <= totalPages; i++) {
            html += `
                <button class="btn btn-sm ${i === currentPage ? 'active' : ''}" 
                        onclick="cambiarPagina(${i})">
                    ${i}
                </button>
            `;
        }
    } else {
        // Primera página
        html += `
            <button class="btn btn-sm ${1 === currentPage ? 'active' : ''}" 
                    onclick="cambiarPagina(1)">1</button>
        `;
        
        if (currentPage > 3) {
            html += '<span>...</span>';
        }
        
        // Páginas alrededor de la actual
        const start = Math.max(2, currentPage - 1);
        const end = Math.min(totalPages - 1, currentPage + 1);
        
        for (let i = start; i <= end; i++) {
            html += `
                <button class="btn btn-sm ${i === currentPage ? 'active' : ''}" 
                        onclick="cambiarPagina(${i})">
                    ${i}
                </button>
            `;
        }
        
        if (currentPage < totalPages - 2) {
            html += '<span>...</span>';
        }
        
        // Última página
        html += `
            <button class="btn btn-sm ${totalPages === currentPage ? 'active' : ''}" 
                    onclick="cambiarPagina(${totalPages})">
                ${totalPages}
            </button>
        `;
    }
    
    // Botón siguiente
    html += `
        <button class="btn btn-sm ${currentPage === totalPages ? 'disabled' : ''}" 
                onclick="cambiarPagina(${currentPage + 1})" 
                ${currentPage === totalPages ? 'disabled' : ''}>
            <i class="fas fa-chevron-right"></i>
        </button>
    `;
    
    // Info de registros
    html += `
        <span style="margin-left: 1rem; color: #6b7280;">
            Mostrando ${((currentPage - 1) * 25) + 1} - ${Math.min(currentPage * 25, pagination.total)} de ${pagination.total}
        </span>
    `;
    
    container.innerHTML = html;
}

// Cambiar página
function cambiarPagina(page) {
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    cargarClientes();
}

// Filtrar clientes
function filtrarClientes() {
    currentPage = 1;
    cargarClientes();
}

// Guardar cliente
async function guardarCliente(event) {
    event.preventDefault();
    
    const formData = new FormData(document.getElementById('formCliente'));
    const clienteId = document.getElementById('clienteId').value;
    
    const action = clienteId ? 'actualizar' : 'crear';
    
    try {
        showLoading('Guardando...');
        
        const response = await fetch(`../controllers/ClienteController.php?action=${action}`, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        hideLoading();
        
        if (data.success) {
            showSuccess('¡Éxito!', data.message);
            closeModal('modalCliente');
            resetForm('formCliente');
            cargarClientes();
        } else {
            showError('Error', data.message);
        }
    } catch (error) {
        hideLoading();
        console.error('Error al guardar cliente:', error);
        showError('Error', 'No se pudo guardar el cliente');
    }
}

// Editar cliente
async function editarCliente(id) {
    try {
        showLoading('Cargando...');
        
        const response = await fetch(`../controllers/ClienteController.php?action=obtener&id=${id}`);
        const data = await response.json();
        
        hideLoading();
        
        if (data.success) {
            const cliente = data.cliente;
            
            document.getElementById('modalClienteTitle').textContent = 'Editar Cliente';
            document.getElementById('clienteId').value = cliente.id;
            document.getElementById('codigo_cliente').value = cliente.codigo_cliente;
            document.getElementById('nombre_cliente').value = cliente.nombre_cliente;
            document.getElementById('nif').value = cliente.nif || '';
            document.getElementById('paga_transporte').value = cliente.paga_transporte;
            document.getElementById('importe_riesgo').value = cliente.importe_riesgo;
            document.getElementById('deuda').value = cliente.deuda;
            document.getElementById('plat').value = cliente.plat || '';
            document.getElementById('carga').value = cliente.carga || '';
            document.getElementById('activo').value = cliente.activo;
            
            openModal('modalCliente');
        } else {
            showError('Error', data.message);
        }
    } catch (error) {
        hideLoading();
        console.error('Error al cargar cliente:', error);
        showError('Error', 'No se pudo cargar el cliente');
    }
}

// Eliminar cliente
function eliminarCliente(id, nombre) {
    confirmDelete(async function() {
        try {
            showLoading('Eliminando...');
            
            const formData = new FormData();
            formData.append('id', id);
            
            const response = await fetch('../controllers/ClienteController.php?action=eliminar', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            hideLoading();
            
            if (data.success) {
                showSuccess('¡Eliminado!', `El cliente ${nombre} ha sido eliminado`);
                cargarClientes();
            } else {
                showError('Error', data.message);
            }
        } catch (error) {
            hideLoading();
            console.error('Error al eliminar cliente:', error);
            showError('Error', 'No se pudo eliminar el cliente');
        }
    });
}

// Cerrar modal y resetear formulario
document.querySelector('#modalCliente .modal-close').addEventListener('click', function() {
    resetForm('formCliente');
    document.getElementById('modalClienteTitle').textContent = 'Nuevo Cliente';
    document.getElementById('clienteId').value = '';
});

document.querySelector('#modalCliente .btn-secondary').addEventListener('click', function() {
    resetForm('formCliente');
    document.getElementById('modalClienteTitle').textContent = 'Nuevo Cliente';
    document.getElementById('clienteId').value = '';
});

// Exportar a Excel
function exportarExcel() {
    const table = document.getElementById('tablaClientes');
    const wb = XLSX.utils.table_to_book(table, {sheet: "Clientes"});
    
    // Generar nombre de archivo con fecha
    const fecha = new Date().toISOString().split('T')[0];
    const filename = `clientes_${fecha}.xlsx`;
    
    XLSX.writeFile(wb, filename);
    
    showSuccess('Exportado', 'El archivo se ha descargado correctamente');
}
