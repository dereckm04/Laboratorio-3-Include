<?php
// index.php - Página principal con el formulario visual de registro
include 'includes/header.php';
?>

    <main class="container flex-grow-1 my-4">
        <section class="mx-auto" style="max-width: 520px;">
            <h1 class="h3 fw-bold mb-3">Formulario de Registro de Aspirantes</h1>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <!-- enctype multipart/form-data es obligatorio para poder enviar la foto -->
                    <form action="procesar.php" method="post" enctype="multipart/form-data">

                        <div class="mb-3">
                            <label for="nombre" class="form-label fw-semibold">Nombre (Requerido):</label>
                            <input type="text" class="form-control" id="nombre" name="nombre"
                                   placeholder="Ej: Sofía" required>
                        </div>

                        <div class="mb-3">
                            <label for="apellido" class="form-label fw-semibold">Apellido (Requerido):</label>
                            <input type="text" class="form-control" id="apellido" name="apellido"
                                   placeholder="Ej: Pérez Gómez" required>
                        </div>

                        <div class="mb-3">
                            <label for="identificacion" class="form-label fw-semibold">Identificación (Requerido):</label>
                            <input type="text" class="form-control" id="identificacion" name="identificacion"
                                   placeholder="Ej: 8-123-4567" required>
                        </div>

                        <div class="mb-3">
                            <label for="fecha_nacimiento" class="form-label fw-semibold">Fecha de Nacimiento (Requerido):</label>
                            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento"
                                   placeholder="aaaa-mm-dd" required>
                        </div>

                        <div class="mb-3">
                            <span class="form-label fw-semibold d-block">Sexo (Requerido):</span>
                            <div class="btn-group w-100" role="group" aria-label="Sexo">
                                <input type="radio" class="btn-check" name="sexo" id="sexo_hombre" value="Hombre" required>
                                <label class="btn btn-outline-secondary" for="sexo_hombre">Hombre</label>

                                <input type="radio" class="btn-check" name="sexo" id="sexo_mujer" value="Mujer" required>
                                <label class="btn btn-outline-secondary" for="sexo_mujer">Mujer</label>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="foto" class="form-label fw-semibold">Fotografía del Aspirante (png, jpg, jpeg, gif, webp):</label>
                            <input type="file" class="form-control" id="foto" name="foto"
                                   accept=".png,.jpg,.jpeg,.gif,.webp,image/*" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Registrar Aspirante</button>
                    </form>
                </div>
            </div>
        </section>
    </main>

<?php include 'includes/footer.php'; ?>
