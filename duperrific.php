<?php
/**
 * Duperrific Theme Engine
 *
 * (c) Armando Sosa
 *
 * Dual licensed under MIT license
 *
 * @author Armando Sosa
 */


if (!defined('DUP_APP_PATH')) {
	define('DUP_APP_PATH',TEMPLATEPATH."/app");
	define('DUP_L10N_PATH',DUP_APP_PATH."/l10n");
	define('DUP_CONFIG_PATH',DUP_APP_PATH."/config");
}

/**
 *  Define the url for the core stylesheets
 */
if (!defined('DUP_CORE_STYLES_URL')) {
	 define('DUP_CORE_STYLES_URL',get_bloginfo('template_directory')."/".basename(dirname(__FILE__))."/styles/");
}
/**
 *  Define the url for the core javascript
 */

if (!defined('DUP_CORE_JS_URL')) {
	 define('DUP_CORE_JS_URL',get_bloginfo('template_directory')."/".basename(dirname(__FILE__))."/js/");
}

// if (!defined('DUP_CORE_STYLES_URL')) {
// 	 define('DUP_CORE_STYLES_URL',get_bloginfo('template_directory')."/".basename(dirname(__FILE__))."/js/");
// }

/**
 * Path where the framework is located
 */
define('DUP_PATH',dirname(__FILE__));
/**
 * Path to the core libraries directory
 */
define('DUP_CORE_PATH',DUP_PATH.'/core');
/**
 * Path to the default base classes. Duperrific will seek in DUP_APP_PATH first, thene here
 */
define('DUP_DEFAULTS_PATH',DUP_CORE_PATH.'/defaults');

define('DUP_STYLES_PATH',TEMPLATEPATH.'/styles');
define('DUP_SKINS_PATH',DUP_STYLES_PATH.'/skins');


define('DUP_VIEWS_PATH',TEMPLATEPATH.'/views');
define('DUP_ELEMENTS_PATH',DUP_VIEWS_PATH.'/elements');


require_once(DUP_CORE_PATH.'/functions.php');
require_once(DUP_CORE_PATH.'/duperrific.class.php');
require_once(DUP_CORE_PATH.'/controller.class.php');
require_once(DUP_CORE_PATH.'/theme.class.php');
require_once(DUP_CORE_PATH.'/behavior.class.php');
require_once(DUP_CORE_PATH.'/widget.class.php');
require_once(DUP_CORE_PATH.'/component.class.php');

/**
 *  Define the url for the core stylesheets
 */
if (!defined('DUP_STYLES_URL')) {
	 define('DUP_STYLES_URL',get_bloginfo('template_directory')."/styles/");
}


dupLoad('controller');
dupLoad('theme');
dupLoad('widgets');



?>