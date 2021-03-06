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

define('DB_NAME', 'truyen');


/** MySQL database username */

define('DB_USER', 'root');


/** MySQL database password */

define('DB_PASSWORD', "root");


/** MySQL hostname */

define('DB_HOST', 'localhost:8888');


/** Database Charset to use in creating database tables. */

define('DB_CHARSET', 'utf8');


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

define('AUTH_KEY',         'WdgQk_5p1Q0h@aP-Vm@7');

define('SECURE_AUTH_KEY',  'OxkBY8/hLA b$zGs=jVG');

define('LOGGED_IN_KEY',    'z#*_CX!kk=WMQH(t9x4=');

define('NONCE_KEY',        'Q+W)zp8EaqyTM&pU4PQJ');

define('AUTH_SALT',        'HQmg-M1(h64 NPbMm8ID');

define('SECURE_AUTH_SALT', '9n_)m!9tm) WNQKTKW+7');

define('LOGGED_IN_SALT',   'E/sgm92Z$SYbOz-LZftF');

define('NONCE_SALT',       'USJMS8m-8B$9W*%U)K8C');


/**#@-*/


/**

 * WordPress Database Table prefix.

 *

 * You can have multiple installations in one database if you give each

 * a unique prefix. Only numbers, letters, and underscores please!

 */

$table_prefix  = 'wp_';


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


/* That's all, stop editing! Happy blogging. */


/** Absolute path to the WordPress directory. */

if ( !defined('ABSPATH') )

	define('ABSPATH', dirname(__FILE__) . '/');


/** Sets up WordPress vars and included files. */

require_once(ABSPATH . 'wp-settings.php');

