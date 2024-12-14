<?php
/**
 * Plugin Name: AbuseIPDB WP Reporter
 * Description: Automatically reports suspicious IPs to AbuseIPDB after multiple failed attempts.
 * Version: 1.2
 * Author: Kristian Gasic @github.com/kristiangasic
 */

if (!defined('ABSPATH')) exit;

class AbuseIPDBReporter {

    private $api_key;

    public function __construct() {
        $this->api_key = get_option('abuseipdb_api_key', '');

        // Admin menu and settings page
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);

        // Create database table for logs
        register_activation_hook(__FILE__, [$this, 'create_log_table']);

        // Monitor requests
        add_action('init', [$this, 'monitor_requests']);
    }

    public function add_admin_menu() {
        add_options_page(
            'AbuseIPDB Reporter',
            'AbuseIPDB Reporter',
            'manage_options',
            'abuseipdb-reporter',
            [$this, 'settings_page']
        );
        add_menu_page(
            'AbuseIPDB Logs',
            'AbuseIPDB Logs',
            'manage_options',
            'abuseipdb-logs',
            [$this, 'logs_page'],
            'dashicons-list-view',
            100
        );
    }

    public function register_settings() {
        register_setting('abuseipdb_settings', 'abuseipdb_api_key');
    }

    public function create_log_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'abuseipdb_logs';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            ip_address VARCHAR(45) NOT NULL,
            date_reported DATETIME DEFAULT CURRENT_TIMESTAMP,
            target_uri TEXT NOT NULL,
            user_agent TEXT NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>AbuseIPDB Reporter Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('abuseipdb_settings');
                do_settings_sections('abuseipdb_settings');
                ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">AbuseIPDB API Key</th>
                        <td>
                            <input type="text" name="abuseipdb_api_key" value="<?php echo esc_attr(get_option('abuseipdb_api_key')); ?>" size="50" />
                            <p class="description">Enter your AbuseIPDB API key here. You can obtain one from <a href="https://www.abuseipdb.com" target="_blank">AbuseIPDB</a>.</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button('Save API Key'); ?>
            </form>

            <?php if (empty(get_option('abuseipdb_api_key'))) : ?>
                <div style="margin-top: 20px; padding: 10px; border: 1px solid red; color: red; background: #fdd;">
                    <strong>Warning:</strong> The API key is not set. The plugin will not work until a valid API key is provided.
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    public function logs_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'abuseipdb_logs';
        $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY date_reported DESC", ARRAY_A);
        ?>
        <div class="wrap">
            <h1>AbuseIPDB Logs</h1>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th width="20%">IP Address</th>
                        <th width="20%">Date Reported</th>
                        <th width="30%">Target URI</th>
                        <th width="30%">User Agent</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($results)) : ?>
                        <?php foreach ($results as $row) : ?>
                            <tr>
                                <td><?php echo esc_html($row['ip_address']); ?></td>
                                <td><?php echo esc_html($row['date_reported']); ?></td>
                                <td><?php echo esc_html($row['target_uri']); ?></td>
                                <td><?php echo esc_html($row['user_agent']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="4">No logs found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public function monitor_requests() {
        if (is_admin()) return;

        $ip = $_SERVER['REMOTE_ADDR'];
        $uri = $_SERVER['REQUEST_URI'];
        $referrer = $_SERVER['HTTP_REFERER'] ?? 'Unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

        // Files often targeted by bots or attackers
        $target_files = [
            'xmlrpc.php',
            'wp-admin.php',
            'wp-login.php',
            'install.php',
            'readme.html',
            '.env',
            '.git',
            'phpmyadmin',
            'wp-config.php',
            'license.txt'
        ];

        foreach ($target_files as $file) {
            if (strpos($uri, $file) !== false || http_response_code() === 503) {
                $this->log_failed_attempt($ip, $uri, $referrer, $user_agent);
                break;
            }
        }
    }

    private function log_failed_attempt($ip, $uri, $referrer, $user_agent) {
        $transient_key = 'abuseipdb_' . md5($ip);
        $attempts = get_transient($transient_key) ?: 0;
        $attempts++;

        if ($attempts >= 3) {
            $this->report_to_abuseipdb($ip, $uri, $referrer, $user_agent);
            delete_transient($transient_key); // Reset after reporting
        } else {
            set_transient($transient_key, $attempts, 3600); // 1 hour
        }
    }

    private function report_to_abuseipdb($ip, $uri, $referrer, $user_agent) {
        if (!$this->api_key) return;

        $comment = sprintf(
            'Automatic report: Suspicious activity detected.
            Target URI: %s
            Referrer: %s
            User Agent: %s',
            $uri,
            $referrer,
            $user_agent
        );

        $data = [
            'ip' => $ip,
            'categories' => '18,22,21', // Categories for brute-force, web app attacks, and reconnaissance
            'comment' => $comment
        ];

        $response = wp_remote_post('https://api.abuseipdb.com/api/v2/report', [
            'headers' => [
                'Key' => $this->api_key,
                'Accept' => 'application/json'
            ],
            'body' => $data
        ]);

        if (is_wp_error($response)) {
            error_log('AbuseIPDB Reporter Error: ' . $response->get_error_message());
        } else {
            error_log('AbuseIPDB Reporter: Reported IP - ' . $ip);
            $this->log_to_database($ip, $uri, $user_agent);
        }
    }

    private function log_to_database($ip, $uri, $user_agent) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'abuseipdb_logs';

        $wpdb->insert($table_name, [
            'ip_address' => $ip,
            'target_uri' => $uri,
            'user_agent' => $user_agent,
            'date_reported' => current_time('mysql')
        ]);
    }
}

new AbuseIPDBReporter();

