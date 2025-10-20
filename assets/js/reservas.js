/**
 * JavaScript para Reservas
 */

let paginaActual = 1;
let totalPaginas = 1;
let reservaEditando = null;

document.addEventListener('DOMContentLoaded', function() {
    cargarReservas();
    cargarClientesSelect();
    cargarComercialesSelect();
    
    // Fecha por defecto hoy
    if (document.getElementById('fecha_reserva')) {
        document.getElementById('fecha_reserva').valueAsDate = new Date();
    }
});

// Cargar clientes para los selects
async function cargarClientesSelect() {
    try {
        const response = await fetch('../controllers/ReservaController.php?action=listar_clientes');
        const data = await response.json();
        
        if (data.success) {
            const selects = ['cliente_id_res', 'filtroCliente'];
            
            selects.forEach(selectId => {
                const select = document.getElementById(selectId);
                if (!select) return;
                
                if (selectId === 'cliente_id_res') {
                    select.innerHTML = '<option value="">Seleccione un cliente...</option>';
                } else {
                    select.innerHTML = '<option value="">Todos los clientes</option>';
                }
                
                data.clientes.forEach(cliente => {
                    select.add(new Option(cliente.nombre_cliente, cliente.id));
                });
            });
        }
    } catch (error) {
        console.error('Error al cargar clientes:', error);
    }
}

// Cargar comerciales para los selects
async function cargarComercialesSelect() {
    try {
        const response = await fetch('../controllers/ReservaController.php?action=listar_comerciales');
        const data = await response.json();
        
        if (data.success) {
            const selects = ['comercial_id_res', 'filtroComercial'];
            
            selects.forEach(selectId => {
                const select = document.getElementById(selectId);
                if (!select) return;
                
                if (selectId === 'comercial_id_res') {
                    select.innerHTML = '<option value="">Seleccione un comercial...</option>';
                } else {
                    select.innerHTML = '<option value="">Todos los comerciales</option>';
                }
                
                data.comerciales.forEach(comercial => {
                    select.add(new Option(comercial.nombre, comercial.id));
                });
            });
        }
    } catch (error) {
        console.error('Error al cargar comerciales:', error);
    }
}

// Cargar reservas
async function cargarReservas(pagina = 1) {
    paginaActual = pagina;
    
    try {
        const filtros = {
            pagina: pagina,
            busqueda: document.getElementById('busquedaReserva')?.value || '',
            cliente_id: document.getElementById('filtroCliente')?.value || '',
            comercial_id: document.getElementById('filtroComercial')?.value || '',
            estado: document.getElementById('filtroEstado')?.value || '',
            fecha_desde: document.getElementById('fechaDesde')?.value || '',
            fecha_hasta: document.getElementById('fechaHasta')?.value || ''
        };
        
        const params = new URLSearchParams(filtros);
        const response = await fetch(`../controllers/ReservaController.php?action=listar&${params}`);
        const data = await response.json();
        
        if (data.success) {
            mostrarReservas(data.reservas);
            mostrarPaginacion(data.total, data.pagina_actual);
        } else {
            showAlert('error', data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Error al cargar reservas');
    }
}

// Mostrar reservas en tabla
function mostrarReservas(reservas) {
    const tbody = document.getElementById('tbody-reservas');
    
    if (reservas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center"><i class="fas fa-inbox"></i> No se encontraron reservas</td></tr>';
        return;
    }
    
    tbody.innerHTML = reservas.map(reserva => {
        const estadoBadge = reserva.estado === 'enviado' 
            ? '<span class="badge badge-success">🟢 Enviado</span>' 
            : '<span class="badge badge-warning">🟡 Reservado</span>';
        
        const faltanPrecios = reserva.faltan_precios == 1 
            ? '<i class="fas fa-exclamation-triangle text-warning" title="Faltan precios"></i>' 
            : '<i class="fas fa-check text-success"></i>';
        
        const prepago = reserva.prepago == 1 
            ? '<i class="fas fa-dollar-sign text-success" title="Prepago"></i>' 
            : '<i class="fas fa-times text-muted"></i>';
        
        return `
        <tr>
            <td><strong>${escapeHtml(reserva.numero_reserva)}</strong></td>
            <td>${formatearFecha(reserva.fecha)}</td>
            <td>${escapeHtml(reserva.nombre_cliente || '-')}</td>
            <td>${escapeHtml(reserva.comercial_nombre || '-')}</td>
            <td>${escapeHtml(reserva.transportista || '-')}</td>
            <td>${estadoBadge}</td>
            <td class="text-center">${faltanPrecios}</td>
            <td class="text-center">${prepago}</td>
            <td>
                <button onclick="editarReserva(${reserva.id})" class="btn btn-warning btn-sm" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button onclick="imprimirReservaPDF(${reserva.id})" class="btn btn-danger btn-sm" title="PDF">
                    <i class="fas fa-file-pdf"></i>
                </button>
                <button onclick="eliminarReserva(${reserva.id})" class="btn btn-danger btn-sm" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
        `;
    }).join('');
}

// Mostrar paginación
function mostrarPaginacion(total, paginaActual) {
    const itemsPorPagina = 25;
    totalPaginas = Math.ceil(total / itemsPorPagina);
    const container = document.getElementById('paginacion-reservas');
    
    if (totalPaginas <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = '<div class="pagination">';
    html += `<button class="btn btn-sm ${paginaActual === 1 ? 'disabled' : ''}" onclick="cargarReservas(${paginaActual - 1})" ${paginaActual === 1 ? 'disabled' : ''}><i class="fas fa-chevron-left"></i></button>`;
    
    for (let i = 1; i <= totalPaginas; i++) {
        if (i === 1 || i === totalPaginas || (i >= paginaActual - 2 && i <= paginaActual + 2)) {
            html += `<button class="btn btn-sm ${i === paginaActual ? 'btn-primary' : ''}" onclick="cargarReservas(${i})">${i}</button>`;
        } else if (i === paginaActual - 3 || i === paginaActual + 3) {
            html += '<span>...</span>';
        }
    }
    
    html += `<button class="btn btn-sm ${paginaActual === totalPaginas ? 'disabled' : ''}" onclick="cargarReservas(${paginaActual + 1})" ${paginaActual === totalPaginas ? 'disabled' : ''}><i class="fas fa-chevron-right"></i></button>`;
    html += '</div>';
    container.innerHTML = html;
}

// Abrir modal para nueva reserva
function abrirModalReserva() {
    reservaEditando = null;
    document.getElementById('formReserva').reset();
    document.getElementById('reserva_id').value = '';
    if (document.getElementById('fecha_reserva')) {
        document.getElementById('fecha_reserva').valueAsDate = new Date();
    }
    document.getElementById('modalReservaTitle').textContent = 'Nueva Reserva';
    openModal('modalReserva');
}

// Generar número de reserva automático
async function generarNumeroReserva() {
    try {
        const response = await fetch('../controllers/ReservaController.php?action=generar_numero');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('numero_reserva').value = data.numero;
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Editar reserva
async function editarReserva(id) {
    try {
        const response = await fetch(`../controllers/ReservaController.php?action=obtener&id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            reservaEditando = data.reserva;
            
            document.getElementById('reserva_id').value = data.reserva.id;
            document.getElementById('numero_reserva').value = data.reserva.numero_reserva;
            document.getElementById('fecha_reserva').value = data.reserva.fecha;
            document.getElementById('cliente_id_res').value = data.reserva.cliente_id;
            document.getElementById('comercial_id_res').value = data.reserva.comercial_id || '';
            document.getElementById('transportista_res').value = data.reserva.transportista || '';
            document.getElementById('plataforma_carga').value = data.reserva.plataforma_carga || '';
            document.getElementById('estado_res').value = data.reserva.estado;
            document.getElementById('faltan_precios').checked = data.reserva.faltan_precios == 1;
            document.getElementById('prepago_res').checked = data.reserva.prepago == 1;
            document.getElementById('observaciones_res').value = data.reserva.observaciones || '';
            
            document.getElementById('modalReservaTitle').textContent = 'Editar Reserva';
            openModal('modalReserva');
        } else {
            showAlert('error', data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Error al cargar la reserva');
    }
}

// Guardar reserva
async function guardarReserva(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const id = document.getElementById('reserva_id').value;
    formData.append('action', id ? 'actualizar' : 'crear');
    
    try {
        const response = await fetch('../controllers/ReservaController.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('success', data.message);
            closeModal('modalReserva');
            cargarReservas(paginaActual);
        } else {
            showAlert('error', data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Error al guardar la reserva');
    }
}

// Eliminar reserva
async function eliminarReserva(id) {
    const result = await Swal.fire({
        title: '¿Está seguro?',
        text: "Esta acción no se puede deshacer",
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
            
            const response = await fetch('../controllers/ReservaController.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showAlert('success', data.message);
                cargarReservas(paginaActual);
            } else {
                showAlert('error', data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('error', 'Error al eliminar la reserva');
        }
    }
}

// Imprimir PDF de una reserva
async function imprimirReservaPDF(id) {
    window.open(`../controllers/ReservaController.php?action=generar_pdf&id=${id}`, '_blank');
}

// Limpiar filtros
function limpiarFiltros() {
    document.getElementById('busquedaReserva').value = '';
    document.getElementById('filtroCliente').value = '';
    document.getElementById('filtroComercial').value = '';
    document.getElementById('filtroEstado').value = '';
    document.getElementById('fechaDesde').value = '';
    document.getElementById('fechaHasta').value = '';
    cargarReservas(1);
}

// Formatear fecha
function formatearFecha(fecha) {
    if (!fecha) return '-';
    const date = new Date(fecha);
    return date.toLocaleDateString('es-ES', { year: 'numeric', month: '2-digit', day: '2-digit' });
}

// Escapar HTML
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

