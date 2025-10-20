<?php
$pageName = 'Reservas';
$pageTitle = 'Gestión de Reservas';
include __DIR__ . '/layouts/header.php';

if (!hasPermission('reservas')) {
    echo '<div class="alert alert-danger">No tienes permisos para acceder a este módulo</div>';
    include __DIR__ . '/layouts/footer.php';
    exit;
}
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-bookmark"></i>
            Gestión de Reservas
        </h3>
        <button onclick="abrirModalReserva()" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nueva Reserva
        </button>
    </div>
    
    <div class="card-body">
        <!-- Filtros -->
        <div class="filters-grid">
            <div class="form-group">
                <input type="text" id="busquedaReserva" class="form-control" placeholder="Buscar por número, cliente...">
            </div>
            <div class="form-group">
                <select id="filtroCliente" class="form-control">
                    <option value="">Todos los clientes</option>
                </select>
            </div>
            <div class="form-group">
                <select id="filtroComercial" class="form-control">
                    <option value="">Todos los comerciales</option>
                </select>
            </div>
            <div class="form-group">
                <select id="filtroEstado" class="form-control">
                    <option value="">Todos los estados</option>
                    <option value="reservado">Reservado</option>
                    <option value="enviado">Enviado</option>
                </select>
            </div>
            <div class="form-group">
                <input type="date" id="fechaDesde" class="form-control">
            </div>
            <div class="form-group">
                <input type="date" id="fechaHasta" class="form-control">
            </div>
            <button onclick="cargarReservas()" class="btn btn-primary">
                <i class="fas fa-search"></i> Buscar
            </button>
            <button onclick="limpiarFiltros()" class="btn btn-secondary">
                <i class="fas fa-eraser"></i> Limpiar
            </button>
        </div>
        
        <!-- Tabla -->
        <div class="table-container">
            <table class="table" id="tablaReservas">
                <thead>
                    <tr>
                        <th>Nº Reserva</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Comercial</th>
                        <th>Transportista</th>
                        <th>Estado</th>
                        <th>Precios</th>
                        <th>Prepago</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbody-reservas">
                    <tr>
                        <td colspan="9" class="text-center">
                            <i class="fas fa-spinner fa-spin"></i> Cargando...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <div id="paginacion-reservas" class="pagination-container"></div>
    </div>
</div>

<!-- Modal Reserva -->
<div class="modal" id="modalReserva">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h3 id="modalReservaTitle">Nueva Reserva</h3>
            <button class="modal-close" onclick="closeModal('modalReserva')">&times;</button>
        </div>
        <form id="formReserva" onsubmit="guardarReserva(event)">
            <input type="hidden" id="reserva_id" name="id">
            
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label for="numero_reserva">Número de Reserva <span class="text-danger">*</span></label>
                        <div style="display: flex; gap: 10px;">
                            <input type="text" id="numero_reserva" name="numero_reserva" class="form-control" required placeholder="RES-000001">
                            <button type="button" onclick="generarNumeroReserva()" class="btn btn-secondary" title="Generar automático">
                                <i class="fas fa-magic"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="fecha_reserva">Fecha <span class="text-danger">*</span></label>
                        <input type="date" id="fecha_reserva" name="fecha" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="cliente_id_res">Cliente <span class="text-danger">*</span></label>
                        <select id="cliente_id_res" name="cliente_id" class="form-control" required>
                            <option value="">Seleccione un cliente...</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="comercial_id_res">Comercial</label>
                        <select id="comercial_id_res" name="comercial_id" class="form-control">
                            <option value="">Seleccione un comercial...</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="transportista_res">Transportista</label>
                        <input type="text" id="transportista_res" name="transportista" class="form-control" placeholder="Nombre del transportista">
                    </div>
                    
                    <div class="form-group">
                        <label for="plataforma_carga">Plataforma de Carga</label>
                        <input type="text" id="plataforma_carga" name="plataforma_carga" class="form-control" placeholder="Ubicación de carga">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="estado_res">Estado</label>
                        <select id="estado_res" name="estado" class="form-control">
                            <option value="reservado">🟡 Reservado</option>
                            <option value="enviado">🟢 Enviado</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="display: flex; gap: 30px; align-items: center; padding-top: 30px;">
                        <label class="checkbox-label">
                            <input type="checkbox" id="faltan_precios" name="faltan_precios">
                            Faltan Precios
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" id="prepago_res" name="prepago">
                            Prepago
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="observaciones_res">Observaciones</label>
                    <textarea id="observaciones_res" name="observaciones" class="form-control" rows="3" placeholder="Notas adicionales..."></textarea>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalReserva')">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<script src="../assets/js/reservas.js"></script>
<?php include __DIR__ . '/layouts/footer.php'; ?>
</div>

<script src="../assets/js/reservas.js"></script>
<?php include __DIR__ . '/layouts/footer.php'; ?>
