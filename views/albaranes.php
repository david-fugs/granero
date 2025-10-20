<?php
$pageName = 'Albaranes';
$pageTitle = 'Gestión de Albaranes';
include __DIR__ . '/layouts/header.php';

if (!hasPermission('albaranes')) {
    echo '<div class="alert alert-danger">No tienes permisos para acceder a este módulo</div>';
    include __DIR__ . '/layouts/footer.php';
    exit;
}
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-file-invoice"></i>
            Gestión de Albaranes
        </h3>
        <button onclick="abrirModalAlbaran()" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Albarán
        </button>
    </div>
    
    <div class="card-body">
        <!-- Tabs de Estados -->
        <div class="tabs-estados" id="tabsEstados">
            <button class="tab-estado active" data-estado="pendiente" onclick="cambiarEstadoAlbaran('pendiente')">
                <i class="fas fa-clock"></i> Pendientes <span class="badge" id="count-pendiente">0</span>
            </button>
            <button class="tab-estado" data-estado="en_preparacion" onclick="cambiarEstadoAlbaran('en_preparacion')">
                <i class="fas fa-tasks"></i> En Preparación <span class="badge" id="count-en_preparacion">0</span>
            </button>
            <button class="tab-estado" data-estado="pendiente_facturar" onclick="cambiarEstadoAlbaran('pendiente_facturar')">
                <i class="fas fa-file-invoice-dollar"></i> Pdte. Facturar <span class="badge" id="count-pendiente_facturar">0</span>
            </button>
            <button class="tab-estado" data-estado="facturado" onclick="cambiarEstadoAlbaran('facturado')">
                <i class="fas fa-check-circle"></i> Facturados <span class="badge" id="count-facturado">0</span>
            </button>
            <button class="tab-estado" data-estado="pendiente_precio" onclick="cambiarEstadoAlbaran('pendiente_precio')">
                <i class="fas fa-exclamation-circle"></i> Pdte. Precio <span class="badge" id="count-pendiente_precio">0</span>
            </button>
        </div>
        
        <!-- Filtros -->
        <div class="filters-grid" style="margin-top: 1.5rem;">
            <div class="form-group">
                <input type="text" id="busquedaAlbaran" class="form-control" placeholder="Buscar por número...">
            </div>
            <div class="form-group">
                <select id="filtroCliente" class="form-control">
                    <option value="">Todos los clientes</option>
                </select>
            </div>
            <button onclick="cargarAlbaranes()" class="btn btn-primary">
                <i class="fas fa-search"></i> Buscar
            </button>
            <button onclick="limpiarFiltros()" class="btn btn-secondary">
                <i class="fas fa-eraser"></i> Limpiar
            </button>
        </div>
        
        <!-- Tabla -->
        <div class="table-container">
            <table class="table" id="tablaAlbaranes">
                <thead>
                    <tr>
                        <th>Nº Albarán</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Comercial</th>
                        <th>Mozo</th>
                        <th>Total Peso</th>
                        <th>Total €</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tbody-albaranes">
                    <tr>
                        <td colspan="8" class="text-center">
                            <i class="fas fa-spinner fa-spin"></i> Cargando...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <div id="paginacion-albaranes" class="pagination-container"></div>
    </div>
</div>

<!-- Modal Albarán -->
<div class="modal" id="modalAlbaran">
    <div class="modal-content modal-xl">
        <div class="modal-header">
            <h3 id="modalAlbaranTitle">Nuevo Albarán</h3>
            <button class="modal-close" onclick="closeModal('modalAlbaran')">&times;</button>
        </div>
        <form id="formAlbaran" onsubmit="guardarAlbaran(event)">
            <input type="hidden" id="albaran_id" name="id">
            
            <div class="modal-body">
                <!-- Datos principales del albarán -->
                <div class="card" style="margin-bottom: 1.5rem;">
                    <div class="card-header" style="background: #f9fafb; padding: 0.75rem 1rem;">
                        <h4 style="margin: 0; font-size: 1rem;">📋 Datos del Albarán</h4>
                    </div>
                    <div class="card-body" style="padding: 1rem;">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="numero_albaran">Nº Albarán <span class="text-danger">*</span></label>
                                <div style="display: flex; gap: 10px;">
                                    <input type="text" id="numero_albaran" name="numero_albaran" class="form-control" required placeholder="ALB-000001">
                                    <button type="button" onclick="generarNumeroAlbaran()" class="btn btn-secondary" title="Generar">
                                        <i class="fas fa-magic"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="fecha_albaran">Fecha <span class="text-danger">*</span></label>
                                <input type="date" id="fecha_albaran" name="fecha" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="cliente_id_alb">Cliente <span class="text-danger">*</span></label>
                                <select id="cliente_id_alb" name="cliente_id" class="form-control" required>
                                    <option value="">Seleccione...</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="comercial_id_alb">Comercial</label>
                                <select id="comercial_id_alb" name="comercial_id" class="form-control">
                                    <option value="">Seleccione...</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="mozo_id_alb">Mozo</label>
                                <select id="mozo_id_alb" name="mozo_id" class="form-control">
                                    <option value="">Seleccione...</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="estado_alb">Estado</label>
                                <select id="estado_alb" name="estado" class="form-control">
                                    <option value="pendiente">⏳ Pendiente</option>
                                    <option value="en_preparacion">📦 En Preparación</option>
                                    <option value="pendiente_facturar">💰 Pendiente Facturar</option>
                                    <option value="facturado">✅ Facturado</option>
                                    <option value="pendiente_precio">⚠️ Pendiente Precio</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group" style="display: flex; gap: 20px; align-items: center;">
                                <label class="checkbox-label">
                                    <input type="checkbox" id="faltan_precios_alb" name="faltan_precios">
                                    Faltan Precios
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" id="preparado_alb" name="preparado">
                                    Preparado
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" id="unificado_alb" name="unificado">
                                    Unificado
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" id="prepago_alb" name="prepago">
                                    Prepago
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Líneas de artículos -->
                <div class="card">
                    <div class="card-header" style="background: #f9fafb; padding: 0.75rem 1rem; display: flex; justify-content: space-between; align-items: center;">
                        <h4 style="margin: 0; font-size: 1rem;">🍎 Artículos del Albarán</h4>
                        <button type="button" onclick="agregarLineaArticulo()" class="btn btn-sm btn-success" id="btnAgregarLinea" disabled>
                            <i class="fas fa-plus"></i> Agregar Artículo
                        </button>
                    </div>
                    <div class="card-body" style="padding: 1rem;">
                        <div class="table-container" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-sm" id="tablaLineas">
                                <thead>
                                    <tr>
                                        <th style="width: 250px;">Artículo</th>
                                        <th style="width: 120px;">Partida</th>
                                        <th style="width: 80px;">Uds.</th>
                                        <th style="width: 100px;">Peso (kg)</th>
                                        <th style="width: 100px;">Precio</th>
                                        <th style="width: 100px;">Transporte</th>
                                        <th style="width: 120px;">Importe</th>
                                        <th style="width: 80px;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-lineas">
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">
                                            <i class="fas fa-info-circle"></i> Guarde primero el albarán para agregar artículos
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Totales -->
                        <div style="margin-top: 1rem; padding: 1rem; background: #f9fafb; border-radius: 4px; display: flex; justify-content: flex-end; gap: 2rem;">
                            <div>
                                <strong>Total Peso:</strong> <span id="total_peso_display">0.00</span> kg
                            </div>
                            <div>
                                <strong>Total General:</strong> <span id="total_general_display">0.00</span> €
                            </div>
                        </div>
                        
                        <input type="hidden" id="total_peso" name="total_peso">
                        <input type="hidden" id="total_general" name="total_general">
                    </div>
                </div>
                
                <div class="form-group" style="margin-top: 1rem;">
                    <label for="observaciones_alb">Observaciones</label>
                    <textarea id="observaciones_alb" name="observaciones" class="form-control" rows="2"></textarea>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalAlbaran')">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" onclick="imprimirAlbaranPDF()" class="btn btn-danger" id="btnPDFAlbaran" style="display: none;">
                    <i class="fas fa-file-pdf"></i> PDF
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Línea de Artículo -->
<div class="modal" id="modalLineaArticulo">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalLineaTitle">Agregar Artículo</h3>
            <button class="modal-close" onclick="closeModal('modalLineaArticulo')">&times;</button>
        </div>
        <form id="formLinea" onsubmit="guardarLinea(event)">
            <input type="hidden" id="linea_id" name="linea_id">
            <input type="hidden" id="linea_albaran_id" name="albaran_id">
            
            <div class="modal-body">
                <div class="form-group">
                    <label for="articulo_id_linea">Artículo <span class="text-danger">*</span></label>
                    <select id="articulo_id_linea" name="articulo_id" class="form-control" required>
                        <option value="">Seleccione un artículo...</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="partida_linea">Partida</label>
                    <input type="text" id="partida_linea" name="partida" class="form-control" placeholder="PART-001">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="unidades_linea">Unidades</label>
                        <input type="number" step="0.01" id="unidades_linea" name="unidades" class="form-control" value="0" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="peso_linea">Peso (kg) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" id="peso_linea" name="peso" class="form-control" required min="0.01" onchange="calcularImporteLinea()">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="precio_linea">Precio (€/kg)</label>
                        <input type="number" step="0.01" id="precio_linea" name="precio" class="form-control" value="0" min="0" onchange="calcularImporteLinea()">
                    </div>
                    
                    <div class="form-group">
                        <label for="importe_transporte_linea">Transporte (€)</label>
                        <input type="number" step="0.01" id="importe_transporte_linea" name="importe_transporte" class="form-control" value="0" min="0" onchange="calcularImporteLinea()">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Importe Total</label>
                    <input type="text" id="importe_linea_display" class="form-control" readonly style="background: #f9fafb; font-weight: bold; font-size: 1.1rem;">
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalLineaArticulo')">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.tabs-estados {
    display: flex;
    gap: 0.5rem;
    border-bottom: 2px solid #e5e7eb;
    padding-bottom: 0.5rem;
    flex-wrap: wrap;
}

.tab-estado {
    padding: 0.5rem 1rem;
    border: 1px solid #d1d5db;
    background: white;
    border-radius: 6px 6px 0 0;
    cursor: pointer;
    transition: all 0.3s;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.tab-estado:hover {
    background: #f3f4f6;
}

.tab-estado.active {
    background: #2563eb;
    color: white;
    border-color: #2563eb;
    border-bottom: 2px solid #2563eb;
    margin-bottom: -2px;
}

.tab-estado .badge {
    background: rgba(255, 255, 255, 0.3);
    padding: 0.2rem 0.5rem;
    border-radius: 10px;
    font-size: 0.75rem;
}

.tab-estado.active .badge {
    background: rgba(255, 255, 255, 0.9);
    color: #2563eb;
    font-weight: bold;
}

.modal-xl {
    max-width: 95%;
    width: 1200px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    margin: 0;
}

.checkbox-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}
</style>

<script src="../assets/js/albaranes.js"></script>
<?php include __DIR__ . '/layouts/footer.php'; ?>
                        <td colspan="8" class="text-center">Próximamente...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="../assets/js/albaranes.js"></script>
<?php include __DIR__ . '/layouts/footer.php'; ?>
