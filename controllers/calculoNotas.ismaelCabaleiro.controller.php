<?php

declare(strict_types=1);
$data = [];

if (isset($_POST['enviar'])) {
    $data['errores'] = checkForm($_POST);
    $data['input'] = filter_var_array($_POST);
    if (count($data['errores']) === 0) {
        $jsonArray = json_decode($_POST['json'], true);
        $resultado = sacarDatos($jsonArray);
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
                        if (!is_array($nota)) {
                            $erroresJson .= "El alumno '" . htmlentities($nombre) . "' no tiene un array de notas<br>";
                        } else {
                            foreach ($nota as $puntuaciones => $a) {
                                if (!is_int($a)) {
                                    $erroresJson .= "El módulo '" . htmlentities($modulo) . "' tiene una nota de '" . htmlentities($nombre) . "' que no es un int<br>";
                                } else {
                                    if ($a < 0 || $a > 10) {
                                        $erroresJson .= "'El alumno '" . htmlentities($nombre) . " en el modulo '" . htmlentities($modulo) . "' tiene una nota de " . $a . "<br>";
                                    }
                                }
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

function sacarDatos(array $materias): array {
    $resultado = [];
    $alumnos = [];
    $promociona = [];
    $noPromociona = [];

    foreach ($materias as $materia => $notas) {
        $resultado[$materia] = [];
        $suspensos = 0;
        $aprobados = 0;
        $max = [
            'alumno' => '',
            'nota' => -1
        ];
        $min = [
            'alumno' => '',
            'nota' => 11
        ];
        $notaAcumulada = 0;
        $contarAlumnos = 0;
        foreach ($notas as $alumno => $nota) {
            if (!isset($alumnos[$alumno])) {
                $alumnos[$alumno] = ['aprobados' => 0, 'suspensos' => 0, 'media' => 0];
            }
            $contarAlumnos++;
            $cantidadNotas = 0;
            $sumaNotas = 0;
            foreach ($nota as $puntuaciones => $a) {
                $sumaNotas += $a;
                $cantidadNotas++;
            }
            $alumnos[$alumno]['media'] = $sumaNotas / $cantidadNotas;
            if ($alumnos[$alumno]['media'] < 5) {
                $suspensos++;
                $alumnos[$alumno]['suspensos']++;
            } else {
                $aprobados++;
                $alumnos[$alumno]['aprobados']++;
            }
            if ($alumnos[$alumno]['media'] > $max['nota']) {
                $max['alumno'] = $alumno;
                $max['nota'] = $a;
            }
            if ($alumnos[$alumno]['media'] < $min['nota']) {
                $min['alumno'] = $alumno;
                $min['nota'] = $a;
            }
            $notaAcumulada += $alumnos[$alumno]['media'];
        }
        if ($contarAlumnos > 0) {
            $resultado[$materia]['media'] = $notaAcumulada / $contarAlumnos;
            $resultado[$materia]['max'] = $max;
            $resultado[$materia]['min'] = $min;
        } else {
            $resultado[$materia]['media'] = 0;
        }
        $resultado[$materia]['suspensos'] = $suspensos;
        $resultado[$materia]['aprobados'] = $aprobados;
    }
     return array('modulos' => $resultado, 'alumnos' => $alumnos);
}

include 'views/templates/header.php';
include 'views/calculoNotas.ismaelCabaleiro.view.php';
include 'views/templates/footer.php';
