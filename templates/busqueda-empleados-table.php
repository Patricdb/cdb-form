<?php if ( empty( $empleados ) ) : ?>
    <?php echo cdb_form_get_mensaje(
        'cdb_mensaje_busqueda_sin_empleados'
    ); ?>
<?php else : ?>
<table class="cdb-busqueda-table">
    <thead>
        <tr>
            <th><?php esc_html_e( 'Puntuación', 'cdb-form' ); ?></th>
            <th><?php esc_html_e( 'Nombre', 'cdb-form' ); ?></th>
            <th><?php esc_html_e( 'Bares', 'cdb-form' ); ?></th>
            <th><?php esc_html_e( 'Año', 'cdb-form' ); ?></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ( $empleados as $emp ) : ?>
        <tr>
            <td><?php echo esc_html( $emp['puntuacion'] ); ?></td>
            <td><a href="<?php echo esc_url( get_permalink( $emp['id'] ) ); ?>"><?php echo esc_html( $emp['nombre'] ); ?></a></td>
            <td>
                <?php foreach ( $emp['bares'] as $index => $bar ) : ?>
                    <?php if ( $index > 0 ) echo ', '; ?>
                    <a href="<?php echo esc_url( get_permalink( $bar['id'] ) ); ?>"><?php echo esc_html( $bar['nombre'] ); ?></a>
                <?php endforeach; ?>
            </td>
            <td><?php echo esc_html( $emp['anio'] ); ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
