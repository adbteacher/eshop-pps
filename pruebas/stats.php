<!DOCTYPE html>
<html>
<head>
  <title>Estadísticas de Ventas</title>
  <link rel="stylesheet" href="estilos/style.css?v=0.0.1">
  <style>
    .estadisticas {
      margin: 20px;
    }
    .estadisticas h2 {
      margin-bottom: 20px;
    }
    .estadisticas table {
      width: 100%;
      border-collapse: collapse;
    }
    .estadisticas th, .estadisticas td {
      border: 1px solid #ddd;
      padding: 8px;
    }
    .estadisticas th {
      background-color: #f2f2f2;
      text-align: left;
    }
    .grafico {
      width: 80%;
      margin: 0 auto;
    }
  </style>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <div class="estadisticas">
    <h1>Estadísticas de Ventas</h1>
    <?php
    include "biblioteca.php";
    $conn = connection();

    // Número de ventas e ingresos generados
    $ventas_totales_sql = "SELECT COUNT(*) AS total_ventas, SUM(subtotal) AS ingresos_totales FROM pps_order_details";
    $stmt = $conn->query($ventas_totales_sql);
    $ventas_totales = $stmt->fetch(PDO::FETCH_ASSOC);

    // Productos más vendidos
    $productos_mas_vendidos_sql = "SELECT p.prd_name, SUM(od.qty) AS cantidad_vendida 
                                   FROM pps_order_details od 
                                   JOIN pps_products p ON od.ord_det_prod_id = p.prd_id 
                                   GROUP BY p.prd_name 
                                   ORDER BY cantidad_vendida DESC 
                                   LIMIT 5";
    $stmt = $conn->query($productos_mas_vendidos_sql);
    $productos_mas_vendidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Tendencias de ventas a lo largo del tiempo
    $tendencias_ventas_sql = "SELECT DATE_FORMAT(o.ord_purchase_date, '%Y-%m') AS mes, SUM(od.subtotal) AS ingresos_mensuales 
                              FROM pps_order_details od 
                              JOIN pps_orders o ON od.ord_det_order_id = o.ord_id 
                              GROUP BY mes 
                              ORDER BY mes";
    $stmt = $conn->query($tendencias_ventas_sql);
    $tendencias_ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    cerrar_conexion($conn);
    ?>
    <h2>Resumen de Ventas</h2>
    <p>Número total de ventas: <?php echo $ventas_totales['total_ventas']; ?></p>
    <p>Ingresos totales generados: $<?php echo number_format($ventas_totales['ingresos_totales'], 2); ?></p>

    <h2>Productos Más Vendidos</h2>
    <table>
      <tr>
        <th>Producto</th>
        <th>Cantidad Vendida</th>
      </tr>
      <?php foreach ($productos_mas_vendidos as $producto) : ?>
        <tr>
          <td><?php echo htmlspecialchars($producto['prd_name']); ?></td>
          <td><?php echo $producto['cantidad_vendida']; ?></td>
        </tr>
      <?php endforeach; ?>
    </table>

    <h2>Tendencias de Ventas Mensuales</h2>
    <div class="grafico">
      <canvas id="ventasMensuales"></canvas>
    </div>
    <script>
      const ctx = document.getElementById('ventasMensuales').getContext('2d');
      const ventasMensuales = new Chart(ctx, {
        type: 'line',
        data: {
          labels: <?php echo json_encode(array_column($tendencias_ventas, 'mes')); ?>,
          datasets: [{
            label: 'Ingresos Mensuales',
            data: <?php echo json_encode(array_column($tendencias_ventas, 'ingresos_mensuales')); ?>,
            borderColor: 'rgba(75, 192, 192, 1)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            fill: true,
          }]
        },
        options: {
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });
    </script>
  </div>
</body>
</html>
