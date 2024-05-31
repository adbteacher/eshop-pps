<?php

require_once '../autoload.php';

function GetDatabaseConnection(): PDO {
    static $Connection;
    if ($Connection === null) {
        $Connection = database::LoadDatabase();
    }
    return $Connection;
}

function consulta(): array {
    $conn = GetDatabaseConnection();
    $consultaSQL = "SELECT * FROM pps_products";
    try {
        $stmt = $conn->query($consultaSQL);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error en consulta: ' . $e->getMessage());
        return [];
    }
}

function mostrar_tabla(array $result): void {
    echo "<table><tr><th colspan=10 id='tablaproductos'>Tabla Productos</th></tr>
    <tr><th>ID</th><th>Nombre</th><th>Categoría</th><th>Detalles</th><th>Precio</th><th>Cantidad en Tienda</th><th>Stock</th><th>Disponibilidad</th><th>Editar</th><th>Eliminar</th></tr>";
    foreach ($result as $row) {
        echo "<tr>";
        echo "<td>", htmlspecialchars($row['prd_id']), "</td>";
        echo "<td>", htmlspecialchars($row['prd_name']), "</td>";
        echo "<td>", htmlspecialchars($row['prd_category']), "</td>";
        echo "<td>", htmlspecialchars($row['prd_details']), "</td>";
        echo "<td>", htmlspecialchars($row['prd_price']), "</td>";
        echo "<td>", htmlspecialchars($row['prd_quantity_shop']), "</td>";
        echo "<td>", htmlspecialchars($row['prd_stock']), "</td>";
        echo "<td>", $row['prd_stock'] && $row['prd_quantity_shop'] > 0 ? "Disponible" : "No disponible", "</td>";
        echo "<td><a href='editar.php?id=" . htmlspecialchars($row['prd_id']) . "'>Editar</a></td>";
        echo "<td><a href='mainpage.php?eliminar_id=" . htmlspecialchars($row['prd_id']) . "'>Eliminar</a></td>";
        echo "</tr>";
    }
    echo "</table><br>";
}

function eliminar_fila(int $id): bool {
    $conn = GetDatabaseConnection();
    $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
    $consultaSQL = "DELETE FROM pps_products WHERE prd_id = :id";
    try {
        $stmt = $conn->prepare($consultaSQL);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log('Error al eliminar la fila: ' . $e->getMessage());
        return false;
    }
}

function cerrar_conexion(): void {
    // PDO cierra la conexión automáticamente al finalizar el script
}

if (isset($_GET['eliminar_id'])) {
    $id = filter_var($_GET['eliminar_id'], FILTER_SANITIZE_NUMBER_INT);
    if (eliminar_fila($id)) {
        echo "<script>alert('Error al eliminar el producto');</script>";
    } else {
        echo "<script>alert('Producto eliminado correctamente');</script>";
    }
}
?>
