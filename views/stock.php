<?php
$pageName = 'Stock por Artículo';
$pageTitle = 'Stock por Artículo';
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
            <i class="fas fa-boxes"></i>
            Stock por Artículo
        </h3>
        <div class="btn-group">
            <button onclick="abrirModalArticulo()" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Artículo
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
                <input type="text" id="busquedaArticulo" class="form-control" placeholder="Buscar artículo...">
            </div>
            <div class="form-group">
                <select id="filtroActivo" class="form-control">
                    <option value="">Todos los estados</option>
                    <option value="1" selected>Activos</option>
                    <option value="0">Inactivos</option>
                </select>
            </div>
            <button onclick="cargarArticulos()" class="btn btn-primary">
                <i class="fas fa-search"></i> Buscar
            </button>
            <button onclick="limpiarFiltros()" class="btn btn-secondary">
                <i class="fas fa-eraser"></i> Limpiar
            </button>
        </div>
        
        <!-- Tabla -->
        <div class="table-container">
            <table class="table" id="tablaArticulos">
                <thead>
                    <tr>
                        <th>Nombre Artículo</th>
                        <th>Stock Disponible</th>
                        <th>En Albaranes</th>
                        <th>Reservado</th>
                        <th>Nombre Comercial</th>
                        <th>Stock Sage</th>
                        <th>Stock Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbody-articulos">
                    <tr>
                        <td colspan="8" class="text-center">
                            <i class="fas fa-spinner fa-spin"></i> Cargando...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <div id="paginacion-articulos" class="pagination-container"></div>
    </div>
</div>

<!-- Modal Artículo -->
<div class="modal" id="modalArticulo">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalArticuloTitle">Nuevo Artículo</h3>
            <button class="modal-close" onclick="closeModal('modalArticulo')">&times;</button>
        </div>
        <form id="formArticulo" onsubmit="guardarArticulo(event)">
            <div class="modal-body">
                <input type="hidden" id="articulo_id" name="id">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre_articulo">Nombre Artículo <span class="text-danger">*</span></label>
                        <input type="text" id="nombre_articulo" name="nombre_articulo" class="form-control" required>
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
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalArticulo')">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<script src="../assets/js/stock.js"></script>
<?php include __DIR__ . '/layouts/footer.php'; ?>
