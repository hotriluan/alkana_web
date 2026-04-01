<?php
/**
 * archive-alkana_project.php — WordPress template hierarchy entry point.
 *
 * WordPress looks for this file at the theme root for the /projects/ archive.
 * Delegates to the full template in templates/ to keep logic centralised.
 *
 * @package Alkana
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'templates/page-projects' );
