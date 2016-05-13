<?php

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

/**
 * Zonda is golden.
 */
class WP_CLI_Valet_Command
{
	/**
	 * Create a new WordPress install -- fast
	 *
	 * ## OPTIONS
	 * <domain>
	 * : Site domain name without TLD.  Eg:  example.com = example
	 *
	 * [--version=<version>]
	 * : WordPress version to install
	 * ---
	 * default: latest
	 * ---
	 *
	 * [--locale=<locale>]
	 * : Select which language you want to install
	 *
	 * [--db=<db>]
	 * : Database driver
	 * ---
	 * default: mysql
	 * options:
	 *   - mysql
	 *   - sqlite
	 * ---
	 *
	 * [--dbname=<dbname>]
	 * : Database name (MySQL only). Default: 'wp_{domain}'
	 * ---
	 * default:
	 * ---
	 *
	 * [--dbuser=<dbuser>]
	 * : Database User (MySQL only)
	 * ---
	 * default: root
	 * ---
	 *
	 * [--dbpass=<dbpass>]
	 * : Set the database user password (MySQL only).  Default: ''
	 *
	 * [--dbprefix=<dbprefix>]
	 * : Set the database table prefix. Default: 'wp_'
	 * ---
	 * default: 'wp_'
	 * ---
	 *
	 * [--secure]
	 * : Valet will setup the site using HTTPS (default). Use --no-secure for http
	 * ---
	 * default: true
	 * ---
	 *
 	 * @when before_wp_load
	 */
	public function new($args, $assoc_args)
	{
		$site_name  = preg_replace('/^a-zA-Z/', '-', $args[0]);
		$is_secure  = \WP_CLI\Utils\get_flag_value($assoc_args, 'secure', true);
		$proto      = $is_secure ? 'https' : 'http';
		$tld        = $this->valet('domain');
		$domain     = "$site_name.$tld";

		$full_url   = "$proto://$domain";
		$full_path  = getcwd() . '/' . $site_name;

		if (! is_dir($full_path) && ! mkdir($full_path, 0755, true)) {
			WP_CLI::error('failed creating directory');
		}

		WP_CLI::line('Don\'t go anywhere, this will only take a second...');

		WP_CLI::debug('Downloading WP');
		WP_CLI::launch_self('core download', [], [
			'path'    => $full_path,
			'version' => $assoc_args['version'],
		], true, true);

		WP_CLI::debug('Configuring WP');
		WP_CLI::launch_self('core config', [], [
			'dbname'   => $assoc_args['dbname'] ?: "wp_{$site_name}",
			'dbuser'   => $assoc_args['dbuser'],
			'dbprefix' => $assoc_args['dbprefix'],
		], false, true, [
			'path' => $full_path,
		]);

		if ('mysql' == $assoc_args['db']) {
			WP_CLI::debug('Creating MySQL DB');
			WP_CLI::launch_self('db create', [], [], true, true, [
				'path' => $full_path,
			]);
		}
		if ('sqlite' == $assoc_args['db']) {
			WP_CLI::debug('Installing SQLite DB');

			if (! class_exists('ZipArchive')) {
				WP_CLI::error('PHP Zip extension seems to be missing.  Can\'t install SQLite integration automatically.');
			}

			WP_CLI::debug('Downloading sqlite-integration');
			$local_file = "$full_path/sqlite.zip";

			file_put_contents($local_file, fopen('https://downloads.wordpress.org/plugin/sqlite-integration.1.8.1.zip', 'r'));

			$zip = new ZipArchive;
			$zip->open($local_file);
		    $zip->extractTo("$full_path/wp-content/plugins/");
		    $zip->close();

		    unlink($local_file);

			if (! file_exists("$full_path/wp-content/plugins/sqlite-integration/db.php")) {
				WP_CLI::warning('sqlite-integration install failed');
				return;
			}

			copy(
				"$full_path/wp-content/plugins/sqlite-integration/db.php",
				"$full_path/wp-content/db.php"
			);

			WP_CLI::debug('sqlite-integration installed!');
		}

		WP_CLI::debug('Installing WordPress DB');
		WP_CLI::launch_self('core install', [], [
			'url'            => $full_url,
			'title'          => $site_name,
			'admin_user'     => 'admin',
			'admin_password' => 'admin',
			'admin_email'    => "admin@{$domain}",
			'skip-email'     => true
		], true, true, [
			'path' => $full_path,
		]);

		if ($is_secure) {
			$this->valet("secure $site_name");
		}

		WP_CLI::success("$site_name ready! $full_url");
	}

	private function valet($command)
	{
		ob_start();
		system("valet $command");
		$stdout = ob_get_clean();

		return trim($stdout);
	}
}
WP_CLI::add_command('valet', WP_CLI_Valet_Command::class);

