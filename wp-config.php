<?php
define( 'DISABLE_WP_CRON', true );
define('WP_CACHE', true); // Added by FlyingPress


//define( 'WP_CRON_LOCK_TIMEOUT', 60 );

/* Enabling GZIP compression */
if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler");

/* Optimize Database */
//define('WP_ALLOW_REPAIR', true);

# Vo hieu hoa tinh nang update theme/plugin
//define( 'DISALLOW_FILE_MODS', true );
//define( 'DISALLOW_FILE_EDIT', true );

# Gioi han save revisions, chi de 8 bang
define( 'WP_POST_REVISIONS', 8 );

# Bat buoc ket noi HTTPS
define('FORCE_SSL_LOGIN', true);
define('FORCE_SSL_ADMIN', true);

# Tang bo nho gioi han cho PHP
define( 'WP_MEMORY_LIMIT', '256M' );
define( 'WP_MAX_MEMORY_LIMIT', '256M' );

# Xoa bai trong thung rac sau 10 ngay
define( 'EMPTY_TRASH_DAYS', 10 );

/* Compression CSS*/
define( 'COMPRESS_CSS', false );

/* Compression JS*/
define( 'COMPRESS_SCRIPTS', false );

/* tat update plugin WP */
define( 'AUTOMATIC_UPDATER_DISABLED', true );

/* tat update core WP */
define( 'WP_AUTO_UPDATE_CORE', false );

/* Enable bug WP */
define('WP_DEBUG', false);

/* Redirect 404 to url */
//define( 'NOBLOGREDIRECT', 'https://demo.cadobanh.net' );

/* Setup cookie */
//define( 'COOKIE_DOMAIN', 'www.demo.cadobanh.net' );

/* Chan external request */
//define('WP_HTTP_BLOCK_EXTERNAL', true);

/* whitelist url duoc access vao site */
//define('WP_ACCESSIBLE_HOSTS', 'wordpress.org');


/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
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
define( 'AUTH_KEY',          '9{x<lb6$_fqy2sJa-j4qe`x&fm/:y[DEXAJ{Tm3N8lJ}xI;RphzKd$[jDK=[ghJj' );
define( 'SECURE_AUTH_KEY',   'Ck7qSOw-FRO5~b4Vgvk#X{If6|Z1Hi@lf[09q]WfE&VA=QoVI4(L$g$Ypq9(/teU' );
define( 'LOGGED_IN_KEY',     '!?t7a{PfB-6hGlH(Pn2U{1<5#<|lrngLZ)7z,^**FjI6&U).=$`j*F~W#zC*Z%1{' );
define( 'NONCE_KEY',         '=}wZJ2fpVSdyF|iE:sU>+4Je$vlyP{RB(bRHYlbiA*xB ?U+~{Kc-M(C&<M,By>:' );
define( 'AUTH_SALT',         '%84|*Q[g%ZOFQAs*ruWPp-R`+u8^c%?O=fBzp{9@jRo-Vi=~ouni!ThNu-1K39%n' );
define( 'SECURE_AUTH_SALT',  'jhb=@o]j.CP<C:3mg1&|2%2xO/SItM 8=QNJ9:Obi^(;X?)Z8PE@3ZnNbTR-!Sn_' );
define( 'LOGGED_IN_SALT',    '%jwZql=yolYS.D|Au~*PC.5Ly],$o+~BW=S NLk3i*&W92@MA[s~{8:o*nC.K^wh' );
define( 'NONCE_SALT',        'Xm,as(7m##T[PbiyH2)<*bey=d78Z<_MI|v`:zk7f)T,V/>(I5|4M6sEl{!HXG09' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'NWI5OT_';


/* Add any custom values between this line and the "stop editing" line. */

define( 'WP_DEBUG_LOG', false );
define( 'WP_DEBUG_DISPLAY', false );


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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';