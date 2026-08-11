<?php
/**
 * Plugin Name: Network Login Redirect
 * Description: Sends sub-site login pages to the primary site's wp-login.php
 * Version:     1.0.0
 * Network:     true
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPMS_Network_Login_Redirect' ) ) :

final class WPMS_Network_Login_Redirect {

	/**
	 * wp-login.php actions that may be handed over to the primary site.
	 * Everything else stays local on purpose: logout (session/nonce), rp + resetpass
	 * (reset cookie is set on the sub-site path), postpass, confirmaction (email keys).
	 */
	private static $redirectable_actions = array( 'login', 'lostpassword', 'retrievepassword' );

	/** Per-request memo for host lookups. */
	private static $host_cache = array();

	public static function init() {
		if ( ! is_multisite() ) {
			return;
		}

		// Runs on every site: lets wp_safe_redirect() bounce users to another site of the network.
		add_filter( 'allowed_redirect_hosts', array( __CLASS__, 'allow_network_hosts' ), 10, 2 );

		// Make every login / lost-password link generated on a sub-site point at the primary site.
		add_filter( 'login_url',        array( __CLASS__, 'filter_login_url' ), 20, 3 );
		add_filter( 'lostpassword_url', array( __CLASS__, 'filter_lostpassword_url' ), 20, 2 );

		// Catch direct hits on a sub-site's wp-login.php.
		add_action( 'login_init', array( __CLASS__, 'maybe_redirect_login_screen' ), 0 );
	}

	/* ---------------------------------------------------------------------
	 * 1. Link rewriting (covers auth_redirect(), "Log in" links, widgets, ...)
	 * ------------------------------------------------------------------- */

	public static function filter_login_url( $login_url, $redirect = '', $force_reauth = false ) {
		if ( ! self::should_redirect() ) {
			return $login_url;
		}

		$args = array();
		$target = self::resolve_redirect_target( $redirect );
		if ( $target ) {
			$args['redirect_to'] = $target;
		}
		if ( $force_reauth ) {
			$args['reauth'] = '1';
		}

		return self::primary_login_url( $args );
	}

	public static function filter_lostpassword_url( $url, $redirect = '' ) {
		if ( ! self::should_redirect() ) {
			return $url;
		}

		$args = array( 'action' => 'lostpassword' );
		$target = self::resolve_redirect_target( $redirect );
		if ( $target ) {
			$args['redirect_to'] = $target;
		}

		return self::primary_login_url( $args );
	}

	/* ---------------------------------------------------------------------
	 * 2. Direct requests to the sub-site login screen
	 * ------------------------------------------------------------------- */

	public static function maybe_redirect_login_screen() {
		if ( ! self::should_redirect() ) {
			return;
		}

		// The "session expired" modal is an iframe inside the sub-site admin: keep it local.
		if ( isset( $_REQUEST['interim-login'] ) ) {
			return;
		}

		// Never redirect a POST: that would silently discard credentials / 2FA input.
		$method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( $_SERVER['REQUEST_METHOD'] ) : 'GET';
		if ( 'GET' !== $method && 'HEAD' !== $method ) {
			return;
		}

		$action = isset( $_REQUEST['action'] ) ? sanitize_key( $_REQUEST['action'] ) : 'login';
		if ( ! in_array( $action, self::$redirectable_actions, true ) ) {
			return;
		}

		$args = array();
		if ( 'login' !== $action ) {
			$args['action'] = $action;
		}
		$target = self::resolve_redirect_target();
		if ( $target ) {
			$args['redirect_to'] = $target;
		}
		if ( ! empty( $_REQUEST['reauth'] ) ) {
			$args['reauth'] = '1';
		}
		if ( ! empty( $_REQUEST['checkemail'] ) ) {
			$args['checkemail'] = sanitize_key( $_REQUEST['checkemail'] );
		}
		if ( ! empty( $_REQUEST['loggedout'] ) ) {
			$args['loggedout'] = 'true';
		}

		$url = self::primary_login_url( $args );

		// Loop guard: bail if we would redirect to the URL we are already on.
		if ( self::is_same_endpoint( $url ) ) {
			return;
		}

		wp_redirect( $url, 302 );
		exit;
	}

	/* ---------------------------------------------------------------------
	 * 3. Allow wp_safe_redirect() to send the user back into the network
	 * ------------------------------------------------------------------- */

	public static function allow_network_hosts( $hosts, $host = '' ) {
		if ( empty( $host ) ) {
			return $hosts;
		}

		$host = strtolower( $host );
		if ( in_array( $host, (array) $hosts, true ) ) {
			return $hosts;
		}

		if ( self::is_network_host( $host ) ) {
			$hosts[] = $host;
		}

		return $hosts;
	}

	private static function is_network_host( $host ) {
		if ( isset( self::$host_cache[ $host ] ) ) {
			return self::$host_cache[ $host ];
		}

		$known = false;

		// Any site registered in wp_blogs with this domain (works for mapped domains too).
		$sites = get_sites( array(
			'domain'                 => $host,
			'number'                 => 1,
			'fields'                 => 'ids',
			'update_site_meta_cache' => false,
		) );
		if ( ! empty( $sites ) ) {
			$known = true;
		}

		// Any network's own domain (multi-network installs).
		if ( ! $known && function_exists( 'get_networks' ) ) {
			$networks = get_networks( array( 'domain' => $host, 'number' => 1, 'fields' => 'ids' ) );
			if ( ! empty( $networks ) ) {
				$known = true;
			}
		}

		/**
		 * Filter: add extra hosts (e.g. www. variants, IdP domains) that are safe to return to.
		 *
		 * @param bool   $known
		 * @param string $host
		 */
		$known = (bool) apply_filters( 'wpms_nlr_is_network_host', $known, $host );

		self::$host_cache[ $host ] = $known;

		return $known;
	}

	/* ---------------------------------------------------------------------
	 * Helpers
	 * ------------------------------------------------------------------- */

	private static function should_redirect() {
		if ( is_main_site() ) {
			return false;
		}
		if ( defined( 'WPMS_NLR_DISABLE' ) && WPMS_NLR_DISABLE ) {
			return false;
		}
		// Escape hatch if SSO ever breaks: add ?wpms_nlr=0 to reach the local form.
		if ( isset( $_GET['wpms_nlr'] ) && '0' === $_GET['wpms_nlr'] ) {
			return false;
		}

		/** Filter: return true to skip the redirect for this request. */
		return ! (bool) apply_filters( 'wpms_nlr_bypass', false );
	}

	private static function primary_login_url( array $args = array() ) {
		// network_site_url() always resolves to the primary site of the current network.
		$url = network_site_url( 'wp-login.php', 'login' );

		foreach ( $args as $key => $value ) {
			if ( '' === $value || null === $value ) {
				continue;
			}
			$url = add_query_arg( $key, urlencode( $value ), $url );
		}

		/**
		 * Filter the final primary-site login URL, e.g. to point at an SSO
		 * endpoint such as /wp-login.php?action=saml or /?sso=start.
		 *
		 * @param string $url
		 * @param array  $args
		 */
		return apply_filters( 'wpms_nlr_login_url', $url, $args );
	}

	/**
	 * Work out where the user should land after logging in on the primary site.
	 * Priority: explicit $redirect > ?redirect_to= > HTTP referer > this sub-site's dashboard.
	 * Always absolute (a bare "/wp-admin/" would resolve against the primary site) and
	 * always validated against the network's hosts, so this cannot become an open redirect.
	 */
	private static function resolve_redirect_target( $redirect = '' ) {
		$fallback = admin_url();

		if ( empty( $redirect ) && isset( $_REQUEST['redirect_to'] ) ) {
			$redirect = wp_unslash( $_REQUEST['redirect_to'] );
		}

		if ( empty( $redirect ) ) {
			$referer = wp_get_referer(); // already validated against allowed hosts
			if ( $referer && ! self::is_login_screen_url( $referer ) ) {
				$redirect = $referer;
			}
		}

		if ( empty( $redirect ) ) {
			$redirect = $fallback;
		}

		$redirect = self::make_absolute( $redirect );

		return wp_validate_redirect( $redirect, $fallback );
	}

	private static function make_absolute( $location ) {
		$location = trim( (string) $location );
		if ( '' === $location ) {
			return '';
		}

		$parts = wp_parse_url( $location );
		if ( ! empty( $parts['host'] ) ) {
			return $location;
		}

		$home = wp_parse_url( home_url( '/' ) );
		if ( empty( $home['host'] ) ) {
			return $location;
		}

		$origin = ( empty( $home['scheme'] ) ? 'https' : $home['scheme'] ) . '://' . $home['host'];
		if ( ! empty( $home['port'] ) ) {
			$origin .= ':' . $home['port'];
		}

		if ( 0 === strpos( $location, '/' ) ) {
			return $origin . $location;
		}

		// Relative path: resolve against the sub-site root.
		return home_url( '/' . ltrim( $location, '/' ) );
	}

	private static function is_login_screen_url( $url ) {
		return (bool) preg_match( '#/wp-(login|signup|activate)\.php#', (string) $url );
	}

	private static function is_same_endpoint( $url ) {
		$target  = wp_parse_url( $url );
		$current = wp_parse_url( home_url( isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '/' ) );

		if ( empty( $target['host'] ) || empty( $current['host'] ) ) {
			return false;
		}

		$host_match = strtolower( $target['host'] ) === strtolower( ( isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : $current['host'] ) );
		$path_target  = isset( $target['path'] ) ? $target['path'] : '';
		$path_current = isset( $_SERVER['REQUEST_URI'] ) ? strtok( $_SERVER['REQUEST_URI'], '?' ) : '';

		return $host_match && $path_target === $path_current;
	}
}

WPMS_Network_Login_Redirect::init();

endif;
