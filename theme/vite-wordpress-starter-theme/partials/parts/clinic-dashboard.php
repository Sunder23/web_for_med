<?php
/**
 * Decorative "clinic overview" dashboard on the 45px grid (Figma 3537:2570, panel 3537:2617).
 *
 * Pure decoration (aria-hidden): the numbers are fixed in this file, not content.
 * The stage is drawn at its design size (1350×540) and scaled to the column width
 * through `--dashboard-scale` (assets/src/js/components/dashboardScale.js); the
 * bars, source bars and KPI numbers animate in once on scroll
 * (assets/src/js/components/dashboardReveal.js).
 *
 * @package Vite_Starter
 */

$dashboard_menu = array(
	__( 'Огляд', 'vite-starter' ),
	__( 'Звернення', 'vite-starter' ),
	__( 'Джерела пацієнтів', 'vite-starter' ),
	__( 'Аналітика', 'vite-starter' ),
	__( 'Комунікації', 'vite-starter' ),
);

// [ label, value, number prefix, delta text ].
$dashboard_kpis = array(
	array( __( 'Звернення', 'vite-starter' ), 248, '', __( '↑ 18% до минулого періоду', 'vite-starter' ) ),
	array( __( 'Записи на прийом', 'vite-starter' ), 176, '', __( '↑ 12% до минулого періоду', 'vite-starter' ) ),
	array( __( 'Вартість звернення', 'vite-starter' ), 184, '₴ ', __( '↓ 9% до минулого періоду', 'vite-starter' ) ),
);

// Bar heights in px of the 168px chart (Figma «Звернення · день 1…18»); the last four are solid.
$dashboard_bars   = array( 22.4, 29.6, 24.8, 39.2, 35.2, 45.6, 40.8, 50.4, 58.4, 55.2, 64, 74.4, 69.6, 86.4, 81.6, 95.2, 88, 100.8 );
$dashboard_accent = 14;

// [ label, share in %, filled part of the 298px track (Figma: 210 / 160 / 88px) ].
$dashboard_sources = array(
	array( __( 'Пошук Google', 'vite-starter' ), 46, 0.705 ),
	array( __( 'Реклама Google', 'vite-starter' ), 35, 0.537 ),
	array( __( 'Прямі переходи', 'vite-starter' ), 19, 0.295 ),
);
?>
<div class="clinic-dashboard" data-clinic-dashboard aria-hidden="true">
	<div class="clinic-dashboard__stage grid-lines">
		<div class="clinic-dashboard__panel">
			<p class="clinic-dashboard__brand"><?php esc_html_e( 'Клініка · огляд', 'vite-starter' ); ?></p>
			<p class="clinic-dashboard__period"><?php esc_html_e( 'Останні 30 днів', 'vite-starter' ); ?></p>

			<ul class="clinic-dashboard__menu">
				<?php foreach ( $dashboard_menu as $menu_index => $menu_label ) : ?>
					<li class="clinic-dashboard__menu-item<?php echo 0 === $menu_index ? ' clinic-dashboard__menu-item--current' : ''; ?>"><?php echo esc_html( $menu_label ); ?></li>
				<?php endforeach; ?>
			</ul>

			<p class="clinic-dashboard__title"><?php esc_html_e( 'Ефективність залучення пацієнтів', 'vite-starter' ); ?></p>

			<div class="clinic-dashboard__kpis">
				<?php foreach ( $dashboard_kpis as $kpi ) : ?>
					<div class="clinic-dashboard__kpi">
						<p class="clinic-dashboard__kpi-label"><?php echo esc_html( $kpi[0] ); ?></p>
						<p class="clinic-dashboard__kpi-value" data-count-to="<?php echo esc_attr( (string) $kpi[1] ); ?>" data-prefix="<?php echo esc_attr( $kpi[2] ); ?>"><?php echo esc_html( $kpi[2] . $kpi[1] ); ?></p>
						<p class="clinic-dashboard__kpi-delta"><?php echo esc_html( $kpi[3] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="clinic-dashboard__chart">
				<p class="clinic-dashboard__heading"><?php esc_html_e( 'Динаміка звернень', 'vite-starter' ); ?></p>
				<div class="clinic-dashboard__bars">
					<?php foreach ( $dashboard_bars as $bar_index => $bar_height ) : ?>
						<span class="clinic-dashboard__bar<?php echo $bar_index >= $dashboard_accent ? ' clinic-dashboard__bar--accent' : ''; ?>" style="<?php echo esc_attr( '--h: ' . $bar_height . 'px; --i: ' . $bar_index ); ?>"></span>
					<?php endforeach; ?>
				</div>
				<p class="clinic-dashboard__axis">
					<span><?php esc_html_e( 'Початок періоду', 'vite-starter' ); ?></span>
					<span><?php esc_html_e( 'Сьогодні', 'vite-starter' ); ?></span>
				</p>
			</div>

			<div class="clinic-dashboard__sources">
				<p class="clinic-dashboard__heading"><?php esc_html_e( 'Звідки приходять пацієнти', 'vite-starter' ); ?></p>
				<?php foreach ( $dashboard_sources as $source_index => $source ) : ?>
					<div class="clinic-dashboard__source">
						<span class="clinic-dashboard__source-label"><?php echo esc_html( $source[0] ); ?></span>
						<span class="clinic-dashboard__source-value"><?php echo esc_html( $source[1] . '%' ); ?></span>
						<span class="clinic-dashboard__source-track">
							<span class="clinic-dashboard__source-fill" style="<?php echo esc_attr( '--fill: ' . $source[2] . '; --i: ' . $source_index ); ?>"></span>
						</span>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</div>
