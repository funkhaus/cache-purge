<?php
/**
 * Nested Pages plugin compatibility.
 */

add_action( 'wp_ajax_npsort', 'cp_nested_pages_compatibility', 9 );
add_action( 'cp_purge_cache_bulk', 'cp_purge_cache_bulk', 10, 1 );

function cp_nested_pages_compatibility() {
    $list = $_POST['list'] ?? '';
    $nonce = $_POST['nonce'] ?? '';

    if ( ! wp_verify_nonce( sanitize_text_field( $nonce ), 'nestedpages-nonce' ) ) {
        return;
    }

    if ( empty( $list ) ) {
        return;
    }

    $post_ids = [];
    cp_prepare_post_ids( $list, 0, $post_ids );

    $post_ids = array_unique( $post_ids );

    if ( ! empty( $post_ids ) ) {
        if ( wp_next_scheduled( 'cp_run_purge_cache' ) ) {
            error_log( print_r( 'Already scheduled', true ) );
            return;
        }
        wp_schedule_single_event( time() + 1, 'cp_run_purge_cache' );
    }
}

/**
 * Prepare post IDs for cache purge. Parent and menu order are checked to ensure
 * that the post is changed.
 *
 * @param array $items
 * @param int $parent
 * @param array $aggregates
 */
function cp_prepare_post_ids( $items, $parent = 0, &$aggregates = [] ) {
    foreach ( $items as $order => $item ) {
        $post_id = intval( $item['id'] );

        $post_arr = get_post( $post_id, ARRAY_A );

        if ( $post_arr['post_parent'] !== $parent || $post_arr['menu_order'] !== $order ) {
            if ( ! empty( $post_arr['post_parent'] ) ) {
                $aggregates[] = $post_arr['post_parent']; // Parent should be purged as well.
            }
            $aggregates[] = $post_id;
        }

        if ( isset( $item['children'] ) ) {
            cp_prepare_post_ids( $item['children'], $post_id, $aggregates );
        }
    }
}
