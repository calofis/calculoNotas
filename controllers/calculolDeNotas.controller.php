<?php

declare(strict_types=1);

if (isset($_POST['enviar'])) {
    $data['errores'] = checkForm($_POST);
    $data['input'] = filter_var_array($_POST);
    if (count($data['errores']) === 0) {
//hago la lógica
        $jsonArray = json_decode($_POST['json_notas'], true);
//var_dump($jsonArray);die;
        $resultado = datosAsignaturas($jsonArray);
        $data['resultado'] = $resultado;
    }
}

function checkForm(array $post): array {
    $errores = [];
    if (empty($post['json_notas'])) {
        $errores['json_notas'] = 'Este campo es obligatorio';
    } else {
        $modulos = json_decode($post['json_notas'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $errores['json_notas'] = 'El formato no es correcto';
        } else {
            $erroresJson = "";
            foreach ($modulos as $modulo => $alumnos) {
                if (empty($modulo)) {
                    $erroresJson .= "El nombre del módulo no puede estar vacío<br>";
                }
                if (!is_array($alumnos)) {
                    $erroresJson .= "El módulo '" . htmlentities($modulo) . "' no tiene un array de alumnos<br>"; //Equivale a filter_var($modulo, FILTER_SANITIZE_SPECIAL_CHARS);
                } else {
                    foreach ($alumnos as $nombre => $nota) {
                        if (empty($nombre)) {
                            $erroresJson .= "El módulo '" . htmlentities($modulo) . "' tiene un alumno sin nombre<br>"; //Equivale a filter_var($modulo, FILTER_SANITIZE_SPECIAL_CHARS);
                        }
                        if (!is_int($nota)) {
                            $erroresJson .= "El módulo '" . htmlentities($modulo) . "' tiene la nota '" . htmlentities($modulo) . "' que no es un int<br>"; //Equivale a filter_var($modulo, FILTER_SANITIZE_SPECIAL_CHARS);
                        } else {
                            if ($nota < 0 || $nota > 10) {
                                $erroresJson .= "Módulo '" . htmlentities($modulo) . "' alumno '" . htmlentities($nombre) . "' tiene una nota de " . $nota . "<br>"; //Equivale a filter_var($modulo, FILTER_SANITIZE_SPECIAL_CHARS);
                            }
                        }
                    }
                }
            }
            if (!empty($erroresJson)) {
                $errores['json_notas'] = $erroresJson;
            }
        }
    }
    return $errores;
}

function datosAsignaturas(array $materias): array {
    $resultado = [];
    $alumnos = [];
    foreach ($materias as $nombreMateria => $notas) {
        $resultado[$nombreMateria] = [];
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
                $alumnos[$alumno] = ['aprobados' => 0, 'suspensos' => 0];
            }
            $contarAlumnos++;
            $notaAcumulada += $nota;
            if ($nota < 5) {
                $suspensos++;
                $alumnos[$alumno]['suspensos']++;
            } else {
                $aprobados++;
                $alumnos[$alumno]['aprobados']++;
            }
            if ($nota > $max['nota']) {
                $max['alumno'] = $alumno;
                $max['nota'] = $nota;
            }
            if ($nota < $min['nota']) {
                $min['alumno'] = $alumno;
                $min['nota'] = $nota;
            }
        }
        if ($contarAlumnos > 0) {
            $resultado[$nombreMateria]['media'] = $notaAcumulada / $contarAlumnos;
            $resultado[$nombreMateria]['max'] = $max;
            $resultado[$nombreMateria]['min'] = $min;
        } else {
            $resultado[$nombreMateria]['media'] = 0;
        }
        $resultado[$nombreMateria]['suspensos'] = $suspensos;
        $resultado[$nombreMateria]['aprobados'] = $aprobados;
// $resultado[$nombreMateria]['max']['alumno'] = $max['alumno'];
// $resultado[$nombreMateria]['max']['nota'] = $max['nota'];
    }
    return array('modulos' => $resultado, 'alumnos' => $alumnos);
}

include 'views/templates/header.php';
include 'views/calculoDeNotas.view.php';
include 'views/templates/footer.php';
