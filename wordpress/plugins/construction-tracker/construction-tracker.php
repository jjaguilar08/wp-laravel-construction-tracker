<?php
/**
 * Plugin Name:       Construction Tracker
 * Plugin URI:         https://example.com/construction-tracker
 * Description:       Tracks construction log entries (materials, payroll, permits, hauling, equipment) as a custom post type with REST API support.
 * Version:            1.0.0
 * Requires at least:  5.8
 * Requires PHP:       7.4
 * Author:             Construction Tracker
 * License:            GPL v2 or later
 * License URI:        https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:        construction-tracker
 *
 * @package Construction_Tracker
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the `construction_log` custom post type.
 */
function ct_register_construction_log_post_type() {
	$labels = array(
		'name'               => __( 'Construction Log Entries', 'construction-tracker' ),
		'singular_name'      => __( 'Construction Log Entry', 'construction-tracker' ),
		'add_new'            => __( 'Add New', 'construction-tracker' ),
		'add_new_item'       => __( 'Add New Construction Log Entry', 'construction-tracker' ),
		'edit_item'          => __( 'Edit Construction Log Entry', 'construction-tracker' ),
		'new_item'           => __( 'New Construction Log Entry', 'construction-tracker' ),
		'view_item'          => __( 'View Construction Log Entry', 'construction-tracker' ),
		'search_items'       => __( 'Search Construction Log Entries', 'construction-tracker' ),
		'not_found'          => __( 'No construction log entries found', 'construction-tracker' ),
		'not_found_in_trash' => __( 'No construction log entries found in Trash', 'construction-tracker' ),
		'all_items'          => __( 'Construction Log Entries', 'construction-tracker' ),
		'menu_name'          => __( 'Construction Log', 'construction-tracker' ),
	);

	$args = array(
		'labels'       => $labels,
		'public'       => true,
		'show_in_rest' => true,
		'supports'     => array( 'title' ),
		'menu_icon'    => 'dashicons-hammer',
		'has_archive'  => true,
		'rewrite'      => array( 'slug' => 'construction-log' ),
	);

	register_post_type( 'construction_log', $args );
}
add_action( 'init', 'ct_register_construction_log_post_type' );

/**
 * Register the `help_article` custom post type.
 */
function ct_register_help_article_post_type() {
	$labels = array(
		'name'               => __( 'Help Articles', 'construction-tracker' ),
		'singular_name'      => __( 'Help Article', 'construction-tracker' ),
		'add_new'            => __( 'Add New', 'construction-tracker' ),
		'add_new_item'       => __( 'Add New Help Article', 'construction-tracker' ),
		'edit_item'          => __( 'Edit Help Article', 'construction-tracker' ),
		'new_item'           => __( 'New Help Article', 'construction-tracker' ),
		'view_item'          => __( 'View Help Article', 'construction-tracker' ),
		'search_items'       => __( 'Search Help Articles', 'construction-tracker' ),
		'not_found'          => __( 'No help articles found', 'construction-tracker' ),
		'not_found_in_trash' => __( 'No help articles found in Trash', 'construction-tracker' ),
		'all_items'          => __( 'Help Articles', 'construction-tracker' ),
		'menu_name'          => __( 'Help Articles', 'construction-tracker' ),
	);

	$args = array(
		'labels'       => $labels,
		'public'       => true,
		'show_in_rest' => true,
		'supports'     => array( 'title', 'editor', 'thumbnail' ),
		'menu_icon'    => 'dashicons-editor-help',
		'has_archive'  => true,
		'rewrite'      => array( 'slug' => 'help-articles' ),
	);

	register_post_type( 'help_article', $args );
}
add_action( 'init', 'ct_register_help_article_post_type' );

/**
 * Use a more descriptive title placeholder on the `construction_log` edit screen.
 *
 * @param string $title Existing placeholder text.
 * @return string
 */
function ct_construction_log_title_placeholder( $title ) {
	$screen = get_current_screen();

	if ( $screen && 'construction_log' === $screen->post_type ) {
		return __( 'e.g. "Concrete delivery — Site A"', 'construction-tracker' );
	}

	return $title;
}
add_filter( 'enter_title_here', 'ct_construction_log_title_placeholder' );

/**
 * Register custom meta fields for the `construction_log` post type.
 */
function ct_register_construction_log_meta() {
	$meta_args = array(
		'show_in_rest'      => true,
		'single'            => true,
		'auth_callback'     => function () {
			return current_user_can( 'edit_posts' );
		},
	);

	register_post_meta(
		'construction_log',
		'entry_date',
		array_merge( $meta_args, array( 'type' => 'string' ) )
	);

	register_post_meta(
		'construction_log',
		'category',
		array_merge(
			$meta_args,
			array(
				'type'         => 'string',
				'show_in_rest' => array(
					'schema' => array(
						'type' => 'string',
						'enum' => array( 'materials', 'payroll', 'permits', 'hauling', 'equipment' ),
					),
				),
			)
		)
	);

	register_post_meta(
		'construction_log',
		'amount',
		array_merge( $meta_args, array( 'type' => 'number' ) )
	);

	register_post_meta(
		'construction_log',
		'notes',
		array_merge( $meta_args, array( 'type' => 'string' ) )
	);
}
add_action( 'init', 'ct_register_construction_log_meta' );

/**
 * Register the meta box for entering construction log details.
 */
function ct_add_construction_log_meta_box() {
	add_meta_box(
		'ct_construction_log_details',
		__( 'Construction Log Details', 'construction-tracker' ),
		'ct_render_construction_log_meta_box',
		'construction_log',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'ct_add_construction_log_meta_box' );

/**
 * Enqueue admin styling for the `construction_log` edit screen only.
 */
function ct_enqueue_admin_assets() {
	$screen = get_current_screen();

	if ( ! $screen || 'construction_log' !== $screen->post_type ) {
		return;
	}

	wp_add_inline_style( 'common', '
		#ct_construction_log_details .inside { margin: 0; padding: 0; }
		.ct-fields { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px 24px; padding: 16px; }
		.ct-field { display: flex; flex-direction: column; gap: 6px; }
		.ct-field--full { grid-column: 1 / -1; }
		.ct-field label { display: flex; align-items: center; gap: 6px; font-weight: 600; font-size: 13px; color: #1d2327; }
		.ct-field label .dashicons { font-size: 16px; width: 16px; height: 16px; color: #646970; }
		.ct-field input[type="date"],
		.ct-field input[type="number"],
		.ct-field select,
		.ct-field textarea { border-radius: 4px; border-color: #8c8f94; padding: 8px 10px; }
		.ct-field textarea { resize: vertical; }
	' );
}
add_action( 'admin_enqueue_scripts', 'ct_enqueue_admin_assets' );

/**
 * Render the meta box fields.
 *
 * @param WP_Post $post Current post object.
 */
function ct_render_construction_log_meta_box( $post ) {
	wp_nonce_field( 'ct_save_construction_log_meta', 'ct_construction_log_meta_nonce' );

	$entry_date = get_post_meta( $post->ID, 'entry_date', true );
	$category   = get_post_meta( $post->ID, 'category', true );
	$amount     = get_post_meta( $post->ID, 'amount', true );
	$notes      = get_post_meta( $post->ID, 'notes', true );

	$categories = array(
		'materials' => __( 'Materials', 'construction-tracker' ),
		'payroll'   => __( 'Payroll', 'construction-tracker' ),
		'permits'   => __( 'Permits', 'construction-tracker' ),
		'hauling'   => __( 'Hauling', 'construction-tracker' ),
		'equipment' => __( 'Equipment', 'construction-tracker' ),
	);
	?>
	<div class="ct-fields">
		<div class="ct-field">
			<label for="ct_entry_date"><span class="dashicons dashicons-calendar-alt"></span><?php esc_html_e( 'Entry Date', 'construction-tracker' ); ?></label>
			<input type="date" id="ct_entry_date" name="ct_entry_date" value="<?php echo esc_attr( $entry_date ); ?>" />
		</div>
		<div class="ct-field">
			<label for="ct_category"><span class="dashicons dashicons-category"></span><?php esc_html_e( 'Category', 'construction-tracker' ); ?></label>
			<select id="ct_category" name="ct_category">
				<option value=""><?php esc_html_e( '— Select a category —', 'construction-tracker' ); ?></option>
				<?php foreach ( $categories as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $category, $value ); ?>>
						<?php echo esc_html( $label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="ct-field">
			<label for="ct_amount"><span class="dashicons dashicons-money-alt"></span><?php esc_html_e( 'Amount', 'construction-tracker' ); ?></label>
			<input type="number" step="0.01" id="ct_amount" name="ct_amount" value="<?php echo esc_attr( $amount ); ?>" />
		</div>
		<div class="ct-field ct-field--full">
			<label for="ct_notes"><span class="dashicons dashicons-edit-page"></span><?php esc_html_e( 'Notes', 'construction-tracker' ); ?></label>
			<textarea id="ct_notes" name="ct_notes" rows="5"><?php echo esc_textarea( $notes ); ?></textarea>
		</div>
	</div>
	<?php
}

/**
 * Save the meta box fields when a `construction_log` post is saved.
 *
 * @param int $post_id Post ID being saved.
 */
function ct_save_construction_log_meta( $post_id ) {
	if ( ! isset( $_POST['ct_construction_log_meta_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ct_construction_log_meta_nonce'] ) ), 'ct_save_construction_log_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['ct_entry_date'] ) ) {
		update_post_meta( $post_id, 'entry_date', sanitize_text_field( wp_unslash( $_POST['ct_entry_date'] ) ) );
	}

	if ( isset( $_POST['ct_category'] ) ) {
		$allowed_categories = array( 'materials', 'payroll', 'permits', 'hauling', 'equipment' );
		$category           = sanitize_text_field( wp_unslash( $_POST['ct_category'] ) );

		if ( in_array( $category, $allowed_categories, true ) ) {
			update_post_meta( $post_id, 'category', $category );
		} else {
			delete_post_meta( $post_id, 'category' );
		}
	}

	if ( isset( $_POST['ct_amount'] ) ) {
		update_post_meta( $post_id, 'amount', floatval( wp_unslash( $_POST['ct_amount'] ) ) );
	}

	if ( isset( $_POST['ct_notes'] ) ) {
		update_post_meta( $post_id, 'notes', sanitize_textarea_field( wp_unslash( $_POST['ct_notes'] ) ) );
	}
}
add_action( 'save_post_construction_log', 'ct_save_construction_log_meta' );

/**
 * Register the `wp-tracker/v1/logs` REST API route.
 */
function ct_register_rest_routes() {
	register_rest_route(
		'wp-tracker/v1',
		'/logs',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'ct_get_construction_logs',
			'permission_callback' => '__return_true',
		)
	);

	register_rest_route(
		'wp-tracker/v1',
		'/articles',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'ct_get_help_articles',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'ct_register_rest_routes' );

/**
 * Callback for GET wp-tracker/v1/logs.
 *
 * Returns all published construction_log entries with their meta fields.
 *
 * @param WP_REST_Request $request Full request object.
 * @return WP_REST_Response
 */
function ct_get_construction_logs( $request ) {
	$posts = get_posts(
		array(
			'post_type'      => 'construction_log',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);

	$logs = array();

	foreach ( $posts as $post ) {
		$logs[] = array(
			'id'         => $post->ID,
			'title'      => get_the_title( $post ),
			'entry_date' => get_post_meta( $post->ID, 'entry_date', true ),
			'category'   => get_post_meta( $post->ID, 'category', true ),
			'amount'     => (float) get_post_meta( $post->ID, 'amount', true ),
			'notes'      => get_post_meta( $post->ID, 'notes', true ),
		);
	}

	return new WP_REST_Response( $logs, 200 );
}

/**
 * Callback for GET wp-tracker/v1/articles.
 *
 * Returns all published help_article entries as clean JSON.
 *
 * @param WP_REST_Request $request Full request object.
 * @return WP_REST_Response
 */
function ct_get_help_articles( $request ) {
	$posts = get_posts(
		array(
			'post_type'      => 'help_article',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);

	$articles = array();

	foreach ( $posts as $post ) {
		$articles[] = array(
			'id'             => $post->ID,
			'title'          => get_the_title( $post ),
			'content'        => apply_filters( 'the_content', $post->post_content ),
			'slug'           => $post->post_name,
			'featured_image' => get_the_post_thumbnail_url( $post ),
		);
	}

	return new WP_REST_Response( $articles, 200 );
}
