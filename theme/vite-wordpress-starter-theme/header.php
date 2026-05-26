<!DOCTYPE HTML>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="profile" href="http://gmpg.org/xfn/11">
  <?php wp_head(); ?>
</head>

<?php $header = [
  'logo' => get_field('logo', 'options'),
  'header_button' => get_field('header_button', 'options'),
];
?>

<body <?php body_class(); ?>>
  <?php wp_body_open(); ?>
  <div id="page" class="site">

    <header class="header" id="header">
      <div class="header__wrap">
        <div class="header__inner">
          <?php if (!empty($header['logo'])) : ?>
            <a href="<?php echo esc_url(home_url('/')); ?>" class="logo">
              <?php echo wp_get_attachment_image($header['logo'], 'full'); ?>
            </a>
          <?php endif; ?>
          <nav class="nav" id="mainNav" aria-label="<?php esc_attr_e('Main navigation', 'textdomaintomodify'); ?>">
            <?php
            wp_nav_menu(
              [
                'theme_location' => 'menu-main',
                'menu_id'        => 'menu-main',
                'container'      => false,
                'fallback_cb'    => false,
              ]
            );
            ?>
          </nav>
          <?php if (!empty($header['header_button'])) : ?>
            <a href="<?php echo esc_url($header['header_button']['url']); ?>" class="btn btn--primary btn--sm header__cta">
              <?php echo esc_html($header['header_button']['title']); ?>
            </a>
          <?php endif; ?>
          <button class="burger" id="burger" aria-label="<?php esc_attr_e('Р’С–РґРєСЂРёС‚Рё РјРµРЅСЋ', 'textdomaintomodify'); ?>" aria-expanded="false" aria-controls="mainNav">
            <span></span><span></span><span></span>
          </button>
        </div>
      </div>
    </header>
    
    <div id="content" class="site-content">