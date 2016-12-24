<header class="container">
  <div id="headerwrap">
      <div id="header">

        <?php global $redux_builder_amp;

          if ($redux_builder_amp['amp-on-off-support-for-non-amp-home-page']) {
            $ampforwp_home_url = trailingslashit( get_bloginfo('url') );
          }else{
            $ampforwp_home_url = trailingslashit( get_bloginfo('url') ) . '?' . AMP_QUERY_VAR;
          }
        ?>

        <?php if ( true == ($redux_builder_amp['opt-media']['url']) ) {  ?>
          <a href="<?php echo esc_url( $ampforwp_home_url ); ?>">
              <amp-img src="https://technutty.co.uk/TechNuttyAmpLogo.png" width="600" height="60" alt="logo" class="amp-logo -amp-element -amp-layout-fixed -amp-layout-size-defined -amp-layout" id="AMP_1">
              </amp-img>
          </a>
        <?php } else { ?>
          <h1><a href="<?php echo esc_url( $ampforwp_home_url ); ?>"><?php bloginfo('name'); ?></a></h1>
        <?php } ?>

      </div>
  </div>
</header>

<amp-install-serviceworker
       src="sw.js"
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
