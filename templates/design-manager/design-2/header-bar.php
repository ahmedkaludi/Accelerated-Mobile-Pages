<header class="container">
  <div id="headerwrap">
      <div id="header">

        <?php global $redux_builder_amp;

          if ($redux_builder_amp['amp-on-off-support-for-non-amp-home-page']) {
            $ampforwp_home_url = untrailingslashit( get_bloginfo('url') ).'?nonamp=1';
          }else{
            $ampforwp_home_url = trailingslashit( get_bloginfo('url') ) . AMP_QUERY_VAR;
          }
        ?>

        <?php if ( true == ($redux_builder_amp['opt-media']['url']) ) {  ?>
          <a href="<?php echo esc_url( $ampforwp_home_url ); ?>">
            <span class="header-logo-center">
              <amp-img src="https://technutty.co.uk/wp-content/assets/TechNuttyLogo.svg" width="220" height="35" alt="logo" class="amp-logo" layout=responsive id="AMP_1">
              </amp-img>
            </spam>
          </a>
        <?php } else { ?>
          <h3><a href="<?php echo esc_url( $ampforwp_home_url ); ?>"><?php bloginfo('name'); ?></a></h3>
        <?php } ?>

      </div>
  </div>
</header>

<amp-install-serviceworker
       src="https://technutty.co.uk/sw.js"
       data-iframe-src="https://<?php echo $_SERVER['SERVER_NAME']; ?>/"
       layout="nodisplay">
     </amp-install-serviceworker>

<!-- <div on='tap:sidebar.toggle' role="button" tabindex="0" class="nav_container">
	<a href="#" class="toggle-text"><?php echo esc_html( $redux_builder_amp['amp-translator-navigate-text'] ); ?></a>
</div>

<amp-sidebar id='sidebar'
    layout="nodisplay"
    side="right">
  <div class="toggle-navigationv2">
      <div role="button" tabindex="0" on='tap:sidebar.close' class="close-nav">X</div>
      <?php wp_nav_menu( array( 'theme_location' => 'amp-menu' ) ); ?>
  </div>
</amp-sidebar> -->
