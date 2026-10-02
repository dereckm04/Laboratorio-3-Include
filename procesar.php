<?php
// procesar.php - Backend: valida, estandariza y guarda la foto del aspirante

// ----- Funciones de ayuda -----

// Limpia un texto: quita etiquetas y espacios sobrantes
function limpiar($texto)
{
    return trim(strip_tags($texto));
}

// Formato Tipo Título: "sofia perez" -> "Sofia Perez"
// (ucwords(strtolower()) de la rúbrica; con mbstring se manejan bien las tildes)
function tipoTitulo($texto)
{
    if (function_exists('mb_convert_case')) {
        return mb_convert_case($texto, MB_CASE_TITLE, 'UTF-8');
    }
    return ucwords(strtolower($texto));
}

// Muestra un texto de forma segura en pantalla (previene XSS)
function e($texto)
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

// ----- Variables de trabajo -----
$errores = [];
$exito = false;
$nombre = $apellido = $identificacion = $sexo = $fechaNacimiento = '';
$edad = null;
$nombreArchivo = '';
$vistaFoto = '';

const EDAD_MIN = 18;
const EDAD_MAX = 70;
const MAX_BYTES = 2 * 1024 * 1024; // 2 MB
$extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$mimePermitidos = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/gif'  => 'gif',
    'image/webp' => 'webp',
];
$carpetaFotos = __DIR__ . '/uploaded_files/';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $errores[] = 'Debe llenar el formulario de registro primero.';
} else {

    // 1. Recibir y limpiar los datos
    $nombre          = tipoTitulo(limpiar($_POST['nombre'] ?? ''));
    $apellido        = tipoTitulo(limpiar($_POST['apellido'] ?? ''));
    $identificacion  = strtoupper(limpiar($_POST['identificacion'] ?? ''));
    $sexo            = limpiar($_POST['sexo'] ?? '');
    $fechaNacimiento = limpiar($_POST['fecha_nacimiento'] ?? '');

    // 2. Validar que los campos no estén vacíos
    if ($nombre === '')          $errores[] = 'El nombre es obligatorio.';
    if ($apellido === '')        $errores[] = 'El apellido es obligatorio.';
    if ($identificacion === '')  $errores[] = 'La identificación es obligatoria.';
    if ($sexo === '')            $errores[] = 'Debe seleccionar el sexo.';
    elseif (!in_array($sexo, ['Hombre', 'Mujer'], true)) $errores[] = 'El sexo seleccionado no es válido.';

    // 3. Calcular la edad y verificar el rango (18 a 70 años)
    if ($fechaNacimiento === '') {
        $errores[] = 'La fecha de nacimiento es obligatoria.';
    } else {
        $fecha = DateTime::createFromFormat('Y-m-d', $fechaNacimiento);
        $fechaValida = $fecha && $fecha->format('Y-m-d') === $fechaNacimiento;

        if (!$fechaValida) {
            $errores[] = 'La fecha de nacimiento no es válida.';
        } elseif ($fecha > new DateTime('today')) {
            $errores[] = 'La fecha de nacimiento no puede ser futura.';
        } else {
            $edad = $fecha->diff(new DateTime('today'))->y;
            if ($edad < EDAD_MIN || $edad > EDAD_MAX) {
                $errores[] = 'La edad debe estar entre ' . EDAD_MIN . ' y ' . EDAD_MAX . ' años (edad calculada: ' . $edad . ').';
            }
        }
    }

    // 4. Validar la foto
    if (!isset($_FILES['foto']) || $_FILES['foto']['error'] === UPLOAD_ERR_NO_FILE) {
        $errores[] = 'Debe subir la fotografía del aspirante.';
    } elseif ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        $errores[] = 'Ocurrió un error al subir la fotografía (código ' . (int) $_FILES['foto']['error'] . ').';
    } else {
        $tmp = $_FILES['foto']['tmp_name'];
        $extension = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

        if (!is_uploaded_file($tmp)) {
            $errores[] = 'El archivo recibido no es válido.';
        } elseif ($_FILES['foto']['size'] > MAX_BYTES) {
            $errores[] = 'La fotografía no puede pesar más de 2 MB.';
        } elseif (!in_array($extension, $extensionesPermitidas, true)) {
            $errores[] = 'Extensión no permitida. Solo se aceptan: ' . implode(', ', $extensionesPermitidas) . '.';
        } else {
            // Se revisa el contenido real del archivo, no solo el nombre
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($tmp);
            if (!isset($mimePermitidos[$mime]) || @getimagesize($tmp) === false) {
                $errores[] = 'El archivo no es una imagen válida.';
            }
        }
    }

    // 5. Si todo está bien, guardar la foto de forma segura
    if (empty($errores)) {
        // Nombre aleatorio: no se usa el nombre que mandó el usuario
        $nombreArchivo = bin2hex(random_bytes(12)) . '.' . $mimePermitidos[$mime];
        $destino = $carpetaFotos . $nombreArchivo;

        if (move_uploaded_file($tmp, $destino)) {
            $exito = true;
            // La carpeta está bloqueada desde el navegador, así que la vista previa
            // se arma leyendo el archivo desde el servidor
            $vistaFoto = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($destino));
        } else {
            $errores[] = 'No se pudo guardar la fotografía en el servidor.';
        }
    }
}

include 'includes/header.php';
?>

    <main class="container flex-grow-1 my-4">
        <section class="mx-auto" style="max-width: 640px;">

            <?php if ($exito): ?>
                <div class="alert alert-success" role="alert">
                    <strong>¡Registro exitoso!</strong> El aspirante fue registrado correctamente.
                </div>

                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <div class="row g-3 align-items-center">
                            <div class="col-sm-4 text-center">
                                <img src="<?php echo $vistaFoto; ?>" alt="Fotografía del aspirante"
                                     class="img-thumbnail" style="max-height: 180px;">
                            </div>
                            <div class="col-sm-8">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item"><strong>Nombre:</strong> <?php echo e($nombre); ?></li>
                                    <li class="list-group-item"><strong>Apellido:</strong> <?php echo e($apellido); ?></li>
                                    <li class="list-group-item"><strong>Identificación:</strong> <?php echo e($identificacion); ?></li>
                                    <li class="list-group-item"><strong>Fecha de nacimiento:</strong> <?php echo e($fechaNacimiento); ?></li>
                                    <li class="list-group-item"><strong>Edad:</strong> <?php echo (int) $edad; ?> años</li>
                                    <li class="list-group-item"><strong>Sexo:</strong> <?php echo e($sexo); ?></li>
                                    <li class="list-group-item"><strong>Foto guardada como:</strong> <?php echo e($nombreArchivo); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <a href="index.php" class="btn btn-primary mt-3">Registrar otro aspirante</a>

            <?php else: ?>
                <div class="alert alert-danger" role="alert">
                    <strong>No se pudo completar el registro:</strong>
                    <ul class="mb-0 mt-2">
                        <?php foreach ($errores as $error): ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <a href="index.php" class="btn btn-secondary">Volver al formulario</a>
            <?php endif; ?>

        </section>
    </main>

<?php include 'includes/footer.php'; ?>
