<?php
function connection() {
    // Configuración de la base de datos
    $host = 'localhost'; // Cambia esto por tu host si es diferente
    $dbname = 'eshop_pps'; // Cambia esto por el nombre de tu base de datos
    $username = 'root'; // Cambia esto por tu nombre de usuario de la base de datos
    $password = ''; // Cambia esto por tu contraseña de la base de datos

    try {
        // Conexión a la base de datos utilizando PDO
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

        // Configuración de PDO para mostrar errores
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Si llegas a este punto, la conexión fue exitosa
        echo "";
        return $conn; // Devuelve la conexión para su uso posterior
    } catch(PDOException $e) {
        // Si hay algún error en la conexión, se captura y muestra el mensaje de error
        echo "Error en la conexión: " . $e->getMessage();
        return null; // Devuelve null si hay un error en la conexión
    }
}

function consulta($conn) {
    $consultaSQL = "SELECT * FROM pps_products";
    try {
        // Preparar y ejecutar la consulta utilizando PDO
        $stmt = $conn->query($consultaSQL);

        // Devolver los resultados como un array asociativo
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo 'No se ha podido realizar la consulta: ' . $e->getMessage();
        return null;
    }
}

function consulta_tabla($conn, $tabla) {
    $consultaSQL = "SELECT * FROM $tabla";
    try {
        // Preparar y ejecutar la consulta utilizando PDO
        $stmt = $conn->query($consultaSQL);

        // Devolver el resultado
        return $stmt;
    } catch (PDOException $e) {
        echo 'No se ha podido realizar la consulta: ' . $e->getMessage();
        return null;
    }
}


function mostrar_tabla($result) {
  echo "<table border=1><tr><th colspan=10 id='tablaproductos'>Tabla Productos</th></tr>
  <tr><th>ID</th><th>Nombre</th><th>Categoría</th><th>Detalles</th><th>Precio</th><th>Cantidad en Tienda</th><th>Stock</th><th>Disponibilidad</th><th>Editar</th><th>Eliminar</th></tr>";
  foreach ($result as $row) {
      echo "<tr>";
      echo "<td>", $row['prd_id'], "</td>";
      echo "<td>", $row['prd_name'], "</td>";
      echo "<td>", $row['prd_category'], "</td>";
      echo "<td>", $row['prd_details'], "</td>";
      echo "<td>", $row['prd_price'], "</td>";
      echo "<td>", $row['prd_quantity_shop'], "</td>";
      echo "<td>", $row['prd_stock'], "</td>";
      if ($row['prd_stock'] && $row['prd_quantity_shop'] > 0) {
          echo "<td>Disponible</td>";
      } else {
          echo "<td>No disponible</td>";
      }
      // Botón de Edición
      echo "<td><button onclick=\"window.location.href='editar.php?id=" . $row['prd_id'] . "'\" id='editar'>Editar</button></td>";
      // Botón de Eliminación con confirmación
      echo "<td><button onclick='eliminarFila(" . $row['prd_id'] . ")'>Eliminar</button></td>";
      
      echo "</tr>\n";
  }
  echo "</table><br>";

  // Script JavaScript para eliminar fila
  echo "<script>
          function eliminarFila(id) {
              if (confirm('¿Estás seguro de que quieres eliminar este producto?')) {
                  window.location.href = 'mainpage.php?eliminar_id=' + id;
              }
          }
        </script>";
}


function eliminar_fila($conn, $id) {
  $id = htmlspecialchars($id); // Para evitar inyección SQL

  $consultaSQL = "DELETE FROM pps_products WHERE prd_id = :id";
  try {
      // Preparar y ejecutar la consulta utilizando PDO con marcadores de posición
      $stmt = $conn->prepare($consultaSQL);
      $stmt->bindParam(':id', $id);
      $stmt->execute();

      // Verificar si se eliminó alguna fila
      if ($stmt->rowCount() > 0) {
          return true; // Éxito al eliminar
      } else {
          return false; // No se eliminó ninguna fila
      }
  } catch (PDOException $e) {
      echo 'No se ha podido eliminar la fila: ' . $e->getMessage();
      return false; // Error al eliminar
  }
}

// Lógica para eliminar la fila si se recibe un ID de eliminación
if (isset($_GET['eliminar_id'])) {
  $conn = connection();
  $id = $_GET['eliminar_id'];
  if (eliminar_fila($conn, $id)) {
      echo "<script>alert('Producto eliminado correctamente');</script>";
  } else {
      echo "<script>alert('Error al eliminar el producto');</script>";
  }
  cerrar_conexion($conn);
}



function cerrar_conexion($conn) {
    // Cerrar la conexión PDO
    $conn = null;
}

?>
