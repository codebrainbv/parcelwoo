<?php


/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'gsdemo_graphx');

/** MySQL database username */
define('DB_USER', 'gsdemo_graphx');

/** MySQL database password */
define('DB_PASSWORD', 'Wqv8sAde');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'Ik<w*983&UBPmpRK{Wo<Huda3QCNtAs`]u/|LJ:Iq057CtlZAZ/%%IO2PWfC3;8F');
define('SECURE_AUTH_KEY',  '9q!V`dQH0+4:Ob_7wA7Faja.<kKNmjI]|_uuBlQo+4qiLw-%`?]!!YdD+g}2@Nyq');
define('LOGGED_IN_KEY',    '1sRrugGr;7neV|B+XP:iy1_GqW^(*zqpSP{=`8iL~T*p gP_;.G~0S8V^1/!WaK3');
define('NONCE_KEY',        'VW!Z&xbx+<$/H3pKD7cRH+my7vqgeg sN,-.6X5wNThat%dA]mp3@rSH^b;**<T(');
define('AUTH_SALT',        '$j|Wi{d6}%jufiVGc2<ucDEJK=5W(x8gt362^YA_Yq_Tuq5Nv5J ze^+mT|z_,KR');
define('SECURE_AUTH_SALT', ':^Fh!`v5Fk1 DH(5jd2_J00n}C[jMA8X@O.uvpKTz-f3J)D.`[>z,b77vRPl_`)9');
define('LOGGED_IN_SALT',   'i~LgMyUC~bm@v{qux|s<pNh ,oW@jc^59MYeQYbS@oK>p]&Et_[3!OwL`/o&ll60');
define('NONCE_SALT',       'ia8b+Fi,*e%|N>fVukM^ &V}KH`sW`oJSJ5K(0(*hMkZNX=K^D$^#S#?DV}?,[{1');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wpwoo_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

// Disable display of errors and warnings 
define('WP_DEBUG_DISPLAY', true);
@ini_set('display_errors', 1);
@ini_set('display_startup_errors', 1);
// @error_reporting(E_ALL | E_STRICT);
@error_reporting(E_ALL);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
