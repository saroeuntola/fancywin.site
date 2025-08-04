<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'u582932647_rfbVY' );

/** Database username */
define( 'DB_USER', 'u582932647_y5nzn' );

/** Database password */
define( 'DB_PASSWORD', 'Welcome2@Fw88' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'rEq4/yosaLcy}`54#*:#LU-dKu;*.UH|Fw-`g[Uub](BIA0)<l5Byd>NjW)4BT*/');
define('SECURE_AUTH_KEY',  ';.%,Iw7G7DO*kvkj0]<pb|fn!4no5rn~&-5.v.8+#IL%XFY5{_&g5{UmI4|8EKD]');
define('LOGGED_IN_KEY',    'Dh/Yx2;-;X8>[RR6Y(9D=|4RT}2{,bjt:o+yz%l#N;LF})pzFZ-KHA92Q1zG@-4X');
define('NONCE_KEY',        '4^ of$];E&=_I=EvA-oKSv2evF}l5Ah)+0@]5WHhZ+BiU*I!4{v}%#,XqW(HK(&(');
define('AUTH_SALT',        '#85Gr-BD+<g/y:2lI3-.]kNxp6$!c^Frj:dDh.UJsV!P4I~g%MRk55Q0fYnT%WQE');
define('SECURE_AUTH_SALT', 'g`5ZqbR[V~wDX=`z>,<{$`oT+Kp,*.;apQ~wv8!LD,C-e:R+FRPa0ti8Y-9BU=.q');
define('LOGGED_IN_SALT',   'XfbWu4wk-ZK6GRP*U^gZmbl~~@ T)b-/komhG;`P&HgVv^t9mXPGMKWK;$mTqbnZ');
define('NONCE_SALT',       'L~,b$Xj.UY&:}jHQv+kM|Hq)u3OY&=]X||p3xFToRkFSQ?`F)9 e@N<FnH^oSb*^');

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'NWI5OT_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
