<?php
/**
 * Seed alkana_job posts from career-data.php
 * Usage: wp eval-file scripts/seed-jobs.php
 */
defined('ABSPATH') || die("Run via WP-CLI: wp eval-file scripts/seed-jobs.php\n");

$data = require __DIR__ . '/dummy-data/career-data.php';

$count = 0;
foreach ($data as $job) {
    $pid = wp_insert_post([
        'post_type'    => 'alkana_job',
        'post_title'   => $job['job_title'],
        'post_content' => $job['description'],
        'post_status'  => 'publish',
    ]);
    if (is_wp_error($pid)) {
        WP_CLI::warning('Failed: ' . $job['job_title'] . ' — ' . $pid->get_error_message());
        continue;
    }
    update_post_meta($pid, 'department',      $job['department']);
    update_post_meta($pid, 'location',        $job['location']);
    update_post_meta($pid, 'employment_type', $job['employment_type']);
    update_post_meta($pid, 'deadline',        $job['deadline']);
    WP_CLI::log('Created: ' . $job['job_title'] . ' (ID ' . $pid . ')');
    $count++;
}

WP_CLI::success($count . ' job openings seeded.');
