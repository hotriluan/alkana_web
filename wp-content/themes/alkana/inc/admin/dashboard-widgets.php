<?php
/**
 * Alkana Dashboard 2.0 — Widget render functions.
 * Loaded by dashboard.php; each function is registered as a wp_add_dashboard_widget callback.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

// ── Welcome Card ─────────────────────────────────────────────────────────────

function alkana_render_welcome_card(): void {
	$user       = wp_get_current_user();
	$role_map   = [
		'administrator'  => 'Quản trị viên',
		'editor'         => 'Biên tập viên',
		'alkana_tech'    => 'Kỹ thuật viên',
		'alkana_content' => 'Biên tập nội dung',
	];
	$role_label = '';
	foreach ( (array) ( $user->roles ?? [] ) as $r ) {
		$role_label = $role_map[ $r ] ?? ucfirst( $r );
		break;
	}
	$now = wp_date( 'd F Y, H:i', null, wp_timezone() );
	?>
	<div style="display:flex;align-items:center;gap:16px;padding:6px 0;">
		<div style="background:linear-gradient(135deg,#4C0682,#8236BC);width:52px;height:52px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:24px;">👤</div>
		<div style="min-width:0;">
			<h2 style="margin:0;font-size:17px;font-weight:700;color:#4C0682;">Chào mừng, <?php echo esc_html( $user->display_name ); ?>! 👋</h2>
			<p style="margin:4px 0 0;font-size:12px;color:#777;"><?php echo esc_html( $role_label ); ?> &nbsp;·&nbsp; <?php echo esc_html( $now ); ?></p>
		</div>
		<div style="margin-left:auto;flex-shrink:0;">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener" style="background:#f0e8f8;color:#67219D;padding:8px 14px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:600;white-space:nowrap;">Xem website →</a>
		</div>
	</div>
	<?php
}

// ── Stats Cards ───────────────────────────────────────────────────────────────

function alkana_render_stats_cards(): void {
	$stats = [
		[ 'label' => 'Sản phẩm',      'icon' => '🎨', 'color' => '#67219D', 'count' => (int) ( wp_count_posts( 'alkana_product' )->publish     ?? 0 ), 'link' => admin_url( 'edit.php?post_type=alkana_product' ) ],
		[ 'label' => 'Dự án',          'icon' => '🏗️', 'color' => '#8236BC', 'count' => (int) ( wp_count_posts( 'alkana_project' )->publish     ?? 0 ), 'link' => admin_url( 'edit.php?post_type=alkana_project' ) ],
		[ 'label' => 'Việc làm',       'icon' => '💼', 'color' => '#4C0682', 'count' => (int) ( wp_count_posts( 'alkana_job' )->publish         ?? 0 ), 'link' => admin_url( 'edit.php?post_type=alkana_job' ) ],
		[ 'label' => 'Đơn ứng tuyển', 'icon' => '📩', 'color' => '#B87EDD', 'count' => (int) ( wp_count_posts( 'alkana_application' )->publish ?? 0 ), 'link' => admin_url( 'edit.php?post_type=alkana_application' ) ],
	];
	echo '<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;">';
	foreach ( $stats as $s ) {
		printf(
			'<a href="%s" style="display:block;background:#fff;border:1px solid #e8d5f5;border-left:4px solid %s;border-radius:10px;padding:14px;text-decoration:none;">
				<div style="font-size:22px;margin-bottom:6px;">%s</div>
				<div style="font-size:26px;font-weight:700;color:%s;">%s</div>
				<div style="font-size:11px;color:#888;margin-top:3px;">%s</div>
			</a>',
			esc_url( $s['link'] ),
			esc_attr( $s['color'] ),
			$s['icon'], // phpcs:ignore -- static emoji
			esc_attr( $s['color'] ),
			esc_html( (string) $s['count'] ),
			esc_html( $s['label'] )
		);
	}
	echo '</div>';
}

// ── Quick Actions ─────────────────────────────────────────────────────────────

function alkana_render_quick_actions(): void {
	$actions = [
		[ 'icon' => 'dashicons-plus-alt2',       'color' => '#67219D', 'label' => 'Thêm Sản phẩm',  'url' => admin_url( 'post-new.php?post_type=alkana_product' ) ],
		[ 'icon' => 'dashicons-plus-alt2',       'color' => '#8236BC', 'label' => 'Thêm Dự án',     'url' => admin_url( 'post-new.php?post_type=alkana_project' ) ],
		[ 'icon' => 'dashicons-edit',            'color' => '#4C0682', 'label' => 'Sửa Trang chủ',  'url' => admin_url( 'post.php?post=' . (int) get_option( 'page_on_front' ) . '&action=edit' ) ],
		[ 'icon' => 'dashicons-admin-appearance','color' => '#B87EDD', 'label' => 'Paint Builder',   'url' => admin_url( 'admin.php?page=alkana-paint-builder' ) ],
		[ 'icon' => 'dashicons-tag',             'color' => '#67219D', 'label' => 'Danh mục SP',     'url' => admin_url( 'edit-tags.php?taxonomy=product_category&post_type=alkana_product' ) ],
		[ 'icon' => 'dashicons-admin-post',      'color' => '#8236BC', 'label' => 'Thêm Bài viết',  'url' => admin_url( 'post-new.php' ) ],
		[ 'icon' => 'dashicons-admin-generic',   'color' => '#4C0682', 'label' => 'Cài đặt',        'url' => admin_url( 'admin.php?page=alkana-settings' ) ],
		[ 'icon' => 'dashicons-visibility',      'color' => '#B87EDD', 'label' => 'Xem website',    'url' => home_url( '/' ), 'external' => true ],
	];
	echo '<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;">';
	foreach ( $actions as $a ) {
		$target = ! empty( $a['external'] ) ? ' target="_blank" rel="noopener"' : '';
		printf(
			'<a href="%s"%s style="display:flex;flex-direction:column;align-items:center;gap:6px;padding:14px 8px;background:#f8f2fd;border-radius:10px;text-decoration:none;border:1px solid #e8d5f5;">
				<span class="dashicons %s" style="color:%s;font-size:22px;width:22px;height:22px;"></span>
				<span style="font-size:11px;font-weight:600;color:#4C0682;text-align:center;line-height:1.3;">%s</span>
			</a>',
			esc_url( $a['url'] ),
			$target,
			esc_attr( $a['icon'] ),
			esc_attr( $a['color'] ),
			esc_html( $a['label'] )
		);
	}
	echo '</div>';
}

// ── Charts Widget ─────────────────────────────────────────────────────────────

function alkana_render_charts_widget(): void {
	global $wpdb;

	// Products by category
	$terms      = get_terms( [ 'taxonomy' => 'product_category', 'hide_empty' => true, 'number' => 8 ] );
	$cat_labels = [];
	$cat_counts = [];
	foreach ( is_array( $terms ) ? $terms : [] as $term ) {
		$cat_labels[] = $term->name;
		$cat_counts[] = (int) $term->count;
	}
	if ( empty( $cat_labels ) ) {
		$cat_labels = [ 'Chưa có danh mục' ];
		$cat_counts = [ 0 ];
	}

	// Monthly activity — last 6 months
	$m_labels = [];
	$m_counts = [];
	$abbr     = [ 'T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12' ];
	for ( $i = 5; $i >= 0; $i-- ) {
		$dt = new DateTime( 'now', wp_timezone() );
		$dt->modify( "-{$i} months" );
		$m  = (int) $dt->format( 'n' );
		$y  = (int) $dt->format( 'Y' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$count = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			 WHERE post_status = 'publish' AND YEAR(post_date) = %d AND MONTH(post_date) = %d
			 AND post_type NOT IN ('attachment','revision','nav_menu_item','wp_navigation')",
			$y, $m
		) );
		$m_labels[] = $abbr[ $m - 1 ] . '/' . $dt->format( 'y' );
		$m_counts[] = $count;
	}

	$palette = [ '#67219D', '#8236BC', '#B87EDD', '#4C0682', '#9B59C8', '#D4A7ED', '#3B0670', '#C084FC' ];
	?>
	<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
		<div>
			<p style="font-size:11px;font-weight:600;text-transform:uppercase;color:#67219D;margin:0 0 8px;letter-spacing:.5px;">Sản phẩm theo danh mục</p>
			<canvas id="alk-donut-chart" height="190"></canvas>
		</div>
		<div>
			<p style="font-size:11px;font-weight:600;text-transform:uppercase;color:#67219D;margin:0 0 8px;letter-spacing:.5px;">Bài đăng 6 tháng gần đây</p>
			<canvas id="alk-line-chart" height="190"></canvas>
		</div>
	</div>
	<script>
	(function() {
		if (typeof Chart === 'undefined') return;
		const cats   = <?php echo wp_json_encode( $cat_labels ); ?>;
		const catV   = <?php echo wp_json_encode( $cat_counts ); ?>;
		const months = <?php echo wp_json_encode( $m_labels ); ?>;
		const monthV = <?php echo wp_json_encode( $m_counts ); ?>;
		const pal    = <?php echo wp_json_encode( $palette ); ?>;
		const font   = { family: 'Inter, -apple-system, sans-serif', size: 11 };
		new Chart(document.getElementById('alk-donut-chart'), {
			type: 'doughnut',
			data: { labels: cats, datasets: [{ data: catV, backgroundColor: pal, borderWidth: 0 }] },
			options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { font, color: '#555', padding: 10 } } } }
		});
		new Chart(document.getElementById('alk-line-chart'), {
			type: 'line',
			data: { labels: months, datasets: [{ data: monthV, borderColor: '#67219D', backgroundColor: 'rgba(103,33,157,0.08)', fill: true, tension: 0.4, pointBackgroundColor: '#67219D', pointRadius: 4 }] },
			options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0, font, color: '#888' } }, x: { ticks: { font, color: '#888' } } } }
		});
	})();
	</script>
	<?php
}

// ── Activity Feed ─────────────────────────────────────────────────────────────

function alkana_render_activity_feed(): void {
	$posts = get_posts( [
		'posts_per_page' => 10,
		'post_status'    => [ 'publish', 'draft', 'pending' ],
		'post_type'      => [ 'post', 'page', 'alkana_product', 'alkana_project', 'alkana_job' ],
		'orderby'        => 'modified',
		'order'          => 'DESC',
	] );

	if ( empty( $posts ) ) {
		echo '<p style="color:#888;font-size:13px;">Chưa có hoạt động nào.</p>';
		return;
	}

	$dot = [
		'publish' => '<span style="color:#22c55e;">●</span>',
		'draft'   => '<span style="color:#f59e0b;">●</span>',
		'pending' => '<span style="color:#3b82f6;">●</span>',
	];
	$type_label = [
		'post'           => 'Bài viết',
		'page'           => 'Trang',
		'alkana_product' => 'Sản phẩm',
		'alkana_project' => 'Dự án',
		'alkana_job'     => 'Việc làm',
	];

	echo '<ul style="margin:0;padding:0;list-style:none;">';
	foreach ( $posts as $p ) {
		$author   = get_the_author_meta( 'display_name', $p->post_author );
		$type     = $type_label[ $p->post_type ] ?? $p->post_type;
		$icon     = $dot[ $p->post_status ] ?? '●';
		$ago      = human_time_diff( strtotime( $p->post_modified_gmt ), time() ) . ' trước';
		$edit_url = get_edit_post_link( $p->ID ) ?? '#';
		printf(
			'<li style="display:flex;align-items:center;gap:8px;padding:7px 0;border-bottom:1px solid #f3e8ff;font-size:12px;">
				<span style="flex:0 0 auto;font-size:10px;">%s</span>
				<span style="flex:1;min-width:0;overflow:hidden;">
					<a href="%s" style="color:#4C0682;font-weight:600;text-decoration:none;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">%s</a>
					<span style="color:#999;">%s &middot; %s</span>
				</span>
				<span style="flex:0 0 auto;color:#aaa;white-space:nowrap;">%s</span>
			</li>',
			$icon,
			esc_url( $edit_url ),
			esc_html( $p->post_title ?: '(chưa có tiêu đề)' ),
			esc_html( $type ),
			esc_html( $author ),
			esc_html( $ago )
		);
	}
	echo '</ul>';
	printf( '<p style="margin:8px 0 0;text-align:right;"><a href="%s" style="color:#67219D;font-size:12px;font-weight:600;text-decoration:none;">Xem tất cả →</a></p>', esc_url( admin_url( 'edit.php?orderby=modified&order=desc' ) ) );
}

// ── System Health ─────────────────────────────────────────────────────────────

function alkana_render_system_health(): void {
	global $wp_version;
	$lscache = class_exists( 'LiteSpeed_Cache' ) || defined( 'LSCWP_V' );
	$theme   = wp_get_theme();
	$items   = [
		[ 'label' => 'PHP',      'value' => PHP_VERSION,              'ok' => version_compare( PHP_VERSION, '8.0', '>=' ) ],
		[ 'label' => 'WordPress','value' => $wp_version,             'ok' => version_compare( $wp_version, '6.0', '>=' ) ],
		[ 'label' => 'Theme',    'value' => $theme->get( 'Version' ) ?: '2.0', 'ok' => true ],
		[ 'label' => 'LiteSpeed','value' => $lscache ? 'ON' : 'OFF', 'ok' => $lscache ],
	];
	echo '<div style="display:flex;gap:12px;flex-wrap:wrap;">';
	foreach ( $items as $item ) {
		$c = $item['ok'] ? '#22c55e' : '#f59e0b';
		printf(
			'<div style="flex:1;min-width:110px;background:#f8f2fd;border-radius:8px;padding:12px;border:1px solid #e8d5f5;">
				<div style="font-size:10px;font-weight:600;text-transform:uppercase;color:#aaa;margin-bottom:4px;">%s</div>
				<div style="font-size:15px;font-weight:700;color:%s;">%s</div>
				<div style="font-size:11px;color:%s;margin-top:2px;">%s</div>
			</div>',
			esc_html( $item['label'] ),
			esc_attr( $c ),
			esc_html( $item['value'] ),
			esc_attr( $c ),
			$item['ok'] ? '✓ OK' : '⚠ Kiểm tra'
		);
	}
	echo '</div>';
}
