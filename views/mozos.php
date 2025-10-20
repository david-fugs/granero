<?php
$pageName = 'Mozos';
$pageTitle = 'Gestión de Mozos';
include __DIR__ . '/layouts/header.php';

if (!hasPermission('mozos')) {
    echo '<div class="alert alert-danger">No tienes permisos para acceder a este módulo</div>';
    include __DIR__ . '/layouts/footer.php';
    exit;
}
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-people-carry"></i>
            Gestión de Mozos
        </h3>
        <button onclick="abrirModalMozo()" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Mozo
        </button>
    </div>
    
    <div class="card-body">
        <!-- Filtros -->
        <div class="filters-grid">
            <div class="form-group">
                <input type="text" id="busquedaMozo" class="form-control" placeholder="Buscar por nombre o código...">
            </div>
            <div class="form-group">
                <select id="filtroActivo" class="form-control">
                    <option value="">Todos los estados</option>
                    <option value="1" selected>Activos</option>
                    <option value="0">Inactivos</option>
                </select>
            </div>
            <button onclick="cargarMozos()" class="btn btn-primary">
                <i class="fas fa-search"></i> Buscar
            </button>
            <button onclick="limpiarFiltros()" class="btn btn-secondary">
                <i class="fas fa-eraser"></i> Limpiar
            </button>
        </div>
        
        <!-- Tabla -->
        <div class="table-container">
            <table class="table" id="tablaMozos">
                <thead>
                    <tr>
                        <th>Nº Identificación</th>
                        <th>Nombre</th>
                        <th>Estado</th>
                        <th>Fecha Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbody-mozos">
                    <tr>
                        <td colspan="5" class="text-center">
                            <i class="fas fa-spinner fa-spin"></i> Cargando...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <div id="paginacion-mozos" class="pagination-container"></div>
    </div>
</div>

<!-- Modal Mozo -->
<div class="modal-overlay" id="modalMozo">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalMozoTitle">Nuevo Mozo</h3>
            <button class="modal-close" onclick="closeModal('modalMozo')">&times;</button>
        </div>
        <form id="formMozo" onsubmit="guardarMozo(event)">
            <div class="modal-body">
                <input type="hidden" id="mozo_id" name="id">
                
                <div class="form-group">
                    <label for="numero_identificacion_mozo">Nº Identificación <span class="text-danger">*</span></label>
                    <input type="text" id="numero_identificacion_mozo" name="numero_identificacion" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="nombre_mozo">Nombre <span class="text-danger">*</span></label>
                    <input type="text" id="nombre_mozo" name="nombre" class="form-control" required>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" id="activo_mozo" name="activo" class="form-check-input" checked>
                    <label for="activo_mozo" class="form-check-label">Activo</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalMozo')">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<script src="../assets/js/mozos.js"></script>
<?php include __DIR__ . '/layouts/footer.php'; ?>
