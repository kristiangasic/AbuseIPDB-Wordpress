# AbuseIPDB WP Reporter

The **AbuseIPDB WP Reporter** is a WordPress plugin that helps secure your website by automatically reporting suspicious IP addresses to [AbuseIPDB](https://www.abuseipdb.com) after multiple failed attempts to access critical files. This plugin is **completely free** and will be updated regularly as needed.

---

## Features

- Automatically reports suspicious IPs after multiple failed access attempts to critical files.
- Logs reported IPs with detailed information such as target URI and user agent.
- Easy-to-use admin interface for plugin configuration.
- Supports AbuseIPDB’s free API for IP reporting.
- Fully open-source and free to use.

---

## Installation

1. **Download the Plugin**:  
   You can either clone the repository or download the ZIP file from GitHub.

2. **Upload the Plugin via WordPress Admin**:  
   - Go to your WordPress Admin Dashboard.
   - Navigate to **Plugins** → **Add New**.
   - Click on the **Upload Plugin** button at the top of the page.
   - Select the downloaded ZIP file and click **Install Now**.
   - After the installation is complete, click **Activate** to activate the plugin.

3. **Configure the Plugin**:  
   - Go to **Settings** → **AbuseIPDB Reporter** in your WordPress admin panel.
   - Enter your **AbuseIPDB API Key**. The API key is **free** and can be obtained from [AbuseIPDB](https://www.abuseipdb.com).
   
   **Note:** The plugin will not function without a valid API key.

---

## How It Works

The plugin monitors failed attempts to access sensitive files on your WordPress site. If an IP tries to access a protected file multiple times within a given timeframe, the IP will be reported to AbuseIPDB.

### Targeted Files:
The plugin monitors the following files:
- `xmlrpc.php`
- `wp-admin.php`
- `wp-login.php`
- `install.php`
- `readme.html`
- `.env`
- `.git`
- `phpmyadmin`
- `wp-config.php`
- `license.txt`

If a suspicious IP triggers multiple failed attempts, it will be reported to AbuseIPDB for further investigation.

---

## Settings

### API Key
To make the plugin functional, you’ll need an API key from [AbuseIPDB](https://www.abuseipdb.com). The API key is **free** and will be provided when you register on their site. After entering the key, click **Save Changes** to activate the plugin.

### Viewing Logs
You can view logs of all reported IP addresses under **Settings** → **AbuseIPDB Logs** in the WordPress admin dashboard. The logs include:
- IP Address
- Date Reported
- Target URI
- User Agent

---

## Contributing

This plugin is **completely free** to use, and contributions are always welcome! If you find a bug or have a feature suggestion, please open an issue or submit a pull request.

- **Issues**: Report bugs or feature requests on the [GitHub Issues page](https://github.com/kristiangasic/abuseipdb-wp-reporter/issues).
- **Contributing**: Fork the repository, make your changes, and submit a pull request.

---

## Updates

The plugin will be updated periodically, and new features will be added as needed. Make sure to keep your plugin updated to get the latest features and improvements.

---

## License

This plugin is released under the [MIT License](LICENSE).

---

## Give a Star! 🌟

If you find this plugin helpful, please consider leaving a star ⭐ on the repository. Your support helps improve the plugin and encourages more development.
[![Buy Me A Coffee](https://www.buymeacoffee.com/assets/img/custom_images/orange_img.png)](https://www.buymeacoffee.com/kristiangasic)
---

## Support

For any questions or issues, feel free to visit the [GitHub Issues page](https://github.com/kristiangasic/abuseipdb-wp-reporter/issues).

For more information, visit [gasic.bio](https://gasic.bio).

---

Thank you for using the **AbuseIPDB WP Reporter**!
