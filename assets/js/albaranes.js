/**
 * JavaScript para Albaranes con Gestión de Líneas
 */

let paginaActual = 1;
let totalPaginas = 1;
let estadoActual = 'pendiente';
let albaranEditando = null;
let lineasArticulos = [];

document.addEventListener('DOMContentLoaded', function() {
    cargarAlbaranes();
    cargarClientesSelect();
    cargarComercialesSelect();
    cargarMozosSelect();
    cargarArticulosSelect();
    
    // Fecha por defecto hoy
    if (document.getElementById('fecha_albaran')) {
        document.getElementById('fecha_albaran').valueAsDate = new Date();
    }
});

// Cargar selects
async function cargarClientesSelect() {
    try {
        const response = await fetch('../controllers/AlbaranController.php?action=listar_clientes');
        const data = await response.json();
        
        if (data.success) {
            const selects = ['cliente_id_alb', 'filtroCliente'];
            
            selects.forEach(selectId => {
                const select = document.getElementById(selectId);
                if (!select) return;
                
                if (selectId === 'cliente_id_alb') {
                    select.innerHTML = '<option value="">Seleccione...</option>';
                } else {
                    select.innerHTML = '<option value="">Todos los clientes</option>';
                }
                
                data.clientes.forEach(cliente => {
                    select.add(new Option(cliente.nombre_cliente, cliente.id));
                });
            });
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

async function cargarComercialesSelect() {
    try {
        const response = await fetch('../controllers/AlbaranController.php?action=listar_comerciales');
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById('comercial_id_alb');
            if (select) {
                select.innerHTML = '<option value="">Seleccione...</option>';
                data.comerciales.forEach(comercial => {
                    select.add(new Option(comercial.nombre, comercial.id));
                });
            }
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

async function cargarMozosSelect() {
    try {
        const response = await fetch('../controllers/AlbaranController.php?action=listar_mozos');
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById('mozo_id_alb');
            if (select) {
                select.innerHTML = '<option value="">Seleccione...</option>';
                data.mozos.forEach(mozo => {
                    select.add(new Option(mozo.nombre, mozo.id));
                });
            }
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

async function cargarArticulosSelect() {
    try {
        const response = await fetch('../controllers/AlbaranController.php?action=listar_articulos');
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById('articulo_id_linea');
            if (select) {
                select.innerHTML = '<option value="">Seleccione un artículo...</option>';
                data.articulos.forEach(articulo => {
                    select.add(new Option(articulo.nombre_articulo, articulo.id));
                });
            }
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Cambiar tab de estado
function cambiarEstadoAlbaran(estado) {
    estadoActual = estado;
    
    // Actualizar tabs activos
    document.querySelectorAll('.tab-estado').forEach(tab => {
        tab.classList.remove('active');
        if (tab.dataset.estado === estado) {
            tab.classList.add('active');
        }
    });
    
    cargarAlbaranes(1);
}

// Cargar albaranes
async function cargarAlbaranes(pagina = 1) {
    paginaActual = pagina;
    
    try {
        const filtros = {
            pagina: pagina,
            busqueda: document.getElementById('busquedaAlbaran')?.value || '',
            cliente_id: document.getElementById('filtroCliente')?.value || '',
            estado: estadoActual
        };
        
        const params = new URLSearchParams(filtros);
        const response = await fetch(`../controllers/AlbaranController.php?action=listar&${params}`);
        const data = await response.json();
        
        if (data.success) {
            mostrarAlbaranes(data.albaranes);
            mostrarPaginacion(data.total, data.pagina_actual);
            actualizarContadores();
        } else {
            showAlert('error', data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Error al cargar albaranes');
    }
}

// Mostrar albaranes en tabla
function mostrarAlbaranes(albaranes) {
    const tbody = document.getElementById('tbody-albaranes');
    
    if (albaranes.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center"><i class="fas fa-inbox"></i> No se encontraron albaranes</td></tr>';
        return;
    }
    
    tbody.innerHTML = albaranes.map(albaran => {
        return `
        <tr>
            <td><strong>${escapeHtml(albaran.numero_albaran)}</strong></td>
            <td>${formatearFecha(albaran.fecha)}</td>
            <td>${escapeHtml(albaran.nombre_cliente || '-')}</td>
            <td>${escapeHtml(albaran.comercial_nombre || '-')}</td>
            <td>${escapeHtml(albaran.mozo_nombre || '-')}</td>
            <td>${formatearNumero(albaran.total_peso || 0)} kg</td>
            <td><strong>${formatearNumero(albaran.total_general || 0)} €</strong></td>
            <td>
                <button onclick="editarAlbaran(${albaran.id})" class="btn btn-warning btn-sm" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button onclick="imprimirAlbaranPDF(${albaran.id})" class="btn btn-danger btn-sm" title="PDF">
                    <i class="fas fa-file-pdf"></i>
                </button>
                <button onclick="eliminarAlbaran(${albaran.id})" class="btn btn-danger btn-sm" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
        `;
    }).join('');
}

// Actualizar contadores de tabs
async function actualizarContadores() {
    const estados = ['pendiente', 'en_preparacion', 'pendiente_facturar', 'facturado', 'pendiente_precio'];
    
    for (const estado of estados) {
        try {
            const response = await fetch(`../controllers/AlbaranController.php?action=listar&estado=${estado}&pagina=1`);
            const data = await response.json();
            
            if (data.success) {
                const badge = document.getElementById(`count-${estado}`);
                if (badge) {
                    badge.textContent = data.total || 0;
                }
            }
        } catch (error) {
            console.error(`Error al contar ${estado}:`, error);
        }
    }
}

// Mostrar paginación
function mostrarPaginacion(total, paginaActual) {
    const itemsPorPagina = 25;
    totalPaginas = Math.ceil(total / itemsPorPagina);
    const container = document.getElementById('paginacion-albaranes');
    
    if (totalPaginas <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = '<div class="pagination">';
    html += `<button class="btn btn-sm ${paginaActual === 1 ? 'disabled' : ''}" onclick="cargarAlbaranes(${paginaActual - 1})" ${paginaActual === 1 ? 'disabled' : ''}><i class="fas fa-chevron-left"></i></button>`;
    
    for (let i = 1; i <= totalPaginas; i++) {
        if (i === 1 || i === totalPaginas || (i >= paginaActual - 2 && i <= paginaActual + 2)) {
            html += `<button class="btn btn-sm ${i === paginaActual ? 'btn-primary' : ''}" onclick="cargarAlbaranes(${i})">${i}</button>`;
        } else if (i === paginaActual - 3 || i === paginaActual + 3) {
            html += '<span>...</span>';
        }
    }
    
    html += `<button class="btn btn-sm ${paginaActual === totalPaginas ? 'disabled' : ''}" onclick="cargarAlbaranes(${paginaActual + 1})" ${paginaActual === totalPaginas ? 'disabled' : ''}><i class="fas fa-chevron-right"></i></button>`;
    html += '</div>';
    container.innerHTML = html;
}

// Abrir modal para nuevo albarán
function abrirModalAlbaran() {
    albaranEditando = null;
    lineasArticulos = [];
    
    document.getElementById('formAlbaran').reset();
    document.getElementById('albaran_id').value = '';
    if (document.getElementById('fecha_albaran')) {
        document.getElementById('fecha_albaran').valueAsDate = new Date();
    }
    
    document.getElementById('modalAlbaranTitle').textContent = 'Nuevo Albarán';
    document.getElementById('btnAgregarLinea').disabled = true;
    document.getElementById('btnPDFAlbaran').style.display = 'none';
    
    document.getElementById('tbody-lineas').innerHTML = '<tr><td colspan="8" class="text-center text-muted"><i class="fas fa-info-circle"></i> Guarde primero el albarán para agregar artículos</td></tr>';
    
    actualizarTotales();
    openModal('modalAlbaran');
}

// Generar número de albarán
async function generarNumeroAlbaran() {
    try {
        const response = await fetch('../controllers/AlbaranController.php?action=generar_numero');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('numero_albaran').value = data.numero;
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Editar albarán
async function editarAlbaran(id) {
    try {
        const response = await fetch(`../controllers/AlbaranController.php?action=obtener&id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            albaranEditando = data.albaran;
            lineasArticulos = data.albaran.lineas || [];
            
            document.getElementById('albaran_id').value = data.albaran.id;
            document.getElementById('numero_albaran').value = data.albaran.numero_albaran;
            document.getElementById('fecha_albaran').value = data.albaran.fecha;
            document.getElementById('cliente_id_alb').value = data.albaran.cliente_id;
            document.getElementById('comercial_id_alb').value = data.albaran.comercial_id || '';
            document.getElementById('mozo_id_alb').value = data.albaran.mozo_id || '';
            document.getElementById('estado_alb').value = data.albaran.estado;
            document.getElementById('faltan_precios_alb').checked = data.albaran.faltan_precios == 1;
            document.getElementById('preparado_alb').checked = data.albaran.preparado == 1;
            document.getElementById('unificado_alb').checked = data.albaran.unificado == 1;
            document.getElementById('prepago_alb').checked = data.albaran.prepago == 1;
            document.getElementById('observaciones_alb').value = data.albaran.observaciones || '';
            
            document.getElementById('modalAlbaranTitle').textContent = 'Editar Albarán';
            document.getElementById('btnAgregarLinea').disabled = false;
            document.getElementById('btnPDFAlbaran').style.display = 'inline-block';
            
            mostrarLineas();
            actualizarTotales();
            openModal('modalAlbaran');
        } else {
            showAlert('error', data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Error al cargar el albarán');
    }
}

// Guardar albarán
async function guardarAlbaran(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const id = document.getElementById('albaran_id').value;
    formData.append('action', id ? 'actualizar' : 'crear');
    
    try {
        const response = await fetch('../controllers/AlbaranController.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('success', data.message);
            
            // Si es nuevo, actualizar el ID y permitir agregar líneas
            if (!id && data.id) {
                document.getElementById('albaran_id').value = data.id;
                albaranEditando = { id: data.id };
                document.getElementById('btnAgregarLinea').disabled = false;
                document.getElementById('btnPDFAlbaran').style.display = 'inline-block';
                document.getElementById('modalAlbaranTitle').textContent = 'Editar Albarán';
            } else {
                closeModal('modalAlbaran');
                cargarAlbaranes(paginaActual);
            }
        } else {
            showAlert('error', data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Error al guardar el albarán');
    }
}

// Eliminar albarán
async function eliminarAlbaran(id) {
    const result = await Swal.fire({
        title: '¿Está seguro?',
        text: "Se eliminarán también todas las líneas de artículos",
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
            
            const response = await fetch('../controllers/AlbaranController.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showAlert('success', data.message);
                cargarAlbaranes(paginaActual);
            } else {
                showAlert('error', data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('error', 'Error al eliminar el albarán');
        }
    }
}

// ========== GESTIÓN DE LÍNEAS ==========

// Mostrar líneas
function mostrarLineas() {
    const tbody = document.getElementById('tbody-lineas');
    
    if (lineasArticulos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted"><i class="fas fa-info-circle"></i> No hay artículos agregados</td></tr>';
        return;
    }
    
    tbody.innerHTML = lineasArticulos.map(linea => {
        const importe = parseFloat(linea.importe || 0);
        return `
        <tr>
            <td>${escapeHtml(linea.nombre_articulo)}</td>
            <td>${escapeHtml(linea.partida || '-')}</td>
            <td>${formatearNumero(linea.unidades)}</td>
            <td>${formatearNumero(linea.peso)}</td>
            <td>${formatearNumero(linea.precio)}</td>
            <td>${formatearNumero(linea.importe_transporte)}</td>
            <td><strong>${formatearNumero(importe)} €</strong></td>
            <td>
                <button type="button" onclick="editarLinea(${linea.id})" class="btn btn-warning btn-sm" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" onclick="eliminarLinea(${linea.id})" class="btn btn-danger btn-sm" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
        `;
    }).join('');
}

// Agregar línea
function agregarLineaArticulo() {
    const albaranId = document.getElementById('albaran_id').value;
    
    if (!albaranId) {
        showAlert('error', 'Debe guardar primero el albarán');
        return;
    }
    
    document.getElementById('formLinea').reset();
    document.getElementById('linea_id').value = '';
    document.getElementById('linea_albaran_id').value = albaranId;
    document.getElementById('modalLineaTitle').textContent = 'Agregar Artículo';
    
    calcularImporteLinea();
    openModal('modalLineaArticulo');
}

// Editar línea
async function editarLinea(lineaId) {
    const linea = lineasArticulos.find(l => l.id == lineaId);
    
    if (linea) {
        document.getElementById('linea_id').value = linea.id;
        document.getElementById('linea_albaran_id').value = linea.albaran_id;
        document.getElementById('articulo_id_linea').value = linea.articulo_id;
        document.getElementById('partida_linea').value = linea.partida || '';
        document.getElementById('unidades_linea').value = linea.unidades;
        document.getElementById('peso_linea').value = linea.peso;
        document.getElementById('precio_linea').value = linea.precio;
        document.getElementById('importe_transporte_linea').value = linea.importe_transporte;
        
        document.getElementById('modalLineaTitle').textContent = 'Editar Artículo';
        
        calcularImporteLinea();
        openModal('modalLineaArticulo');
    }
}

// Guardar línea
async function guardarLinea(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const lineaId = document.getElementById('linea_id').value;
    formData.append('action', lineaId ? 'actualizar_articulo' : 'agregar_articulo');
    
    if (lineaId) {
        formData.append('id', lineaId);
    }
    
    try {
        const response = await fetch('../controllers/AlbaranController.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('success', data.message);
            closeModal('modalLineaArticulo');
            
            // Recargar el albarán para actualizar líneas
            const albaranId = document.getElementById('albaran_id').value;
            await editarAlbaran(albaranId);
        } else {
            showAlert('error', data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('error', 'Error al guardar la línea');
    }
}

// Eliminar línea
async function eliminarLinea(lineaId) {
    const result = await Swal.fire({
        title: '¿Eliminar artículo?',
        text: "Se actualizarán los totales del albarán",
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
            formData.append('action', 'eliminar_articulo');
            formData.append('id', lineaId);
            
            const response = await fetch('../controllers/AlbaranController.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showAlert('success', data.message);
                
                // Recargar el albarán
                const albaranId = document.getElementById('albaran_id').value;
                await editarAlbaran(albaranId);
            } else {
                showAlert('error', data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('error', 'Error al eliminar la línea');
        }
    }
}

// Calcular importe de línea
function calcularImporteLinea() {
    const peso = parseFloat(document.getElementById('peso_linea')?.value || 0);
    const precio = parseFloat(document.getElementById('precio_linea')?.value || 0);
    const transporte = parseFloat(document.getElementById('importe_transporte_linea')?.value || 0);
    
    const importe = (peso * precio) + transporte;
    
    const display = document.getElementById('importe_linea_display');
    if (display) {
        display.value = `${importe.toFixed(2)} €`;
    }
}

// Actualizar totales del albarán
function actualizarTotales() {
    let totalPeso = 0;
    let totalGeneral = 0;
    
    lineasArticulos.forEach(linea => {
        totalPeso += parseFloat(linea.peso || 0);
        totalGeneral += parseFloat(linea.importe || 0);
    });
    
    document.getElementById('total_peso').value = totalPeso.toFixed(2);
    document.getElementById('total_general').value = totalGeneral.toFixed(2);
    document.getElementById('total_peso_display').textContent = totalPeso.toFixed(2);
    document.getElementById('total_general_display').textContent = totalGeneral.toFixed(2);
}

// Imprimir PDF
function imprimirAlbaranPDF() {
    const albaranId = document.getElementById('albaran_id').value;
    if (albaranId) {
        window.open(`../controllers/AlbaranController.php?action=generar_pdf&id=${albaranId}`, '_blank');
    }
}

// Limpiar filtros
function limpiarFiltros() {
    document.getElementById('busquedaAlbaran').value = '';
    document.getElementById('filtroCliente').value = '';
    cargarAlbaranes(1);
}

// Utilidades
function formatearFecha(fecha) {
    if (!fecha) return '-';
    const date = new Date(fecha);
    return date.toLocaleDateString('es-ES', { year: 'numeric', month: '2-digit', day: '2-digit' });
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

