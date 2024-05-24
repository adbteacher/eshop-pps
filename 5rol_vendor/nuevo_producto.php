<!DOCTYPE html>
<html>
<head>
  <title>Añadir un nuevo producto</title>
  <link href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php
session_start();
require_once '../vendor/autoload.php';
require_once 'biblioteca.php';

//AddSecurityHeaders();
include "../nav.php"; // Incluye el Navbar
?>

<h1 class="my-4">Añadir un nuevo producto</h1>

<div class="container">
  <form action="nuevo_producto.php" method="post" enctype="multipart/form-data">
    <div class="mb-3">
      <label for="prd_name" class="form-label">Nombre:</label>
      <input type="text" class="form-control" name="prd_name" value="<?php if (!empty($_POST['prd_name'])) { echo htmlspecialchars($_POST['prd_name']); } ?>">
      <?php if (isset($_POST['add_prd']) && empty($_POST['prd_name'])) echo "<span class='text-danger'>Campo requerido</span>" ?>
    </div>

    <div class="mb-3">
      <label for="prd_category" class="form-label">Categoría:</label>
      <select class="form-select" name="prd_category">
        <option value="0" <?php if (!empty($_POST['prd_category']) && $_POST['prd_category'] == '0') echo 'selected'; ?>></option>
        <?php
        $conn = GetDatabaseConnection();
        $stmt = $conn->query("SELECT cat_id, cat_description FROM pps_categories");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
          echo "<option value='" . htmlspecialchars($row['cat_id']) . "'";
          if (!empty($_POST['prd_category']) && $_POST['prd_category'] == $row['cat_id']) echo ' selected';
          echo ">" . htmlspecialchars($row['cat_description']) . "</option>";
        }
        $conn = null;
        ?>
      </select>
      <?php if (isset($_POST['add_prd']) && empty($_POST['prd_category'])) echo "<span class='text-danger'>Campo requerido</span>" ?>
    </div>

    <div class="mb-3">
      <label for="prd_details" class="form-label">Detalles:</label>
      <input type="text" class="form-control" name="prd_details" value="<?php if (!empty($_POST['prd_details'])) { echo htmlspecialchars($_POST['prd_details']); } ?>">
      <?php if (isset($_POST['add_prd']) && empty($_POST['prd_details'])) echo "<span class='text-danger'>Campo requerido</span>" ?>
    </div>

    <div class="mb-3">
      <label for="prd_price" class="form-label">Precio:</label>
      <input type="number" class="form-control" name="prd_price" min="1" max="9999" value="<?php if (!empty($_POST['prd_price'])) { echo htmlspecialchars($_POST['prd_price']); } ?>">
      <?php if (isset($_POST['add_prd']) && empty($_POST['prd_price'])) echo "<span class='text-danger'>Campo requerido</span>" ?>
    </div>

    <div class="mb-3">
      <label for="prd_quantity_shop" class="form-label">Cantidad en tienda:</label>
      <input type="number" class="form-control" name="prd_quantity_shop" min="1" max="9999" value="<?php if (!empty($_POST['prd_quantity_shop'])) { echo htmlspecialchars($_POST['prd_quantity_shop']); } ?>">
      <?php if (isset($_POST['add_prd']) && empty($_POST['prd_quantity_shop'])) echo "<span class='text-danger'>Campo requerido</span>" ?>
    </div>

    <div class="mb-3">
      <label for="prd_stock" class="form-label">Stock:</label>
      <input type="number" class="form-control" name="prd_stock" min="1" max="9999" value="<?php if (!empty($_POST['prd_stock'])) { echo htmlspecialchars($_POST['prd_stock']); } ?>">
      <?php if (isset($_POST['add_prd']) && empty($_POST['prd_stock'])) echo "<span class='text-danger'>Campo requerido</span>" ?>
    </div>

    <div class="mb-3">
      <label for="prd_image" class="form-label">Imagen:</label>
      <input type="file" class="form-control" name="prd_image">
      <?php if (isset($_POST['add_prd']) && empty($_FILES['prd_image']['name'])) echo "<span class='text-danger'>Campo requerido</span>" ?>
    </div>

    <div class="mb-3">
      <label for="prd_description" class="form-label">Descripción:</label>
      <input type="text" class="form-control" name="prd_description" value="<?php if (!empty($_POST['prd_description'])) { echo htmlspecialchars($_POST['prd_description']); } ?>">
      <?php if (isset($_POST['add_prd']) && empty($_POST['prd_description'])) echo "<span class='text-danger'>Campo requerido</span>" ?>
    </div>

    <button type="submit" name="add_prd" class="btn btn-primary">Añadir Producto</button>
    <button type="submit" name="Volver" formaction="mainpage.php" class="btn btn-secondary">Volver</button>
  </form><br>
</div>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['Volver'])) {
    header('Location: mainpage.php');
    exit();
  }

  $conn = GetDatabaseConnection();

  if (isset($_POST['add_prd'])) {
    if (!empty($_FILES['prd_image']['name'])) {
      $file_info = $_FILES['prd_image'];
      $file_name = basename($file_info['name']);
      $file_tmp = $file_info['tmp_name'];
      $file_mime = mime_content_type($file_tmp);
      $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
      $allowed_extensions = ['jpg', 'jpeg', 'png'];

      if (in_array($file_ext, $allowed_extensions) && ($file_mime == 'image/jpeg' || $file_mime == 'image/png') && exif_imagetype($file_tmp) != false) {
        $new_file_name = uniqid('img_', true) . '.' . $file_ext;
        $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/pruebas/uploads/';
        $upload_path = $upload_dir . $new_file_name;
        if (!is_dir($upload_dir)) {
          mkdir($upload_dir, 0777, true);
        }
        if (move_uploaded_file($file_tmp, $upload_path)) {
          $SQL = "INSERT INTO pps_products (prd_name, prd_category, prd_details, prd_price, prd_quantity_shop, prd_stock, prd_image, prd_description) VALUES (?,?,?,?,?,?,?,?)";
          $stmt = $conn->prepare($SQL);

          $prd_name = filter_var($_POST['prd_name'], FILTER_SANITIZE_STRING);
          $prd_category = filter_var($_POST['prd_category'], FILTER_SANITIZE_NUMBER_INT);
          $prd_details = filter_var($_POST['prd_details'], FILTER_SANITIZE_STRING);
          $prd_price = filter_var($_POST['prd_price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
          $prd_quantity_shop = filter_var($_POST['prd_quantity_shop'], FILTER_SANITIZE_NUMBER_INT);
          $prd_stock = filter_var($_POST['prd_stock'], FILTER_SANITIZE_NUMBER_INT);
          $prd_image = $new_file_name;
          $prd_description = filter_var($_POST['prd_description'], FILTER_SANITIZE_STRING);

          try {
            $stmt->execute([$prd_name, $prd_category, $prd_details, $prd_price, $prd_quantity_shop, $prd_stock, $prd_image, $prd_description]);
            echo '<div class="alert alert-success">Producto añadido correctamente</div>';
          } catch (PDOException $e) {
            echo '<div class="alert alert-danger">Error al insertar el producto: ' . htmlspecialchars($e->getMessage()) . '</div>';
          }

          $stmt = null;
        } else {
          echo '<div class="alert alert-danger">Error al mover el archivo subido</div>';
        }
      } else {
        echo '<div class="alert alert-danger">El archivo debe ser una imagen JPG, PNG o JPEG válida</div>';
      }
    } else {
      echo '<div class="alert alert-danger">Debe seleccionar una imagen</div>';
    }
  }

  $conn = null;
}
?>

<script src="/vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<?php include "../footer.php"; // Incluye el footer ?>
</body>
</html>