<?php

/**
 * BuddyPress - Activity Post Form
 *
 * @package BuddyPress
 * @subpackage bp-legacy
 */

?>
<form action="<?php bp_activity_post_form_action(); ?>" method="post" id="whats-new-form" name="whats-new-form" role="complementary">

	<?php

	/**
	 * Fires before the activity post form.
	 *
	 * @since BuddyPress (1.2.0)
	 */
	do_action( 'bp_before_activity_post_form' ); ?>

	<div id="whats-new-avatar">
		<a href="<?php echo bp_loggedin_user_domain(); ?>">
			<?php bp_loggedin_user_avatar( 'width=' . bp_core_avatar_thumb_width() . '&height=' . bp_core_avatar_thumb_height() ); ?>
		</a>
	</div>

	<div id="whats-new-content">

		<div id="whats-new-textarea">
			<textarea class="bp-suggestions" name="whats-new" id="whats-new" cols="50" rows="1"
				<?php if ( bp_is_group() ) : ?>data-suggestions-group-id="<?php echo esc_attr( (int) bp_get_current_group_id() ); ?>" <?php endif; ?>
			placeholder="<?php echo esc_attr( bp_get_activity_whats_new_placeholder() ) ;?>"><?php if ( isset( $_GET['r'] ) ) : ?>@<?php echo esc_textarea( $_GET['r'] ); ?> <?php endif; ?></textarea>
		</div>

		<div id="whats-new-actions">
			<?php
			/**
			 * Fires before the activity post form options.
			 *
			 * @since BuddyPress (2.4.0)
			 */
			do_action( 'bp_activity_post_form_before_options' ); ?>

			<div id="whats-new-options">

				<?php if ( bp_is_active( 'groups' ) && !bp_is_my_profile() && !bp_is_group() ) : ?>

					<div id="whats-new-post-in-box">

						<?php _e( 'Post in', 'buddypress' ); ?>:

						<select id="whats-new-post-in" name="whats-new-post-in">
							<option selected="selected" value="0"><?php _e( 'My Profile', 'buddypress' ); ?></option>

							<?php if ( bp_has_groups( 'user_id=' . bp_loggedin_user_id() . '&type=alphabetical&max=100&per_page=100&populate_extras=0&update_meta_cache=0' ) ) :
								while ( bp_groups() ) : bp_the_group(); ?>

									<option value="<?php bp_group_id(); ?>"><?php bp_group_name(); ?></option>

								<?php endwhile;
							endif; ?>

						</select>
					</div>
					<input type="hidden" id="whats-new-post-object" name="whats-new-post-object" value="groups" />

				<?php elseif ( bp_is_group_activity() ) : ?>

					<input type="hidden" id="whats-new-post-object" name="whats-new-post-object" value="groups" />
					<input type="hidden" id="whats-new-post-in" name="whats-new-post-in" value="<?php bp_group_id(); ?>" />

				<?php endif; ?>

				<?php

				/**
				 * Fires at the end of the activity post form options.
				 *
				 * @since BuddyPress (1.2.0)
				 */
				do_action( 'bp_activity_post_form_options' ); ?>

			</div><!-- #whats-new-options -->

			<?php
			/**
			 * Fires before the activity post form submit.
			 *
			 * @since BuddyPress (2.4.0)
			 */
			do_action( 'bp_activity_post_form_before_submit' ); ?>

			<div id="whats-new-submit">

				<input type="reset" name="aw-whats-new-reset" id="aw-whats-new-reset" value="<?php esc_attr_e( 'Cancel', 'buddypress' ); ?>" />
				<input type="submit" name="aw-whats-new-submit" id="aw-whats-new-submit" value="<?php esc_attr_e( 'Post Update', 'buddypress' ); ?>" />

				 <?php
				/**
				 * Fires at the end of the activity post form submit.
				 *
				 * @since BuddyPress (2.4.0)
				 */
				do_action( 'bp_activity_post_form_submit' ); ?>
			</div><!-- #whats-new-submit -->
		</div><!-- #whats-new-actions -->
	</div><!-- #whats-new-content -->

	<?php wp_nonce_field( 'post_update', '_wpnonce_post_update' ); ?>
	<?php

	/**
	 * Fires after the activity post form.
	 *
	 * @since BuddyPress (1.2.0)
	 */
	do_action( 'bp_after_activity_post_form' ); ?>

</form><!-- #whats-new-form -->
