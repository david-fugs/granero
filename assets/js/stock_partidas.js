/**
 * JavaScript para Stock por Partida
 */

let paginaActual = 1;
let totalPaginas = 1;

// Cargar partidas al iniciar
document.addEventListener('DOMContentLoaded', function() {
    cargarPartidas();
    cargarArticulosSelect();
    // Fecha por defecto hoy
    document.getElementById('fecha_partida').value = new Date().toISOString().split('T')[0];
});

// Cargar artículos para el select
async function cargarArticulosSelect() {
    try {
        const response = await fetch('../controllers/StockPartidaController.php?action=listar_articulos');
        const data = await response.json();
        
        if (data.success) {
            const selectArticulo = document.getElementById('articulo_id');
            const filtroArticulo = document.getElementById('filtroArticulo');
            
            data.data.forEach(articulo => {
                const option1 = new Option(articulo.nombre_articulo, articulo.id);
                const option2 = new Option(articulo.nombre_articulo, articulo.id);
                selectArticulo.add(option1);
                filtroArticulo.add(option2);
            });
        }
    } catch (error) {
        console.error('Error al cargar artículos:', error);
    }
}

// Cargar lista de partidas
async function cargarPartidas(pagina = 1) {
    paginaActual = pagina;
    const busqueda = document.getElementById('busquedaPartida').value;
    const articuloId = document.getElementById('filtroArticulo').value;
    const activo = document.getElementById('filtroActivo').value;
    
    try {
        const response = await fetch(`../controllers/StockPartidaController.php?action=listar&pagina=${pagina}&busqueda=${encodeURIComponent(busqueda)}&articulo_id=${articuloId}&activo=${activo}`);
        const data = await response.json();
        
        if (data.success) {
            mostrarPartidas(data.data);
            mostrarPaginacion(data.paginacion);
        } else {
            showAlert('error', 'Error al cargar partidas');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Error de conexión al cargar partidas');
    }
}

// Mostrar partidas en la tabla
function mostrarPartidas(partidas) {
    const tbody = document.getElementById('tbody-partidas');
    
    if (partidas.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center">
                    <i class="fas fa-inbox"></i> No se encontraron partidas
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = partidas.map(partida => {
        const stockTotal = parseFloat(partida.stock_total || 0);
        const stockClass = stockTotal > 0 ? 'text-success' : (stockTotal < 0 ? 'text-danger' : '');
        
        return `
        <tr>
            <td><strong>${escapeHtml(partida.nombre_articulo)}</strong></td>
            <td><span class="badge badge-info">${escapeHtml(partida.partida)}</span></td>
            <td>${formatearFecha(partida.fecha_partida)}</td>
            <td class="text-right">${formatearNumero(partida.stock_disponible)}</td>
            <td class="text-right">${formatearNumero(partida.cantidad_albaranes)}</td>
            <td class="text-right">${formatearNumero(partida.reservado)}</td>
            <td class="text-right ${stockClass}"><strong>${formatearNumero(stockTotal)}</strong></td>
            <td>
                <div class="btn-group">
                    <button onclick="editarPartida(${partida.id})" class="btn btn-sm btn-primary" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="eliminarPartida(${partida.id}, '${escapeHtml(partida.partida)}')" class="btn btn-sm btn-danger" title="Eliminar">
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
    const container = document.getElementById('paginacion-partidas');
    
    if (totalPaginas <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = '<div class="pagination">';
    
    html += `<button class="btn btn-sm ${paginaActual === 1 ? 'disabled' : ''}" 
             onclick="cargarPartidas(${paginaActual - 1})" 
             ${paginaActual === 1 ? 'disabled' : ''}>
             <i class="fas fa-chevron-left"></i>
             </button>`;
    
    for (let i = 1; i <= totalPaginas; i++) {
        if (i === 1 || i === totalPaginas || (i >= paginaActual - 2 && i <= paginaActual + 2)) {
            html += `<button class="btn btn-sm ${i === paginaActual ? 'btn-primary' : ''}" 
                     onclick="cargarPartidas(${i})">${i}</button>`;
        } else if (i === paginaActual - 3 || i === paginaActual + 3) {
            html += '<span>...</span>';
        }
    }
    
    html += `<button class="btn btn-sm ${paginaActual === totalPaginas ? 'disabled' : ''}" 
             onclick="cargarPartidas(${paginaActual + 1})"
             ${paginaActual === totalPaginas ? 'disabled' : ''}>
             <i class="fas fa-chevron-right"></i>
             </button>`;
    
    html += '</div>';
    container.innerHTML = html;
}

// Abrir modal para nueva partida
function abrirModalPartida() {
    document.getElementById('modalPartidaTitle').textContent = 'Nueva Partida';
    document.getElementById('formPartida').reset();
    document.getElementById('partida_id').value = '';
    document.getElementById('activo').checked = true;
    document.getElementById('fecha_partida').value = new Date().toISOString().split('T')[0];
    openModal('modalPartida');
}

// Editar partida
async function editarPartida(id) {
    try {
        const response = await fetch(`../controllers/StockPartidaController.php?action=obtener&id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            const partida = data.data;
            document.getElementById('modalPartidaTitle').textContent = 'Editar Partida';
            document.getElementById('partida_id').value = partida.id;
            document.getElementById('articulo_id').value = partida.articulo_id;
            document.getElementById('partida').value = partida.partida;
            document.getElementById('fecha_partida').value = partida.fecha_partida;
            document.getElementById('stock_disponible').value = partida.stock_disponible;
            document.getElementById('cantidad_albaranes').value = partida.cantidad_albaranes;
            document.getElementById('reservado').value = partida.reservado;
            document.getElementById('nombre_comercial').value = partida.nombre_comercial || '';
            document.getElementById('stock_sage').value = partida.stock_sage;
            document.getElementById('activo').checked = partida.activo == 1;
            openModal('modalPartida');
        } else {
            showAlert('error', data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Error al cargar la partida');
    }
}

// Guardar partida
async function guardarPartida(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const id = document.getElementById('partida_id').value;
    formData.append('action', id ? 'actualizar' : 'crear');
    
    try {
        const response = await fetch('../controllers/StockPartidaController.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('success', data.message);
            closeModal('modalPartida');
            cargarPartidas(paginaActual);
        } else {
            showAlert('error', data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Error al guardar la partida');
    }
}

// Eliminar partida
async function eliminarPartida(id, nombre) {
    const confirmacion = await Swal.fire({
        title: '¿Estás seguro?',
        text: `¿Deseas eliminar la partida "${nombre}"?`,
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
            
            const response = await fetch('../controllers/StockPartidaController.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showAlert('success', data.message);
                cargarPartidas(paginaActual);
            } else {
                showAlert('error', data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('error', 'Error al eliminar la partida');
        }
    }
}

// Exportar a Excel
function exportarExcel() {
    showAlert('info', 'Exportando a Excel...', 1000);
    
    fetch(`../controllers/StockPartidaController.php?action=listar&pagina=1&busqueda=&articulo_id=&activo=&limit=10000`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.length > 0) {
                const datosExcel = data.data.map(p => ({
                    'Artículo': p.nombre_articulo,
                    'Partida': p.partida,
                    'Fecha': p.fecha_partida,
                    'Stock Disponible': parseFloat(p.stock_disponible || 0),
                    'En Albaranes': parseFloat(p.cantidad_albaranes || 0),
                    'Reservado': parseFloat(p.reservado || 0),
                    'Stock Sage': parseFloat(p.stock_sage || 0),
                    'Stock Total': parseFloat(p.stock_total || 0)
                }));
                
                const ws = XLSX.utils.json_to_sheet(datosExcel);
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, 'Stock Partidas');
                
                const fecha = new Date().toISOString().split('T')[0];
                XLSX.writeFile(wb, `stock_partidas_${fecha}.xlsx`);
                
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
    document.getElementById('busquedaPartida').value = '';
    document.getElementById('filtroArticulo').value = '';
    document.getElementById('filtroActivo').value = '1';
    cargarPartidas(1);
}

// Formatear fecha
function formatearFecha(fecha) {
    if (!fecha) return '-';
    const date = new Date(fecha + 'T00:00:00');
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    });
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

