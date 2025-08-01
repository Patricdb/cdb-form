<?php if ( empty( $bares ) ) : ?>
    <p><?php esc_html_e( 'No se encontraron bares con esos filtros.', 'cdb-form' ); ?></p>
<?php else : ?>
<table class="cdb-busqueda-table">
    <thead>
        <tr>
            <th><?php esc_html_e( 'Puntuación', 'cdb-form' ); ?></th>
            <th><?php esc_html_e( 'Nombre', 'cdb-form' ); ?></th>
            <th><?php esc_html_e( 'Zona', 'cdb-form' ); ?></th>
            <th><?php esc_html_e( 'Año', 'cdb-form' ); ?></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ( $bares as $bar ) : // 'puntuacion' proviene del meta 'cdb_puntuacion_total' del bar ?>
        <tr>
            <td><?php echo esc_html( $bar['puntuacion'] ); ?></td>
            <td><a href="<?php echo esc_url( get_permalink( $bar['id'] ) ); ?>"><?php echo esc_html( $bar['nombre'] ); ?></a></td>
            <td>
                <?php if ( ! empty( $bar['zona'] ) ) : ?>
                    <a href="<?php echo esc_url( get_permalink( $bar['zona']['id'] ) ); ?>"><?php echo esc_html( $bar['zona']['nombre'] ); ?></a>
                <?php endif; ?>
            </td>
            <td><?php echo esc_html( $bar['apertura'] ); ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
