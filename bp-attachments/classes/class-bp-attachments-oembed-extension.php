<?php
/**
 * BP Attachments oEmbed handler.
 *
 * @package \bp-attachments\classes\class-bp-attachments-oembed-extension
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BP Attachments oEmbed handler to respond and render user media.
 *
 * @since 1.0.0
 */
class BP_Attachments_OEmbed_Extension extends BP_Core_oEmbed_Extension {

	/**
	 * The current medium being embedded.
	 *
	 * @since 1.0.0
	 *
	 * @var BP_Medium|false
	 */
	protected $current_medium = false;

	/**
	 * Custom oEmbed slug endpoint.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $slug_endpoint = 'bp-attachments';

	/**
	 * Output our custom embed template part.
	 *
	 * @since 1.0.0
	 */
	protected function content() {
		// Temporarly overrides the BuddyPress Template Stack.
		bp_attachments_start_overriding_template_stack();

		bp_get_asset_template_part( 'embeds/content', 'attachments' );

		// Stop overriding the BuddyPress Template Stack.
		bp_attachments_stop_overriding_template_stack();
	}

	/**
	 * Includes Attachments template stack before ending the object buffer.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name Template name.
	 */
	public function content_buffer_end( $name ) {
		// Temporarly overrides the BuddyPress Template Stack.
		bp_attachments_start_overriding_template_stack();

		parent::content_buffer_end( $name );

		// Stop overriding the BuddyPress Template Stack.
		bp_attachments_stop_overriding_template_stack();
	}

	/**
	 * Sets a custom <iframe> title for our oEmbed medium.
	 *
	 * @since 1.0.0
	 *
	 * @param  int $item_id The Medium ID.
	 * @return string The custom <iframe> title for our oEmbed medium.
	 */
	protected function set_iframe_title( $item_id ) {
		return __( 'Embedded Community Media', 'bp-attachments' );
	}

	/**
	 * Set permalink for oEmbed link discovery.
	 *
	 * @since 1.0.0
	 *
	 * @return string the oEmbed url to use for the Medium.
	 */
	protected function set_permalink() {
		$url = parent::set_permalink();

		if ( false === $this->current_medium ) {
			$bp = buddypress();

			if ( isset( $bp->attachments->queried_object ) && $bp->attachments->queried_object instanceof BP_Medium ) {
				$medium = $bp->attachments->queried_object;
			}
		} else {
			$medium = $this->current_medium;
		}

		if ( isset( $medium->links['view'] ) ) {
			$url = $medium->links['view'];
		}

		return $url;
	}

	/**
	 * Check if we're on our single medium page.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	protected function is_page() {
		$is_page = bp_attachments_is_medium_view();

		if ( 'embed_template' === current_filter() || 'bp_embed_content' === current_action() ) {
			$is_page = bp_attachments_is_medium_embed();

			// Make sure our custom permalink shows up in the 'WordPress Embed' block.
			add_filter( 'the_permalink', array( $this, 'filter_the_permalink' ) );
		}

		return $is_page;
	}

	/**
	 * Validates the URL to determine if the medium is valid.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $url The URL to check.
	 * @return string|bool Relative path on success; boolean false on failure.
	 */
	protected function validate_url_to_item_id( $url ) {
		global $wp_query;
		$bp       = buddypress();
		$url_path = trim( wp_parse_url( $url, PHP_URL_PATH ), '/' );

		if ( 0 !== strpos( $url_path, $bp->attachments->root_slug ) ) {
			return false;
		}

		$url_chunks = explode( '/', $url_path );

		// Check component's root slug.
		$root_slug = array_shift( $url_chunks );
		if ( $root_slug !== $bp->attachments->root_slug ) {
			return false;
		}

		// Check medium's visibility.
		$visibility = array_shift( $url_chunks );
		if ( ! in_array( $visibility, array( 'public', 'private' ), true ) ) {
			return false;
		}

		// Check medium's attached component ID.
		$object = array_shift( $url_chunks );
		if ( ! bp_is_active( $object ) ) {
			return false;
		}

		// Check medium's attached component's single item ID.
		$item             = array_shift( $url_chunks );
		$action           = array_shift( $url_chunks );
		$action_variables = $url_chunks;
		$item_id          = 0;

		if ( $bp->members->id === $object ) {
			$user = get_user_by( 'slug', $item );

			if ( $user && isset( $user->ID ) ) {
				$item_id = (int) $user->ID;
			}
		} else {
			$parse_array = array(
				'bp_attachments'                       => 1,
				'bp_attachments_visibility'            => $visibility,
				'bp_attachments_object'                => $object,
				'bp_attachments_object_item'           => $item,
				'bp_attachments_item_action'           => $action,
				'bp_attachments_item_action_variables' => $action_variables,
			);

			/** This filter is documented in bp-attachments/classes/class-bp-attachments-component.php */
			$item_id = apply_filters( 'bp_attachments_parse_querried_object_item', $parse_array );
		}

		if ( ! $item_id ) {
			return false;
		}

		$relative_path = array_filter(
			array_merge(
				array( $object, $item_id ),
				$action_variables
			)
		);
		$id            = array_pop( $relative_path );
		$absolute_path = trailingslashit( bp_attachments_get_media_uploads_dir( $visibility )['path'] ) . implode( '/', $relative_path );

		// Try to get the medium.
		$this->current_medium = bp_attachments_get_medium( $id, $absolute_path );

		if ( false === $this->current_medium ) {
			return false;
		}

		// `BP_Core_oEmbed_Extension::get_item()` is expecting an integer, let's use one to satisfy it.
		return $this->current_medium->last_modified;
	}

	/**
	 * Sets the oEmbed response data for our medium.
	 *
	 * @since 1.0.0
	 *
	 * @param  int $item_id The Medium ID.
	 * @return array
	 */
	protected function set_oembed_response_data( $item_id ) {
		if ( false === $this->current_medium || $this->current_medium->last_modified !== $item_id ) {
			return array();
		}

		return array(
			'content'      => $this->current_medium->description,
			'title'        => $this->current_medium->title,
			'author_name'  => bp_core_get_user_displayname( $this->current_medium->owner_id ),
			'author_url'   => bp_attachments_get_user_url( $this->current_medium->owner_id ),
			'x_buddypress' => 'attachments',
		);
	}

	/**
	 * Sets a custom <blockquote> for our oEmbed fallback HTML.
	 *
	 * @since 1.0.0
	 *
	 * @param  int $item_id The medium ID.
	 * @return string
	 */
	protected function set_fallback_html( $item_id ) {
		if ( false === $this->current_medium || $this->current_medium->last_modified !== $item_id ) {
			return '';
		}

		$medium      = $this->current_medium;
		$mentionname = bp_activity_do_mentions() ? ' (@' . bp_activity_get_user_mentionname( $medium->owner_id ) . ')' : '';
		$date        = date_i18n( get_option( 'date_format' ), $medium->last_modified );

		// Reset current medium.
		$this->current_medium = false;

		return sprintf(
			'<blockquote class="wp-embedded-content bp-attachments-medium">%s</blockquote>',
			sprintf(
				/* translators: 1. The medium title. 2. The user display name. 3. The user mention name between parenthesis. 4. The date. */
				__( '%1$s is shared by %2$s%3$s since %4$s.', 'bp-attachments' ),
				esc_html( $medium->title ),
				esc_html( bp_core_get_user_displayname( $medium->owner_id ) ),
				esc_html( $mentionname ),
				'<a href="' . esc_url( $medium->links['view'] ) . '">' . $date . '</a>'
			)
		);
	}

	/**
	 * Pass the BP Attachments medium embed URL.
	 *
	 * @since 1.0.0
	 *
	 * @param string $url Current embed URL.
	 * @return string The BP Attachments medium embed URL.
	 */
	public function filter_embed_url( $url ) {
		if ( false === $this->current_medium ) {
			$bp = buddypress();

			if ( isset( $bp->attachments->queried_object ) && $bp->attachments->queried_object instanceof BP_Medium ) {
				$url = $bp->attachments->queried_object->links['embed'];
			}
		} else {
			$url = $this->current_medium->links['embed'];
		}

		return $url;
	}

	/**
	 * Pass the BP Attachments medium permalink for embedding.
	 *
	 * @since 1.0.0
	 *
	 * @param string $url Current permalink.
	 * @return string The BP Attachments medium permalink for embedding.
	 */
	public function filter_the_permalink( $url ) {
		if ( false === $this->current_medium ) {
			$bp = buddypress();

			if ( isset( $bp->attachments->queried_object ) && $bp->attachments->queried_object instanceof BP_Medium ) {
				$url = $bp->attachments->queried_object->links['view'];
			}
		} else {
			$url = $this->current_medium->links['view'];
		}

		return $url;
	}
}
