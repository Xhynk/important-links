<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
	<h2 style="display: none;">Third River Marketing: Important Links</h2>
	<div id="trm-links-list" class="trm-links-admin-panel">
		<div class="container">
			<nav class="menu">
				<?php if( $links = get_option( '_trm_important_links' ) ){
					$count = 0;
					foreach( $links as $atts ){ ?>
						<a data-id="<?php echo esc_attr( $atts['id'] ); ?>" <?php echo $count == 0 ? 'class="active"' : ''; ?> href="#" data-attr="link-<?php echo $count; ?>"><?php echo $this->display_svg( 'link' ); ?> <span><?php echo esc_attr( stripslashes( $atts['label'] ) ); ?></span></a>
					<?php $count++; }
				} else {
					echo '<p class="no-links" href="#">No Links - Add One Below!</p>';
				} ?>
				<a href="#" class="collapse"><?php echo $this->display_svg( 'arrow-left-circle' ); ?> <span>Collapse</span></a>
				<a href="#" class="add" title="Add New Link"><?php echo $this->display_svg( 'file-plus' ); ?> <span></span></a>
			</nav>
			<main class="content">
				<?php if( $links = get_option( '_trm_important_links' ) ){
					$count = 0;
					foreach( $links as $atts ){ ?>
						<div data-id="<?php echo esc_attr( $atts['id'] ); ?>" class="link-<?php echo $count; ?> <?php echo $count == 0 ? 'active' : ''; ?>">
							<?php if( is_ssl() ) $atts['url'] = str_replace( 'http://', 'https://', $atts['url'] ); ?>
							<iframe <?php echo $count == 0 ? '' : 'data-';?>src="<?php echo esc_url( $atts['url'] ); ?>"></iframe>
							<nav class="lower-menu">
								<?php if( $atts['login_url'] ) echo '<a href="'. esc_url( $atts['login_url'] ) .'" target="_blank">Login</a>'; ?>
								<a href="<?php echo esc_url( $atts['url'] ); ?>" target="_blank">Open in a New Tab</a>
								<a href="#" class="remove" data-id="<?php echo esc_attr( $atts['id'] ); ?>" title="Remove This Link"><?php echo $this->display_svg( 'file-minus' ); ?></a>
							</nav>
						</div>
					<?php $count++; }
				} ?>
			</main>
			<div id="add-new" style="display: none;">
				<h4>Add New Link</h4>
				<form id="add-new-link" method="post" autocomplete="off">
					<input type="hidden" autocomplete="false" />
					<?php wp_nonce_field( 'update_option', 'add_new_account' ); ?>
					<input type="url" name="url" placeholder="Website URL" />
					<input type="url" name="login_url" placeholder="Login URL (Optional)" />
					<input type="text" name="label" placeholder="Label" />
					<button type="submit" name="submit" value="submit"><?php echo $this->display_svg( 'plus-circle' ); ?></button>
				</form>
			</div>
		</div>
	</div>
</div>