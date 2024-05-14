<!DOCTYPE html>
<html>
<head>
  <title>Añadir un nuevo producto</title>
  <link rel="stylesheet" href="estilos/style.css">
  <style>
    .input-group {
      display: flex;
      align-items: center;
    }
    .input-group label {
      margin-right: 10px;
    }
    .mensaje_exito {
      background-color: #4CAF50;
      color: white;
      padding: 10px;
      margin-bottom: 10px;
      width: fit-content;
    }
  </style>
</head>
<body>
  <h1>Añadir un nuevo producto</h1>

  <form action="nuevo_producto.php" method="post" enctype="multipart/form-data">
    <div class="input-group">
      <label for="prd_name">Nombre:</label>
      <input type="text" name="prd_name" value="<?php if(!empty($_POST['prd_name'])){echo htmlspecialchars($_POST['prd_name']);}?>">
      <?php if (isset($_POST['add_prd']) && empty($_POST['prd_name'])) echo "<span style='color:red'>Campo requerido</span>"?>
    </div>

    <div class="input-group">
      <label for="prd_category">Categoría:</label>
      <select name="prd_category">
        <option value="0" <?php if(!empty($_POST['prd_category']) && $_POST['prd_category'] == '0') echo 'selected'; ?>> </option>
        <option value="1" <?php if(!empty($_POST['prd_category']) && $_POST['prd_category'] == '1') echo 'selected'; ?>>Fruta</option>
        <option value="2" <?php if(!empty($_POST['prd_category']) && $_POST['prd_category'] == '2') echo 'selected'; ?>>Verdura</option>
      </select>
      <?php if (isset($_POST['add_prd']) && empty($_POST['prd_category'])) echo "<span style='color:red'>Campo requerido</span>"?>
    </div>

    <div class="input-group">
      <label for="prd_details">Detalles:</label>
      <input type="text" name="prd_details" value="<?php if(!empty($_POST['prd_details'])){echo htmlspecialchars($_POST['prd_details']);}?>">
      <?php if (isset($_POST['add_prd']) && empty($_POST['prd_details'])) echo "<span style='color:red'>Campo requerido</span>"?>
    </div>

    <div class="input-group">
      <label for="prd_price">Precio:</label>
      <input type="number" name="prd_price" min="1" max="9999" value="<?php if(!empty($_POST['prd_price'])){echo $_POST['prd_price'];}?>">
      <?php if (isset($_POST['add_prd']) && empty($_POST['prd_price'])) echo "<span style='color:red'>Campo requerido</span>"?>
    </div>

    <div class="input-group">
      <label for="prd_quantity_shop">Cantidad en tienda:</label>
      <input type="number" name="prd_quantity_shop" min="1" max="9999" value="<?php if(!empty($_POST['prd_quantity_shop'])){echo $_POST['prd_quantity_shop'];}?>">
      <?php if (isset($_POST['add_prd']) && empty($_POST['prd_quantity_shop'])) echo "<span style='color:red'>Campo requerido</span>"?>
    </div>

    <div class="input-group">
      <label for="prd_stock">Stock:</label>
      <input type="number" name="prd_stock" min="1" max="9999" value="<?php if(!empty($_POST['prd_stock'])){echo $_POST['prd_stock'];}?>">
      <?php if (isset($_POST['add_prd']) && empty($_POST['prd_stock'])) echo "<span style='color:red'>Campo requerido</span>"?>
    </div>

    <div class="input-group">
      <label for="prd_image">Imagen:</label>
      <input type="file" name="prd_image">
      <?php if (isset($_POST['add_prd']) && empty($_FILES['prd_image']['name'])) echo "<span style='color:red'>Campo requerido</span>"?>
    </div>

    <div class="input-group">
      <label for="prd_description">Descripción:</label>
      <input type="text" name="prd_description" value="<?php if(!empty($_POST['prd_description'])){echo htmlspecialchars($_POST['prd_description']);}?>">
      <?php if (isset($_POST['add_prd']) && empty($_POST['prd_description'])) echo "<span style='color:red'>Campo requerido</span>"?>
    </div>

    <input type="submit" name="add_prd" value="Añadir Producto" class="boton">
    <input type="submit" name="Volver" value="Volver" formaction="mainpage.php" class="boton">
  </form><br>

  <?php
  include "biblioteca.php"; // Incluir archivo con funciones de base de datos
  $pdo = connection(); // Establecer conexión a la base de datos usando PDO
  // Realizar consulta a la base de datos

  if (isset($_POST['add_prd'])) {
    // Validar imagen antes de procesarla
    if (!empty($_FILES['prd_image']['name'])) {
      $file_info = $_FILES['prd_image'];
      $file_name = $file_info['name'];
      $file_tmp = $file_info['tmp_name'];
      $file_mime = mime_content_type($file_tmp);

      // Validar tipo de archivo y que sea una imagen
      if (($file_mime == 'image/jpeg' || $file_mime == 'image/png' || $file_mime == 'image/jpeg') && exif_imagetype($file_tmp) != false) {
        // Preparar la consulta SQL para insertar el producto en la base de datos
        $SQL = "INSERT INTO pps_products (prd_name, prd_category, prd_details, prd_price, prd_quantity_shop, prd_stock, prd_image, prd_description) VALUES (?,?,?,?,?,?,?,?)";
        $stmt = $pdo->prepare($SQL);

        // Filtrar y sanitizar los datos del formulario
        $prd_name = filter_var($_POST['prd_name'], FILTER_SANITIZE_STRING);
        $prd_category = filter_var($_POST['prd_category'], FILTER_SANITIZE_STRING);
        $prd_details = filter_var($_POST['prd_details'], FILTER_SANITIZE_STRING);
        $prd_price = filter_var($_POST['prd_price'], FILTER_SANITIZE_NUMBER_INT);
        $prd_quantity_shop = filter_var($_POST['prd_quantity_shop'], FILTER_SANITIZE_NUMBER_INT);
        $prd_stock = filter_var($_POST['prd_stock'], FILTER_SANITIZE_NUMBER_INT);
        $prd_image = $file_name; // El nombre del archivo ya ha sido validado
        $prd_description = filter_var($_POST['prd_description'], FILTER_SANITIZE_STRING);

        // Ejecutar la consulta preparada
        try {
          $stmt->execute([$prd_name, $prd_category, $prd_details, $prd_price, $prd_quantity_shop, $prd_stock, $prd_image, $prd_description]);
          echo '<div class="mensaje_exito">Producto añadido correctamente</div>';
        } catch (PDOException $e) {
          echo '<div class="mensaje_error">Error al insertar el producto: ' . $e->getMessage() . '</div>';
        }

        // Cerrar la consulta preparada
        $stmt = null;
      } else {
        echo '<div class="mensaje_error">El archivo debe ser una imagen JPG, PNG o JPEG válida</div>';
      }
    } else {
      echo '<div class="mensaje_error">Debe seleccionar una imagen</div>';
    }

  }

  // Cerrar conexión a la base de datos
  $pdo = null;
  ?>

</body>
</html>
