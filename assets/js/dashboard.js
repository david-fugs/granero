/**
 * Dashboard JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    cargarEstadisticas();
    cargarUltimasReservas();
    cargarUltimosAlbaranes();
    cargarStockBajo();
    
    // Actualizar cada 5 minutos
    setInterval(cargarEstadisticas, 300000);
});

async function cargarEstadisticas() {
    try {
        const response = await fetch('../controllers/DashboardController.php?action=estadisticas');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('totalClientes').textContent = data.stats.totalClientes || 0;
            document.getElementById('totalArticulos').textContent = data.stats.totalArticulos || 0;
            document.getElementById('reservasPendientes').textContent = data.stats.reservasPendientes || 0;
            document.getElementById('albaranesPendientes').textContent = data.stats.albaranesPendientes || 0;
        }
    } catch (error) {
        console.error('Error al cargar estadísticas:', error);
    }
}

async function cargarUltimasReservas() {
    try {
        const response = await fetch('../controllers/DashboardController.php?action=ultimasReservas');
        const data = await response.json();
        
        if (data.success && data.reservas.length > 0) {
            let html = '';
            data.reservas.forEach(reserva => {
                const estadoBadge = reserva.estado === 'reservado' 
                    ? '<span class="badge badge-warning">Reservado</span>'
                    : '<span class="badge badge-success">Enviado</span>';
                
                html += `
                    <tr>
                        <td>${reserva.numero_reserva}</td>
                        <td>${reserva.nombre_cliente}</td>
                        <td>${formatDate(reserva.fecha)}</td>
                        <td>${estadoBadge}</td>
                    </tr>
                `;
            });
            document.getElementById('ultimasReservas').innerHTML = html;
        } else {
            document.getElementById('ultimasReservas').innerHTML = `
                <tr>
                    <td colspan="4" class="text-center text-muted">No hay reservas recientes</td>
                </tr>
            `;
        }
    } catch (error) {
        console.error('Error al cargar reservas:', error);
    }
}

async function cargarUltimosAlbaranes() {
    try {
        const response = await fetch('../controllers/DashboardController.php?action=ultimosAlbaranes');
        const data = await response.json();
        
        if (data.success && data.albaranes.length > 0) {
            let html = '';
            data.albaranes.forEach(albaran => {
                let estadoBadge = '';
                switch(albaran.estado) {
                    case 'pendiente':
                        estadoBadge = '<span class="badge badge-secondary">Pendiente</span>';
                        break;
                    case 'en_preparacion':
                        estadoBadge = '<span class="badge badge-info">En Preparación</span>';
                        break;
                    case 'pendiente_facturar':
                        estadoBadge = '<span class="badge badge-warning">Pdte. Facturar</span>';
                        break;
                    case 'facturado':
                        estadoBadge = '<span class="badge badge-success">Facturado</span>';
                        break;
                    case 'pendiente_precio':
                        estadoBadge = '<span class="badge badge-danger">Pdte. Precio</span>';
                        break;
                }
                
                html += `
                    <tr>
                        <td>${albaran.numero_albaran}</td>
                        <td>${albaran.nombre_cliente}</td>
                        <td>${formatDate(albaran.fecha)}</td>
                        <td>${estadoBadge}</td>
                    </tr>
                `;
            });
            document.getElementById('ultimosAlbaranes').innerHTML = html;
        } else {
            document.getElementById('ultimosAlbaranes').innerHTML = `
                <tr>
                    <td colspan="4" class="text-center text-muted">No hay albaranes recientes</td>
                </tr>
            `;
        }
    } catch (error) {
        console.error('Error al cargar albaranes:', error);
    }
}

async function cargarStockBajo() {
    try {
        const response = await fetch('../controllers/DashboardController.php?action=stockBajo');
        const data = await response.json();
        
        if (data.success && data.articulos.length > 0) {
            let html = '';
            data.articulos.forEach(articulo => {
                const stockTotal = parseFloat(articulo.stock_disponible) - 
                                  parseFloat(articulo.cantidad_albaranes) - 
                                  parseFloat(articulo.reservado);
                
                let estadoBadge = '';
                if (stockTotal <= 0) {
                    estadoBadge = '<span class="badge badge-danger">Sin Stock</span>';
                } else if (stockTotal < 50) {
                    estadoBadge = '<span class="badge badge-warning">Stock Bajo</span>';
                } else {
                    estadoBadge = '<span class="badge badge-success">Normal</span>';
                }
                
                html += `
                    <tr>
                        <td>${articulo.nombre_articulo}</td>
                        <td>${Number(articulo.stock_disponible).toFixed(2)}</td>
                        <td>${Number(articulo.reservado).toFixed(2)}</td>
                        <td>${stockTotal.toFixed(2)}</td>
                        <td>${estadoBadge}</td>
                    </tr>
                `;
            });
            document.getElementById('stockBajo').innerHTML = html;
        } else {
            document.getElementById('stockBajo').innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-muted">Todos los artículos tienen stock adecuado</td>
                </tr>
            `;
        }
    } catch (error) {
        console.error('Error al cargar stock bajo:', error);
    }
}
