/**
 * JavaScript para Movimientos de Stock
 */

let paginaActual = 1;
let totalPaginas = 1;

document.addEventListener('DOMContentLoaded', function() {
    cargarMovimientos();
    cargarArticulosSelect();
});

// Cargar artículos para el select
async function cargarArticulosSelect() {
    try {
        const response = await fetch('../controllers/MovimientoController.php?action=listar_articulos');
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById('articulo_id_mov');
            if (select) {
                select.innerHTML = '<option value="">Seleccione un artículo...</option>';
                
                data.articulos.forEach(articulo => {
                    const option = new Option(articulo.nombre_articulo, articulo.id);
                    // Guardar datos del artículo en el option
                    option.dataset.stockDisponible = articulo.stock_disponible;
                    option.dataset.cantidadAlbaranes = articulo.cantidad_albaranes;
                    option.dataset.reservado = articulo.reservado;
                    option.dataset.nombreComercial = articulo.nombre_comercial;
                    option.dataset.stockSage = articulo.stock_sage;
                    select.add(option);
                });
            }
        }
    } catch (error) {
        console.error('Error al cargar artículos:', error);
    }
}

// Cargar stock actual al seleccionar artículo
function cargarStockActual() {
    const select = document.getElementById('articulo_id_mov');
    const selectedOption = select.options[select.selectedIndex];
    
    if (selectedOption.value) {
        const stockDisponible = parseFloat(selectedOption.dataset.stockDisponible || 0);
        const cantidadAlbaranes = parseFloat(selectedOption.dataset.cantidadAlbaranes || 0);
        const reservado = parseFloat(selectedOption.dataset.reservado || 0);
        const stockTotal = stockDisponible + cantidadAlbaranes + reservado;
        
        document.getElementById('stock_actual_display').value = `${stockTotal.toFixed(2)} kg (Disp: ${stockDisponible.toFixed(2)} kg)`;
        
        // Guardar en campos ocultos
        document.getElementById('stock_disponible_mov').value = stockDisponible;
        document.getElementById('cantidad_albaranes_mov').value = cantidadAlbaranes;
        document.getElementById('reservado_mov').value = reservado;
        document.getElementById('nombre_comercial_mov').value = selectedOption.dataset.nombreComercial;
        document.getElementById('stock_sage_mov').value = selectedOption.dataset.stockSage;
    } else {
        document.getElementById('stock_actual_display').value = '';
        document.getElementById('stock_disponible_mov').value = '';
        document.getElementById('cantidad_albaranes_mov').value = '';
        document.getElementById('reservado_mov').value = '';
        document.getElementById('nombre_comercial_mov').value = '';
        document.getElementById('stock_sage_mov').value = '';
    }
}

async function cargarMovimientos(pagina = 1) {
    paginaActual = pagina;
    
    try {
        const filtros = {
            pagina: pagina,
            busqueda: document.getElementById('busquedaMovimiento')?.value || '',
            tipo: document.getElementById('filtroTipo')?.value || '',
            fecha_desde: document.getElementById('fechaDesde')?.value || '',
            fecha_hasta: document.getElementById('fechaHasta')?.value || ''
        };
        
        const params = new URLSearchParams(filtros);
        const response = await fetch(`../controllers/MovimientoController.php?action=listar&${params}`);
        const data = await response.json();
        
        if (data.success) {
            mostrarMovimientos(data.movimientos);
            mostrarPaginacion(data.total, data.pagina_actual);
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Error al cargar movimientos');
    }
}

function mostrarMovimientos(movimientos) {
    const tbody = document.getElementById('tbody-movimientos');
    if (movimientos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center"><i class="fas fa-inbox"></i> No hay movimientos registrados</td></tr>';
        return;
    }
    
    tbody.innerHTML = movimientos.map(mov => {
        const iconoTipo = {
            'entrada': '<i class="fas fa-arrow-down text-success"></i>',
            'salida': '<i class="fas fa-arrow-up text-danger"></i>',
            'ajuste': '<i class="fas fa-adjust text-warning"></i>'
        }[mov.tipo_movimiento] || '';
        
        return `
        <tr>
            <td>${formatearFechaHora(mov.fecha_movimiento)}</td>
            <td><strong>${escapeHtml(mov.nombre_articulo)}</strong></td>
            <td>${iconoTipo} ${ucFirst(mov.tipo_movimiento)}</td>
            <td class="text-right"><strong>${formatearNumero(mov.cantidad)}</strong></td>
            <td class="text-right">${formatearNumero(mov.stock_despues)}</td>
            <td>${escapeHtml(mov.usuario_nombre || '-')}</td>
            <td>${escapeHtml(mov.observaciones || '-')}</td>
            <td>
                <button onclick="eliminarMovimiento(${mov.id})" class="btn btn-danger btn-sm" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
        `;
    }).join('');
}

function mostrarPaginacion(total, paginaActual) {
    const itemsPorPagina = 25;
    totalPaginas = Math.ceil(total / itemsPorPagina);
    const container = document.getElementById('paginacion-movimientos');
    if (!container) return;
    
    if (totalPaginas <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = '<div class="pagination">';
    html += `<button class="btn btn-sm ${paginaActual === 1 ? 'disabled' : ''}" onclick="cargarMovimientos(${paginaActual - 1})" ${paginaActual === 1 ? 'disabled' : ''}><i class="fas fa-chevron-left"></i></button>`;
    
    for (let i = 1; i <= totalPaginas; i++) {
        if (i === 1 || i === totalPaginas || (i >= paginaActual - 2 && i <= paginaActual + 2)) {
            html += `<button class="btn btn-sm ${i === paginaActual ? 'btn-primary' : ''}" onclick="cargarMovimientos(${i})">${i}</button>`;
        } else if (i === paginaActual - 3 || i === paginaActual + 3) {
            html += '<span>...</span>';
        }
    }
    
    html += `<button class="btn btn-sm ${paginaActual === totalPaginas ? 'disabled' : ''}" onclick="cargarMovimientos(${paginaActual + 1})" ${paginaActual === totalPaginas ? 'disabled' : ''}><i class="fas fa-chevron-right"></i></button>`;
    html += '</div>';
    container.innerHTML = html;
}

function abrirModalMovimiento() {
    document.getElementById('formMovimiento').reset();
    document.getElementById('stock_actual_display').value = '';
    document.getElementById('modalMovimientoTitle').textContent = 'Nuevo Movimiento de Stock';
    openModal('modalMovimiento');
}

// Guardar movimiento
async function guardarMovimiento(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    formData.append('action', 'crear');
    
    try {
        const response = await fetch('../controllers/MovimientoController.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('success', data.message);
            closeModal('modalMovimiento');
            cargarMovimientos(paginaActual);
            // Recargar artículos para actualizar stock
            cargarArticulosSelect();
        } else {
            showAlert('error', data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Error al guardar el movimiento');
    }
}

// Eliminar movimiento
async function eliminarMovimiento(id) {
    const result = await Swal.fire({
        title: '¿Está seguro?',
        text: "Esta acción no se puede deshacer. El stock se revertirá automáticamente.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    });
    
    if (result.isConfirmed) {
        try {
            const formData = new FormData();
            formData.append('action', 'eliminar');
            formData.append('id', id);
            
            const response = await fetch('../controllers/MovimientoController.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showAlert('success', data.message);
                cargarMovimientos(paginaActual);
                cargarArticulosSelect();
            } else {
                showAlert('error', data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('error', 'Error al eliminar el movimiento');
        }
    }
}

function limpiarFiltros() {
    document.getElementById('busquedaMovimiento').value = '';
    document.getElementById('filtroTipo').value = '';
    document.getElementById('fechaDesde').value = '';
    document.getElementById('fechaHasta').value = '';
    cargarMovimientos(1);
}

function formatearFechaHora(fecha) {
    if (!fecha) return '-';
    const date = new Date(fecha);
    return date.toLocaleString('es-ES', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' });
}

function formatearNumero(numero) {
    return parseFloat(numero || 0).toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function ucFirst(text) {
    if (!text) return '';
    return text.charAt(0).toUpperCase() + text.slice(1);
}

