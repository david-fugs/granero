<?php
$pageName = 'Usuarios';
$pageTitle = 'Gestión de Usuarios';
include __DIR__ . '/layouts/header.php';

if (!hasPermission('usuarios')) {
    echo '<div class="alert alert-danger">No tienes permisos para acceder a este módulo</div>';
    include __DIR__ . '/layouts/footer.php';
    exit;
}
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-users"></i>
            Gestión de Usuarios
        </h3>
        <button onclick="abrirModalUsuario()" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Usuario
        </button>
    </div>
    
    <div class="card-body">
        <!-- Filtros -->
        <div class="filters-grid">
            <div class="form-group">
                <input type="text" id="busquedaUsuario" class="form-control" placeholder="Buscar por nombre o email...">
            </div>
            <div class="form-group">
                <select id="filtroTipo" class="form-control">
                    <option value="">Todos los tipos</option>
                    <option value="admin">Admin</option>
                    <option value="vendedor">Vendedor</option>
                    <option value="almacen">Almacén</option>
                    <option value="visualizador">Visualizador</option>
                </select>
            </div>
            <div class="form-group">
                <select id="filtroActivo" class="form-control">
                    <option value="">Todos los estados</option>
                    <option value="1" selected>Activos</option>
                    <option value="0">Inactivos</option>
                </select>
            </div>
            <button onclick="cargarUsuarios()" class="btn btn-primary">
                <i class="fas fa-search"></i> Buscar
            </button>
            <button onclick="limpiarFiltros()" class="btn btn-secondary">
                <i class="fas fa-eraser"></i> Limpiar
            </button>
        </div>
        
        <!-- Tabla -->
        <div class="table-container">
            <table class="table" id="tablaUsuarios">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Fecha Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbody-usuarios">
                    <tr>
                        <td colspan="6" class="text-center">
                            <i class="fas fa-spinner fa-spin"></i> Cargando...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <div id="paginacion-usuarios" class="pagination-container"></div>
    </div>
</div>

<!-- Modal Usuario -->
<div class="modal" id="modalUsuario">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalUsuarioTitle">Nuevo Usuario</h3>
            <button class="modal-close" onclick="closeModal('modalUsuario')">&times;</button>
        </div>
        <form id="formUsuario" onsubmit="guardarUsuario(event)">
            <div class="modal-body">
                <input type="hidden" id="usuario_id" name="id">
                
                <div class="form-group">
                    <label for="nombre">Nombre <span class="text-danger">*</span></label>
                    <input type="text" id="nombre" name="nombre" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email <span class="text-danger">*</span></label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña <span class="text-danger" id="password-required">*</span></label>
                    <input type="password" id="password" name="password" class="form-control">
                    <small class="text-muted">Dejar en blanco para mantener la contraseña actual (solo al editar)</small>
                </div>
                
                <div class="form-group">
                    <label for="tipo_usuario">Tipo de Usuario <span class="text-danger">*</span></label>
                    <select id="tipo_usuario" name="tipo_usuario" class="form-control" required>
                        <option value="visualizador">Visualizador</option>
                        <option value="almacen">Almacén</option>
                        <option value="vendedor">Vendedor</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" id="activo" name="activo" class="form-check-input" checked>
                    <label for="activo" class="form-check-label">Activo</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalUsuario')">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<script src="../assets/js/usuarios.js"></script>
<?php include __DIR__ . '/layouts/footer.php'; ?>
