<?php
/**
 * Plugin Name: Form Contatto DB – Studio Immens
 * Description: Blocco Gutenberg con form, salvataggio DB, dashboard, export CSV, email notifiche.
 * Version: 1.0
 * Author: Studio Immens
 */

if (!defined('ABSPATH')) exit;

define('FSDB_DIR', plugin_dir_path(__FILE__));
define('FSDB_URL', plugin_dir_url(__FILE__));

require_once FSDB_DIR . 'includes/setup.php';
require_once FSDB_DIR . 'includes/block.php';
require_once FSDB_DIR . 'includes/api.php';
require_once FSDB_DIR . 'includes/admin.php';


