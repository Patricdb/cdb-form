<?php
// Asegurar que el archivo no se acceda directamente.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Validar el formulario de empleado
function cdb_validate_empleado( $data ) {
    $errors = array();

    // Validar nombre
    if ( empty( $data['nombre'] ) || ! is_string( $data['nombre'] ) ) {
        $errors[] = 'El nombre es obligatorio y debe ser un texto válido.';
    }

    // Validar disponibilidad (convertir explícitamente a booleano)
    $data['disponible'] = filter_var($data['disponible'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    if ($data['disponible'] === null) {
        $errors[] = 'El campo "Disponible" debe ser Sí o No.';
    }

    return $errors;
}

// Validar el formulario de bar
function cdb_validate_bar( $data ) {
    $errors = array();

    // Validar nombre del bar
    if ( empty( $data['nombre_bar'] ) || ! is_string( $data['nombre_bar'] ) ) {
        $errors[] = 'El nombre del bar es obligatorio y debe ser un texto válido.';
    }

    // Validar estado del bar (eliminar espacios adicionales)
    $estados_permitidos = array(
        'Abierto todo el año',
        'Abierto temporalmente',
        'Cerrado temporalmente',
        'Cerrado permanente',
        'Traspaso',
        'Desconocido'
    );
    $data['estado'] = trim($data['estado']);

    if (!in_array($data['estado'], $estados_permitidos)) {
        $errors[] = 'El estado del bar no es válido.';
    }

    return $errors;
}

/**
 * Validar el formulario de experiencia
 * - Verificar que bar_id y anio sean numéricos
 * - (Opcional) Podemos comprobar que anio no sea mayor/menor que las fechas de apertura/cierre del bar
 */
function cdb_validate_experiencia( $data ) {
    $errors = array();

    // Validar bar_id
    if ( empty( $data['bar_id'] ) || ! is_numeric( $data['bar_id'] ) ) {
        $errors[] = 'El campo "Bar" es obligatorio.';
    }

    // Validar anio
    if ( empty( $data['anio'] ) || ! is_numeric( $data['anio'] ) ) {
        $errors[] = 'El campo "Año" es obligatorio y debe ser un número.';
    } else {
        // Comprobación opcional: si se quiere chequear rango de apertura/cierre
        // if (function_exists('cdb_bar_rango_valido')) {
        //     if (!cdb_bar_rango_valido($data['bar_id'], $data['anio'])) {
        //         $errors[] = 'El año no está dentro del rango de apertura/cierre del bar.';
        //     }
        // }
    }

    // Validar posicion_id (ahora apunta a un post de tipo cdb_posiciones)
    if ( empty( $data['posicion_id'] ) || ! is_numeric( $data['posicion_id'] ) ) {
        $errors[] = 'La posición es obligatoria.';
    } else {
        // Verificar opcionalmente que sea un post_type = 'cdb_posiciones'
        $pos_id   = (int) $data['posicion_id'];
        $pos_post = get_post( $pos_id );
        if ( ! $pos_post || $pos_post->post_type !== 'cdb_posiciones' ) {
            $errors[] = 'La posición seleccionada no es válida.';
        }
    }

    return $errors;
}
