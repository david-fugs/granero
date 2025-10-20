/**
 * JavaScript para Gestión de Stock por Artículo
 */

let paginaActual = 1;
let totalPaginas = 1;

// Cargar artículos al iniciar
document.addEventListener('DOMContentLoaded', function() {
    cargarArticulos();
});

// Cargar lista de artículos
async function cargarArticulos(pagina = 1) {
    paginaActual = pagina;
    const busqueda = document.getElementById('busquedaArticulo').value;
    const activo = document.getElementById('filtroActivo').value;
    
    try {
        const response = await fetch(`../controllers/StockController.php?action=listar&pagina=${pagina}&busqueda=${encodeURIComponent(busqueda)}&activo=${activo}`);
        const data = await response.json();
        
        if (data.success) {
            mostrarArticulos(data.data);
            mostrarPaginacion(data.paginacion);
        } else {
            showAlert('error', 'Error al cargar artículos');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Error de conexión al cargar artículos');
    }
}

// Mostrar artículos en la tabla
function mostrarArticulos(articulos) {
    const tbody = document.getElementById('tbody-articulos');
    
    if (articulos.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center">
                    <i class="fas fa-inbox"></i> No se encontraron artículos
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = articulos.map(articulo => {
        const stockTotal = parseFloat(articulo.stock_total || 0);
        const stockClass = stockTotal > 0 ? 'text-success' : (stockTotal < 0 ? 'text-danger' : '');
        
        return `
        <tr>
            <td><strong>${escapeHtml(articulo.nombre_articulo)}</strong></td>
            <td class="text-right">${formatearNumero(articulo.stock_disponible)}</td>
            <td class="text-right">${formatearNumero(articulo.cantidad_albaranes)}</td>
            <td class="text-right">${formatearNumero(articulo.reservado)}</td>
            <td>${escapeHtml(articulo.nombre_comercial || '-')}</td>
            <td class="text-right">${formatearNumero(articulo.stock_sage)}</td>
            <td class="text-right ${stockClass}"><strong>${formatearNumero(stockTotal)}</strong></td>
            <td>
                <div class="btn-group">
                    <button onclick="editarArticulo(${articulo.id})" class="btn btn-sm btn-primary" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="eliminarArticulo(${articulo.id}, '${escapeHtml(articulo.nombre_articulo)}')" class="btn btn-sm btn-danger" title="Eliminar">
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
    const container = document.getElementById('paginacion-articulos');
    
    if (totalPaginas <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = '<div class="pagination">';
    
    // Botón anterior
    html += `<button class="btn btn-sm ${paginaActual === 1 ? 'disabled' : ''}" 
             onclick="cargarArticulos(${paginaActual - 1})" 
             ${paginaActual === 1 ? 'disabled' : ''}>
             <i class="fas fa-chevron-left"></i>
             </button>`;
    
    // Páginas
    for (let i = 1; i <= totalPaginas; i++) {
        if (i === 1 || i === totalPaginas || (i >= paginaActual - 2 && i <= paginaActual + 2)) {
            html += `<button class="btn btn-sm ${i === paginaActual ? 'btn-primary' : ''}" 
                     onclick="cargarArticulos(${i})">${i}</button>`;
        } else if (i === paginaActual - 3 || i === paginaActual + 3) {
            html += '<span>...</span>';
        }
    }
    
    // Botón siguiente
    html += `<button class="btn btn-sm ${paginaActual === totalPaginas ? 'disabled' : ''}" 
             onclick="cargarArticulos(${paginaActual + 1})"
             ${paginaActual === totalPaginas ? 'disabled' : ''}>
             <i class="fas fa-chevron-right"></i>
             </button>`;
    
    html += '</div>';
    container.innerHTML = html;
}

// Abrir modal para nuevo artículo
function abrirModalArticulo() {
    document.getElementById('modalArticuloTitle').textContent = 'Nuevo Artículo';
    document.getElementById('formArticulo').reset();
    document.getElementById('articulo_id').value = '';
    document.getElementById('activo').checked = true;
    openModal('modalArticulo');
}

// Editar artículo
async function editarArticulo(id) {
    try {
        const response = await fetch(`../controllers/StockController.php?action=obtener&id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            const articulo = data.data;
            document.getElementById('modalArticuloTitle').textContent = 'Editar Artículo';
            document.getElementById('articulo_id').value = articulo.id;
            document.getElementById('nombre_articulo').value = articulo.nombre_articulo;
            document.getElementById('nombre_comercial').value = articulo.nombre_comercial || '';
            document.getElementById('stock_disponible').value = articulo.stock_disponible;
            document.getElementById('cantidad_albaranes').value = articulo.cantidad_albaranes;
            document.getElementById('reservado').value = articulo.reservado;
            document.getElementById('stock_sage').value = articulo.stock_sage;
            document.getElementById('activo').checked = articulo.activo == 1;
            openModal('modalArticulo');
        } else {
            showAlert('error', data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Error al cargar el artículo');
    }
}

// Guardar artículo
async function guardarArticulo(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const id = document.getElementById('articulo_id').value;
    formData.append('action', id ? 'actualizar' : 'crear');
    
    try {
        const response = await fetch('../controllers/StockController.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('success', data.message);
            closeModal('modalArticulo');
            cargarArticulos(paginaActual);
        } else {
            showAlert('error', data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Error al guardar el artículo');
    }
}

// Eliminar artículo
async function eliminarArticulo(id, nombre) {
    const confirmacion = await Swal.fire({
        title: '¿Estás seguro?',
        text: `¿Deseas eliminar el artículo "${nombre}"?`,
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
            
            const response = await fetch('../controllers/StockController.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showAlert('success', data.message);
                cargarArticulos(paginaActual);
            } else {
                showAlert('error', data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('error', 'Error al eliminar el artículo');
        }
    }
}

// Exportar a Excel
function exportarExcel() {
    showAlert('info', 'Exportando a Excel...', 1000);
    
    // Obtener todos los datos sin paginación
    fetch(`../controllers/StockController.php?action=listar&pagina=1&busqueda=&activo=&limit=10000`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.length > 0) {
                const datosExcel = data.data.map(a => ({
                    'Artículo': a.nombre_articulo,
                    'Stock Disponible': parseFloat(a.stock_disponible || 0),
                    'En Albaranes': parseFloat(a.cantidad_albaranes || 0),
                    'Reservado': parseFloat(a.reservado || 0),
                    'Nombre Comercial': a.nombre_comercial || '',
                    'Stock Sage': parseFloat(a.stock_sage || 0),
                    'Stock Total': parseFloat(a.stock_total || 0)
                }));
                
                const ws = XLSX.utils.json_to_sheet(datosExcel);
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, 'Stock');
                
                const fecha = new Date().toISOString().split('T')[0];
                XLSX.writeFile(wb, `stock_articulos_${fecha}.xlsx`);
                
                showAlert('success', 'Excel generado correctamente');
            } else {
                showAlert('warning', 'No hay datos para exportar');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Error al exportar a Excel');
        });
}

// Limpiar filtros
function limpiarFiltros() {
    document.getElementById('busquedaArticulo').value = '';
    document.getElementById('filtroActivo').value = '1';
    cargarArticulos(1);
}

// Formatear número
function formatearNumero(numero) {
    return parseFloat(numero || 0).toLocaleString('es-ES', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
