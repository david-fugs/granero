<?php
$pageName = 'Stock por Artículo/Partida';
$pageTitle = 'Stock por Artículo/Partida';
include __DIR__ . '/layouts/header.php';

if (!hasPermission('stock')) {
    echo '<div class="alert alert-danger">No tienes permisos para acceder a este módulo</div>';
    include __DIR__ . '/layouts/footer.php';
    exit;
}
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-box-open"></i>
            Stock por Artículo/Partida
        </h3>
        <div class="btn-group">
            <button onclick="abrirModalPartida()" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Partida
            </button>
            <button onclick="exportarExcel()" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Exportar
            </button>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Filtros -->
        <div class="filters-grid">
            <div class="form-group">
                <input type="text" id="busquedaPartida" class="form-control" placeholder="Buscar por artículo o partida...">
            </div>
            <div class="form-group">
                <select id="filtroArticulo" class="form-control">
                    <option value="">Todos los artículos</option>
                </select>
            </div>
            <div class="form-group">
                <select id="filtroActivo" class="form-control">
                    <option value="">Todos los estados</option>
                    <option value="1" selected>Activos</option>
                    <option value="0">Inactivos</option>
                </select>
            </div>
            <button onclick="cargarPartidas()" class="btn btn-primary">
                <i class="fas fa-search"></i> Buscar
            </button>
            <button onclick="limpiarFiltros()" class="btn btn-secondary">
                <i class="fas fa-eraser"></i> Limpiar
            </button>
        </div>
        
        <!-- Tabla -->
        <div class="table-container">
            <table class="table" id="tablaPartidas">
                <thead>
                    <tr>
                        <th>Artículo</th>
                        <th>Partida</th>
                        <th>Fecha Partida</th>
                        <th>Stock Disponible</th>
                        <th>En Albaranes</th>
                        <th>Reservado</th>
                        <th>Stock Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbody-partidas">
                    <tr>
                        <td colspan="8" class="text-center">
                            <i class="fas fa-spinner fa-spin"></i> Cargando...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <div id="paginacion-partidas" class="pagination-container"></div>
    </div>
</div>

<!-- Modal Partida -->
<div class="modal" id="modalPartida">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalPartidaTitle">Nueva Partida</h3>
            <button class="modal-close" onclick="closeModal('modalPartida')">&times;</button>
        </div>
        <form id="formPartida" onsubmit="guardarPartida(event)">
            <div class="modal-body">
                <input type="hidden" id="partida_id" name="id">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="articulo_id">Artículo <span class="text-danger">*</span></label>
                        <select id="articulo_id" name="articulo_id" class="form-control" required>
                            <option value="">Seleccione un artículo...</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="partida">Nº Partida <span class="text-danger">*</span></label>
                        <input type="text" id="partida" name="partida" class="form-control" required placeholder="Ej: PART-001">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="fecha_partida">Fecha Partida <span class="text-danger">*</span></label>
                        <input type="date" id="fecha_partida" name="fecha_partida" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="nombre_comercial">Nombre Comercial</label>
                        <input type="text" id="nombre_comercial" name="nombre_comercial" class="form-control">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="stock_disponible">Stock Disponible</label>
                        <input type="number" step="0.01" id="stock_disponible" name="stock_disponible" class="form-control" value="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="cantidad_albaranes">En Albaranes</label>
                        <input type="number" step="0.01" id="cantidad_albaranes" name="cantidad_albaranes" class="form-control" value="0">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="reservado">Reservado</label>
                        <input type="number" step="0.01" id="reservado" name="reservado" class="form-control" value="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="stock_sage">Stock Sage</label>
                        <input type="number" step="0.01" id="stock_sage" name="stock_sage" class="form-control" value="0">
                    </div>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" id="activo" name="activo" class="form-check-input" checked>
                    <label for="activo" class="form-check-label">Activo</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalPartida')">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<script src="../assets/js/stock_partidas.js"></script>
<?php include __DIR__ . '/layouts/footer.php'; ?>
