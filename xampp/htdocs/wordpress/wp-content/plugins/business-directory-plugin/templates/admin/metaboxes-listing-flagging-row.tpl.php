<?php
$user = '';

if ( 0 !== $value[ 'user_id' ] ) :
    $user = get_user_by( 'ID', $value[ 'user_id' ] );
endif;
?>
<tr data-id="<?php echo $key; ?>">
    <td class="authoring-info"><?php echo $user ? $user->data->user_email : 'Visitor'; ?>
        <div class="row-actions">
            <span class="trash">
                <a href="<?php echo esc_url( add_query_arg( array( 'wpbdmaction' => 'delete-flagging', 'listing_id' => $listing->get_id(), 'meta_pos' => $key ) ) ); ?>" class="delete">
                    <?php echo _x( 'Delete', 'flag listing', 'WPBDP' ); ?>
                </a>
            </span>
        </div>
    </td>
    <td class="report">
        <div class="submitted-on">
            <?php echo date_i18n( get_option( 'date_format' ) . ' @ ' . get_option( 'time_format' ), $value['date'] ); ?>
        </div>
        <div class="report-reasons">
            <?php echo _x( 'Selected Option: ', 'admin listings', 'WPBDM' ) . esc_html( $value[ 'reason' ] ); ?>
            <br/>
            <?php
            if ( ! empty( $value['comments'] ) ):
                echo _x( 'Aditional Info: ', 'admin listings', 'WPBDM' ) . esc_html( $value['comments'] );
            endif;
            ?>
        </div>
    </td>
</tr>


