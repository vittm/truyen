<?php
/**
 * Created by PhpStorm.
 * User: icewindow
 * Date: 31.05.18
 * Time: 15:51
 */

namespace de\orcas\extension;


class Nagger {
	private static $instance;

	private $nags;

	public static function instance() {
		if (null == self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		add_action('admin_notices', array($this, 'adminNotices'));
		add_action('admin_enqueue_scripts', array($this, 'adminScripts'));

		add_action('wp_ajax_orcas-nagger', array($this, 'handleAjax'));

		$this->nags = array(
			'rate'    => array(),
			'pro'     => array(),
			'similar' => array(),
		);
	}

	public function handleAjax() {
		$lastNags = get_option('orcas-nagger', array(
			'rate'    => array(),
			'pro'     => array(),
			'similar' => array(),
		));
		$type     = $_POST['type'];
		$plugin   = $_POST['plugin'];

		if (in_array($type, array_keys($lastNags)) && in_array($plugin, array_keys($this->nags[ $type ]))) {
			if ("true" === $_POST['stop']) {
				$lastNags[ $type ][ $plugin ] = PHP_INT_MAX;
			} elseif ("true" === $_POST['dismiss']) {
				$lastNags[ $type ][ $plugin ] = time() + $this->nags[ $type ][ $plugin ] * 24 * 60 * 60;
			}
			update_option('orcas-nagger', $lastNags, false);
		}

		wp_die();
	}

	public function adminScripts() {
		if (defined('DISABLE_NAG_NOTICES') && DISABLE_NAG_NOTICES) {
			return;
		}
		wp_enqueue_style('orcas-nagger', plugin_dir_url(__FILE__) . 'nagger.css');
		wp_enqueue_script('orcas-nagger', plugin_dir_url(__FILE__) . 'nagger.js', array('jquery'), false, true);
		wp_localize_script('orcas-nagger', 'orcas_nagger', array(
			'ajax' => admin_url('admin-ajax.php'),
		));
	}

	public function adminNotices() {
		if (defined('DISABLE_NAG_NOTICES') && DISABLE_NAG_NOTICES) {
			return;
		}
		$plugins    = get_plugins();
		$pluginData = array();
		foreach ($plugins as $plugin => $data) {
			$slug                = explode(DIRECTORY_SEPARATOR, $plugin);
			$slug                = $slug[0];
			$pluginData[ $slug ] = _get_plugin_data_markup_translate($plugin, $data, false, true);
		}
		$lastNags = get_option('orcas-nagger', array(
			'rate'    => array(),
			'pro'     => array(),
			'similar' => array(),
		));

		$nagLinks = sprintf(
			'<a href="" class="dismiss">%s</a><a href="" class="stop">%s</a>',
			__('Dismiss', 'orcas-upgrade'),
			__('Stop showing this', 'orcas-upgrade')
		);

		ob_start();
		$hasContent = false;
		if (!empty($this->nags['pro']) && !(isset($_GET['page']) && 'orcas-upgrade' == $_GET['page'])) {
			echo '<div class="notice is-dismissible orcas-nagger pro-nagger">';
			printf(
				'<p>%s</p>',
				sprintf(
					__('Want more functionality from our plugins? You might want to consider getting the pro version for these plugins! Check out our <a href="%s">store</a>!', 'orcas-upgrade'),
					admin_url('admin.php?page=orcas-upgrade')
				)
			);

			$requested     = $this->nags['pro'];
			$proExtensions = json_decode(get_option('orcas_pro_extension', '[]'), true);
			foreach (array_keys($requested) as $plugin) {
				if (in_array($plugin, $proExtensions)) {
					continue;
				}
				if (!isset($lastNags['pro'][ $plugin ])) {
					// No last nag date set. Set initial nag time to half of what was requested
					$lastNags['pro'][ $plugin ] = time() + ($requested[ $plugin ] / 2) * 24 * 60 * 60;
				} elseif ($lastNags['pro'][ $plugin ] < time()) {
					// Time to nag!
					$hasContent = true;
					printf('<p class="nag dismissible" data-plugin="%s">%s<span>%s</span></p>', $plugin, $pluginData[ $plugin ]['Name'], $nagLinks);
				}
			}
			echo '</div>';
		}
		if ($hasContent) {
			echo ob_get_clean();
		} else {
			ob_end_clean();
		}

		ob_start();
		$hasContent = false;
		if (!empty($this->nags['rate'])) {
			echo '<div class="notice is-dismissible orcas-nagger rate-nagger">';
			printf(
				'<p>%s</p>',
				__('Like our plugin? Please take a moment to rate it!', 'orcas-upgrade')
			);

			$requested = $this->nags['rate'];
			foreach (array_keys($requested) as $plugin) {
				if (!isset($lastNags['rate'][ $plugin ])) {
					// No last nag date set. Set initial nag time to half of what was requested
					$lastNags['rate'][ $plugin ] = time() + ($requested[ $plugin ] / 2) * 24 * 60 * 60;
				} elseif ($lastNags['rate'][ $plugin ] < time()) {
					// Time to nag!
					$hasContent = true;
					printf(
						'<p class="nag dismissible" data-plugin="%s"><a href="%s" target="_blank">%s</a><span>%s</span></p>',
						$plugin,
						"https://wordpress.org/plugins/{$plugin}/#reviews",
						$pluginData[ $plugin ]['Name'],
						$nagLinks
					);
				}
			}
			echo '</div>';
		}
		if ($hasContent) {
			echo ob_get_clean();
		} else {
			ob_end_clean();
		}

		update_option('orcas-nagger', $lastNags, false);

	}

	/**
	 * Send a reminder to the user to rate the plugin on a regular basis
	 *
	 * @param string $plugin The plugin slug
	 * @param int $interval How long to wait between repeated reminders, in days
	 */
	public function registerRatingNag($plugin, $interval = 31) {
		$this->nags['rate'][ $plugin ] = $interval;
	}

	/**
	 * Send a reminder to get the pro version of a plugin
	 *
	 * @param string $plugin The plugin slug
	 * @param int $interval How long to wait between repeated reminders, in days
	 */
	public function registerProNag($plugin, $interval = 31) {
		$this->nags['pro'][ $plugin ] = $interval;
	}
}