<?php
$pageName = 'Movimientos';
$pageTitle = 'Movimientos de Stock';
include __DIR__ . '/layouts/header.php';

if (!hasPermission('movimientos')) {
    echo '<div class="alert alert-danger">No tienes permisos para acceder a este módulo</div>';
    include __DIR__ . '/layouts/footer.php';
    exit;
}
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-exchange-alt"></i>
            Movimientos de Stock
        </h3>
        <button onclick="abrirModalMovimiento()" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Movimiento
        </button>
    </div>
    
    <div class="card-body">
        <!-- Filtros -->
        <div class="filters-grid">
            <div class="form-group">
                <input type="text" id="busquedaMovimiento" class="form-control" placeholder="Buscar artículo...">
            </div>
            <div class="form-group">
                <select id="filtroTipo" class="form-control">
                    <option value="">Todos los tipos</option>
                    <option value="entrada">Entrada</option>
                    <option value="salida">Salida</option>
                    <option value="ajuste">Ajuste</option>
                </select>
            </div>
            <div class="form-group">
                <input type="date" id="fechaDesde" class="form-control" placeholder="Fecha desde">
            </div>
            <div class="form-group">
                <input type="date" id="fechaHasta" class="form-control" placeholder="Fecha hasta">
            </div>
            <button onclick="cargarMovimientos()" class="btn btn-primary">
                <i class="fas fa-search"></i> Buscar
            </button>
            <button onclick="limpiarFiltros()" class="btn btn-secondary">
                <i class="fas fa-eraser"></i> Limpiar
            </button>
        </div>
        
        <!-- Tabla -->
        <div class="table-container">
            <table class="table" id="tablaMovimientos">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Artículo</th>
                        <th>Tipo</th>
                        <th>Cantidad</th>
                        <th>Stock Después</th>
                        <th>Usuario</th>
                        <th>Observaciones</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbody-movimientos">
                    <tr>
                        <td colspan="8" class="text-center">
                            <i class="fas fa-spinner fa-spin"></i> Cargando...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <div id="paginacion-movimientos" class="pagination-container"></div>
    </div>
</div>

<!-- Modal Movimiento -->
<div class="modal-overlay" id="modalMovimiento">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalMovimientoTitle">Nuevo Movimiento de Stock</h3>
            <button class="modal-close" onclick="closeModal('modalMovimiento')">&times;</button>
        </div>
        <form id="formMovimiento" onsubmit="guardarMovimiento(event)">
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Entrada:</strong> Aumenta stock | 
                    <strong>Salida:</strong> Disminuye stock | 
                    <strong>Ajuste:</strong> Corrige diferencias
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="articulo_id_mov">Artículo <span class="text-danger">*</span></label>
                        <select id="articulo_id_mov" name="articulo_id" class="form-control" required onchange="cargarStockActual()">
                            <option value="">Seleccione un artículo...</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="tipo_movimiento">Tipo de Movimiento <span class="text-danger">*</span></label>
                        <select id="tipo_movimiento" name="tipo_movimiento" class="form-control" required>
                            <option value="">Seleccione...</option>
                            <option value="entrada">🟢 Entrada (Aumenta)</option>
                            <option value="salida">🔴 Salida (Disminuye)</option>
                            <option value="ajuste">🟡 Ajuste (Corrige)</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="cantidad_mov">Cantidad <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" id="cantidad_mov" name="cantidad" class="form-control" required min="0.01" placeholder="0.00">
                    </div>
                    
                    <div class="form-group">
                        <label>Stock Actual</label>
                        <input type="text" id="stock_actual_display" class="form-control" readonly placeholder="Seleccione artículo..." style="background: #f3f4f6; font-weight: bold;">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="observaciones_mov">Observaciones</label>
                    <textarea id="observaciones_mov" name="observaciones" class="form-control" rows="3" placeholder="Motivo del movimiento, proveedor, etc..."></textarea>
                </div>
                
                <!-- Campos ocultos para datos del artículo -->
                <input type="hidden" id="stock_disponible_mov" name="stock_disponible">
                <input type="hidden" id="cantidad_albaranes_mov" name="cantidad_albaranes">
                <input type="hidden" id="reservado_mov" name="reservado">
                <input type="hidden" id="nombre_comercial_mov" name="nombre_comercial">
                <input type="hidden" id="stock_sage_mov" name="stock_sage">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalMovimiento')">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Registrar Movimiento
                </button>
            </div>
        </form>
    </div>
</div>

<script src="../assets/js/movimientos.js"></script>
<?php include __DIR__ . '/layouts/footer.php'; ?>
