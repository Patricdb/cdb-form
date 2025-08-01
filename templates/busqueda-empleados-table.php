<?php if ( empty( $empleados ) ) : ?>
    <p><?php esc_html_e( 'No se encontraron empleados con esos filtros.', 'cdb-form' ); ?></p>
<?php else : ?>
<table class="cdb-busqueda-table">
    <thead>
        <tr>
            <th><?php esc_html_e( 'Puntuaci\xC3\xB3n', 'cdb-form' ); ?></th>
            <th><?php esc_html_e( 'Nombre', 'cdb-form' ); ?></th>
            <th><?php esc_html_e( 'Posiciones', 'cdb-form' ); ?></th>
            <th><?php esc_html_e( 'Bares', 'cdb-form' ); ?></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ( $empleados as $emp ) : ?>
        <tr>
            <td><?php echo esc_html( $emp['puntuacion'] ); ?></td>
            <td><a href="<?php echo esc_url( get_permalink( $emp['id'] ) ); ?>"><?php echo esc_html( $emp['nombre'] ); ?></a></td>
            <td>
                <?php foreach ( $emp['posiciones'] as $index => $pos ) : ?>
                    <?php if ( $index > 0 ) echo ', '; ?>
                    <a href="<?php echo esc_url( get_permalink( $pos['id'] ) ); ?>"><?php echo esc_html( $pos['nombre'] ); ?></a>
                <?php endforeach; ?>
            </td>
            <td>
                <?php foreach ( $emp['bares'] as $index => $bar ) : ?>
                    <?php if ( $index > 0 ) echo ', '; ?>
                    <a href="<?php echo esc_url( get_permalink( $bar['id'] ) ); ?>"><?php echo esc_html( $bar['nombre'] ); ?></a>
                <?php endforeach; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
