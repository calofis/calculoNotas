<?php
declare(strict_types=1);
$data = [];

if (isset($_POST['enviar'])) {
    $data['errores'] = checkForm($_POST);
    $data['input'] = filter_var_array($_POST);
    if (count($data['errores']) === 0) {
        $jsonArray = json_decode($_POST['json'], true);
        $resultado = datosAsignaturas($jsonArray);
        $data['resultado'] = $resultado;
    }
}

function checkForm(array $post): array {
    $errores = [];
    if (empty($post['json'])) {
        $errores['json'] = 'Este campo es obligatorio';
    } else {
        $modulos = json_decode($post['json'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $errores['json'] = 'El formato no es correcto';
        } else {
            $erroresJson = "";
            foreach ($modulos as $modulo => $alumnos) {
                if (empty($modulo)) {
                    $erroresJson .= "El nombre del módulo no puede estar vacío<br>";
                }
                if (!is_array($alumnos)) {
                    $erroresJson .= "El módulo '" . htmlentities($modulo) . "' no tiene un array de alumnos<br>";
                } else {
                    foreach ($alumnos as $nombre => $nota) {
                        if (empty($nombre)) {
                            $erroresJson .= "El módulo '" . htmlentities($modulo) . "' tiene un alumno sin nombre<br>";
                        }
                        if (!is_int($nota)) {
                            $erroresJson .= "El módulo '" . htmlentities($modulo) . "' tiene la nota de '" . htmlentities($nombre) . "' que no es un int<br>";
                        } else {
                            if ($nota < 0 || $nota > 10) {
                                $erroresJson .= "'El alumno '" . htmlentities($nombre) . " en el modulo '" . htmlentities($modulo) . "' tiene una nota de " . $nota . "<br>";
                            }
                        }
                    }
                }
            }
            if (!empty($erroresJson)) {
                $errores['json'] = $erroresJson;
            }
        }
    }
    return $errores;
}


include 'views/templates/header.php';
include 'views/calculoNotas.ismaelCabaleiro.view.php';
include 'views/templates/footer.php';
