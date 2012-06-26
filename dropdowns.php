  <nav id="access" role="navigation">
    <h3 class="assistive-text"><?php _e( 'Main menu', 'twentyeleven' ); ?></h3> <?php /*  Allow screen readers / text browsers to skip the navigation menu and get right to the good stuff. */ ?>
    <div class="skip-link"><a class="assistive-text" href="#content" title="<?php esc_attr_e( 'Skip to primary content', 'twentyeleven' ); ?>"><?php _e( 'Skip to primary content', 'twentyeleven' ); ?></a></div>
    <div class="skip-link"><a class="assistive-text" href="#secondary" title="<?php esc_attr_e( 'Skip to secondary content', 'twentyeleven' ); ?>"><?php _e( 'Skip to secondary content', 'twentyeleven' ); ?></a></div>

    <div id="navbar-menu" class="navbar">
      <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </a>
      <span class="navbar-caret" style="position:absolute;"></span>
      <div class="navbar-inner">
        <h3 class="visible-phone"><a href="<?php bloginfo('url'); ?>/"><?php bloginfo('title'); ?></a></h3>
        <?php uw_dropdowns(); ?>
      </div>
    </div>

  </nav><!-- #access -->
</header>
