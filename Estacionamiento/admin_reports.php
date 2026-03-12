<?php
session_start();
require_once 'config.php';
require_once 'includes/functions.php';

check_session();
if (!is_admin()) {
    redirect('dashboard.php');
}

$page_title = 'Reportes Administrativos';

// Función para obtener reportes
function get_report_data($conn, $type) {
    switch ($type) {
        case 'accesos_diarios':
            $stmt = $conn->prepare("
                SELECT DATE(fecha_hora) as dia, 
                       COUNT(*) as total_accesos,
                       SUM(CASE WHEN tipo_movimiento = 'entrada' THEN 1 ELSE 0 END) as entradas,
                       SUM(CASE WHEN tipo_movimiento = 'salida' THEN 1 ELSE 0 END) as salidas
                FROM historial_accesos
                WHERE fecha_hora >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(fecha_hora)
                ORDER BY dia DESC
                LIMIT 30
            ");
            break;
        
        case 'vehiculos_por_tipo':
            $stmt = $conn->prepare("
                SELECT tipo, COUNT(*) as total
                FROM vehiculos
                WHERE estado_registro = 'activo'
                GROUP BY tipo
            ");
            break;

        case 'infracciones_por_tipo':
            $stmt = $conn->prepare("
                SELECT tipo, COUNT(*) as total
                FROM infracciones
                GROUP BY tipo
            ");
            break;

        case 'espacios_ocupados':
            $stmt = $conn->prepare("
                SELECT e.tipo, COUNT(*) as total
                FROM espacios e
                WHERE e.disponibilidad = 'ocupado'
                GROUP BY e.tipo
            ");
            break;

        default:
            return [];
    }
    
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <h1 class="mb-4"><i class="fas fa-chart-bar me-2"></i>Reportes Administrativos</h1>

    <div class="row">
        <!-- Reporte: Accesos Diarios -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Accesos Diarios (Últimos 30 días)</h5>
                </div>
                <div class="card-body">
                    <canvas id="accesosChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Reporte: Vehículos por Tipo -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-car me-2"></i>Vehículos por Tipo</h5>
                </div>
                <div class="card-body">
                    <canvas id="vehiculosChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Reporte: Infracciones -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Infracciones por Tipo</h5>
                </div>
                <div class="card-body">
                    <canvas id="infraccionesChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Reporte: Espacios Ocupados -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-parking me-2"></i>Espacios Ocupados por Tipo</h5>
                </div>
                <div class="card-body">
                    <canvas id="espaciosChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <a href="admin_dashboard.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Volver al Panel de Administrador</a>
        <a href="admin_reports.php?export=csv" class="btn btn-outline-primary"><i class="fas fa-file-csv me-2"></i>Exportar a CSV</i></a>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Datos para los gráficos
        const accesosData = <?php echo json_encode(get_report_data($conn, 'accesos_diarios')); ?>;
        const vehiculosData = <?php echo json_encode(get_report_data($conn, 'vehiculos_por_tipo')); ?>;
        const infraccionesData = <?php echo json_encode(get_report_data($conn, 'infracciones_por_tipo')); ?>;
        const espaciosData = <?php echo json_encode(get_report_data($conn, 'espacios_ocupados')); ?>;

        // Gráfico de accesos diarios
        if (accesosData.length > 0) {
            const ctx1 = document.getElementById('accesosChart').getContext('2d');
            new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: accesosData.map(d => d.dia),
                    datasets: [{
                        label: 'Accesos Totales',
                        data: accesosData.map(d => d.total_accesos),
                        borderColor: '#17a2b8',
                        backgroundColor: 'rgba(23, 162, 184, 0.1)',
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true }
                    },
                    plugins: {
                        legend: { display: true }
                    }
                }
            });
        }

        // Gráfico de vehículos por tipo
        if (vehiculosData.length > 0) {
            const ctx2 = document.getElementById('vehiculosChart').getContext('2d');
            new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: vehiculosData.map(d => d.tipo === 'auto' ? 'Autos' : 'Motos'),
                    datasets: [{
                        label: 'Cantidad',
                        data: vehiculosData.map(d => d.total),
                        backgroundColor: ['#28a745', '#ffc107']
                    }]
                },
                options: {
                    responsive: true,
                    scales: { y: { beginAtZero: true } }
                }
            });
        }

        // Gráfico de infracciones
        if (infraccionesData.length > 0) {
            const ctx3 = document.getElementById('infraccionesChart').getContext('2d');
            new Chart(ctx3, {
                type: 'pie',
                data: {
                    labels: infraccionesData.map(d => d.tipo),
                    datasets: [{
                        data: infraccionesData.map(d => d.total),
                        backgroundColor: ['#ffc107', '#dc3545', '#6f42c1']
                    }]
                }
            });
        }

        // Gráfico de espacios ocupados
        if (espaciosData.length > 0) {
            const ctx4 = document.getElementById('espaciosChart').getContext('2d');
            new Chart(ctx4, {
                type: 'doughnut',
                data: {
                    labels: espaciosData.map(d => {
                        switch(d.tipo) {
                            case 'normal': return 'Normales';
                            case 'moto': return 'Motos';
                            case 'discapacidad': return 'Discapacidad';
                            default: return d.tipo;
                        }
                    }),
                    datasets: [{
                        data: espaciosData.map(d => d.total),
                        backgroundColor: ['#007bff', '#28a745', '#dc3545']
                    }]
                }
            });
        }
    </script>
</body>
</html>