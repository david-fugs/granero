<?php
$pageName = 'Clientes';
$pageTitle = 'Gestión de Clientes';
include __DIR__ . '/layouts/header.php';

if (!hasPermission('clientes')) {
    echo '<div class="alert alert-danger">No tienes permisos para acceder a este módulo</div>';
    include __DIR__ . '/layouts/footer.php';
    exit;
}
?>

<!-- Filtros -->
<div class="filters-container">
    <div class="filters-grid">
        <div class="form-group mb-0">
            <label class="form-label">Buscar</label>
            <input type="text" id="searchInput" class="form-control" placeholder="Código, nombre o NIF...">
        </div>
        <div class="form-group mb-0">
            <label class="form-label">Estado</label>
            <select id="filterActivo" class="form-control">
                <option value="">Todos</option>
                <option value="1" selected>Activos</option>
                <option value="0">Inactivos</option>
            </select>
        </div>
        <div class="form-group mb-0">
            <label class="form-label">Paga Transporte</label>
            <select id="filterTransporte" class="form-control">
                <option value="">Todos</option>
                <option value="si">Sí</option>
                <option value="no">No</option>
            </select>
        </div>
        <div class="form-group mb-0">
            <label class="form-label">&nbsp;</label>
            <button onclick="filtrarClientes()" class="btn btn-primary btn-block">
                <i class="fas fa-search"></i> Buscar
            </button>
        </div>
    </div>
</div>

<!-- Card de Clientes -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-user-tie"></i>
            Listado de Clientes
        </h3>
        <div class="btn-group">
            <button onclick="openModal('modalCliente')" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Cliente
            </button>
            <button onclick="exportarExcel()" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Exportar
            </button>
        </div>
    </div>
    
    <div class="card-body p-0">
        <!-- Scroll superior -->
        <div class="table-scroll" id="scrollTop" style="overflow-x: auto; overflow-y: hidden;">
            <div style="height: 1px;" id="scrollTopContent"></div>
        </div>
        
        <!-- Tabla principal -->
        <div class="table-scroll" id="scrollMain" style="overflow-x: auto; max-height: 600px;">
            <table class="table" id="tablaClientes">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>NIF</th>
                        <th>Paga Transporte</th>
                        <th>Importe Riesgo</th>
                        <th>Deuda</th>
                        <th>Plat</th>
                        <th>Carga</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaClientesBody">
                    <tr>
                        <td colspan="10" class="text-center">
                            <div class="spinner spinner-large"></div>
                            Cargando...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="card-footer">
        <div class="pagination" id="paginacion"></div>
    </div>
</div>

<!-- Modal Crear/Editar Cliente -->
<div class="modal-overlay" id="modalCliente">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title" id="modalClienteTitle">Nuevo Cliente</h3>
            <button class="modal-close" onclick="closeModal('modalCliente')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="formCliente" onsubmit="guardarCliente(event)">
            <div class="modal-body">
                <input type="hidden" id="clienteId" name="id">
                
                <div class="form-group">
                    <label for="codigo_cliente" class="form-label required">Código Cliente</label>
                    <input type="text" id="codigo_cliente" name="codigo_cliente" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="nombre_cliente" class="form-label required">Nombre Cliente</label>
                    <input type="text" id="nombre_cliente" name="nombre_cliente" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="nif" class="form-label">NIF</label>
                    <input type="text" id="nif" name="nif" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="paga_transporte" class="form-label">Paga Transporte</label>
                    <select id="paga_transporte" name="paga_transporte" class="form-control">
                        <option value="no">No</option>
                        <option value="si">Sí</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="importe_riesgo" class="form-label">Importe Riesgo (€)</label>
                    <input type="number" step="0.01" id="importe_riesgo" name="importe_riesgo" class="form-control" value="0.00">
                </div>
                
                <div class="form-group">
                    <label for="deuda" class="form-label">Deuda (€)</label>
                    <input type="number" step="0.01" id="deuda" name="deuda" class="form-control" value="0.00">
                </div>
                
                <div class="form-group">
                    <label for="plat" class="form-label">Plat</label>
                    <input type="text" id="plat" name="plat" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="carga" class="form-label">Carga</label>
                    <input type="text" id="carga" name="carga" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="activo" class="form-label">Estado</label>
                    <select id="activo" name="activo" class="form-control">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalCliente')">
                    Cancelar
                </button>
                <button type="submit" class="btn btn-primary" id="btnGuardar">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$customJS = '<script src="../assets/js/clientes.js"></script>';
include __DIR__ . '/layouts/footer.php';
?>
