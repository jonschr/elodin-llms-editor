<?php
/*
	Plugin Name: Elodin llms.txt editor
	Plugin URI: https://elod.in
    Update URI: https://github.com/jonschr/elodin-llms-editor
    Description: Just another plugin
	Version: 0.1
    Author: Jon Schroeder
    Author URI: https://elod.in

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.
*/


/* Prevent direct access to the plugin */
if ( !defined( 'ABSPATH' ) ) {
    die( "Sorry, you are not allowed to access this page directly." );
}

define( 'ELODIN_LLMS_EDITOR_OPTION', 'elodin_llms_editor_content' );

add_action( 'admin_menu', 'elodin_llms_editor_add_settings_page' );
add_action( 'admin_init', 'elodin_llms_editor_register_settings' );
add_action( 'parse_request', 'elodin_llms_editor_render_llms_txt', 0 );

function elodin_llms_editor_add_settings_page() {
	add_options_page(
		'llms.txt',
		'llms.txt',
		'manage_options',
		'elodin-llms-editor',
		'elodin_llms_editor_render_settings_page'
	);
}

function elodin_llms_editor_register_settings() {
	register_setting(
		'elodin_llms_editor',
		ELODIN_LLMS_EDITOR_OPTION,
		array(
			'type'              => 'string',
			'sanitize_callback' => 'elodin_llms_editor_sanitize_content',
			'default'           => '',
		)
	);
}

function elodin_llms_editor_sanitize_content( $value ) {
	if ( ! is_string( $value ) ) {
		return '';
	}

	$value = wp_unslash( $value );
	$value = wp_check_invalid_utf8( $value );
	$value = str_replace( array( "\r\n", "\r" ), "\n", $value );

	return $value;
}

function elodin_llms_editor_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$content  = get_option( ELODIN_LLMS_EDITOR_OPTION, '' );
	$llms_url = home_url( '/llms.txt' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'llms.txt', 'elodin-llms-editor' ); ?></h1>
		<p>
			<?php esc_html_e( 'Paste the plain text or Markdown you want served at the root-level llms.txt endpoint.', 'elodin-llms-editor' ); ?>
		</p>
		<p>
			<?php esc_html_e( 'Published URL:', 'elodin-llms-editor' ); ?>
			<a href="<?php echo esc_url( $llms_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $llms_url ); ?></a>
		</p>
		<form action="options.php" method="post">
			<?php settings_fields( 'elodin_llms_editor' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<label for="elodin-llms-editor-content"><?php esc_html_e( 'llms.txt contents', 'elodin-llms-editor' ); ?></label>
					</th>
					<td>
						<textarea
							name="<?php echo esc_attr( ELODIN_LLMS_EDITOR_OPTION ); ?>"
							id="elodin-llms-editor-content"
							class="large-text code"
							rows="24"
						><?php echo esc_textarea( $content ); ?></textarea>
						<p class="description">
							<?php esc_html_e( 'Save changes here to update what bots receive from /llms.txt.', 'elodin-llms-editor' ); ?>
						</p>
					</td>
				</tr>
			</table>
			<?php submit_button( __( 'Save llms.txt', 'elodin-llms-editor' ) ); ?>
		</form>
	</div>
	<?php
}

function elodin_llms_editor_render_llms_txt() {
	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	$request_path = wp_parse_url( $request_uri, PHP_URL_PATH );
	$llms_path    = wp_parse_url( home_url( '/llms.txt' ), PHP_URL_PATH );

	if ( ! is_string( $request_path ) || ! is_string( $llms_path ) || untrailingslashit( $request_path ) !== untrailingslashit( $llms_path ) ) {
		return;
	}

	$request_method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : 'GET';

	if ( ! in_array( $request_method, array( 'GET', 'HEAD' ), true ) ) {
		status_header( 405 );
		header( 'Allow: GET, HEAD' );
		exit;
	}

	$content = get_option( ELODIN_LLMS_EDITOR_OPTION, '' );

	status_header( 200 );
	nocache_headers();
	header( 'X-Content-Type-Options: nosniff' );
	header( 'Content-Type: text/plain; charset=' . get_bloginfo( 'charset' ) );

	if ( 'HEAD' !== $request_method ) {
		echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	exit;
}

require_once __DIR__ . '/vendor/plugin-update-checker/plugin-update-checker.php';

$elodin_llms_editor_update_checker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/jonschr/elodin-llms-editor',
	__FILE__,
	'elodin-llms-editor'
);

$elodin_llms_editor_update_checker->setBranch( 'master' );
