<?php
/**
 * BuddyPress Attachments browse template
 *
 * @since 1.1.0
 *
 * @package BP Attachments
 */
?>

<script id="tmpl-bp-attachments-feedback" type="text/html">
	<# if ( data.message ) { #>
		{{data.message}}
	<# } #>
</script>

<script id="tmpl-bp-attachments-file" type="text/html">
	<# if ( data.uploading ) { #>
		<div id="{{data.id}}" class="bp-attachments-downloading" style="width:<?php echo bp_core_avatar_full_width();?>px;">
			<div class="bp-progress" style="margin:<?php echo ( bp_core_avatar_full_height() - 10 ) / 2; ?>px auto">
				<div class="bp-bar"></div>
			</div>
		</div>
	<# } else if ( 'image' === data.type && data.sizes && data.sizes.bp_attachments_avatar ) { #>
		<# if ( ! data.is_activity ) { #>
			<a href="{{ data.url }}" title="{{ data.title }}" class="thickbox" rel="bp-attachments-preview">
				<img class="bp-attachments-file" src="{{ data.sizes.bp_attachments_avatar.url }}" data-fileid="{{data.id}}" />
			</a>
		<# } else { #>
			<img class="bp-attachments-file" src="{{ data.sizes.bp_attachments_avatar.url }}" data-fileid="{{data.id}}" />
			<input type="hidden" name="bp_attachments_activity_meta[]" value="{{data.id}}" />
		<# } #>
	<# } else { #>
		<div class="bp-attachments-file" style="width:<?php echo bp_core_avatar_full_width();?>px;height:<?php echo bp_core_avatar_full_height(); ?>px">
			<# if ( ! data.is_activity ) { #>
				<a class="bp-attachments-icon" href="{{ data.url }}">
					<img src="{{ data.icon }}" />
				</a>
				<a class="bp-attachments-filename" href="{{ data.url }}">{{ data.title }}</a>
			<# } else { #>
				<img src="{{ data.icon }}" />
				<span class="bp-attachments-filename">{{ data.title }}</span>
				<input type="hidden" name="bp_attachments_activity_meta[]" value="{{data.id}}" />
			<# } #>
		</div>
	<# } #>

	<# if ( ! data.uploading ) { #>
		<div class="bp-attachments-actions">
			<?php if ( bp_is_user() && bp_is_current_component( 'attachments' ) ) :?>
				<# if ( data.bp_groups ) { #>
					<a class='bp-groups' title="{{ data.bp_groups }}"></a>&nbsp;
				<# } #>
				<# if ( data.nonces.update ) { #>
					<a href="{{ data.editLink }}" class="edit"></a>&nbsp;
				<# } #>
				<# if ( data.nonces.delete ) { #>
					<a href="#" class="delete"></a>
				<# } #>
			<?php elseif ( bp_is_group() && bp_is_current_action( 'attachments' ) ) :?>
				<# if ( data.owner_avatar ) { #>
					<a href="{{ data.owner_avatar.user_domain }}" title="{{ data.owner_avatar.user_title }}"><img src="{{ data.owner_avatar.user_avatar }}" width="20px" height="20px" /></a>&nbsp;
				<# } #>
				<# if ( data.nonces.update ) { #>
					<a href="{{ data.editLink }}" class="edit"></a>&nbsp;
				<# } #>
				<# if ( data.nonces.remove ) { #>
					<a href="#" class="remove"></a>
				<# } #>
			<?php else :?>
				<# if ( data.nonces.delete ) { #>
					<a href="#" class="delete"></a>
				<# } #>
			<?php endif ;?>
			<# if ( ! data.nonces.update && ! data.nonces.delete ) { #>
				&nbsp;
			<# } #>
		</div>
	<# } #>
</script>
