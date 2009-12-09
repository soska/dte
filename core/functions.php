<?php
/*
	
*/
function dupLoad($className,$default = DUP_DEFAULTS_PATH,$app=DUP_APP_PATH){
	$path = "/$className.php";	
	$defaultPath = $default.$path;
	$path = $app.$path;
	if (file_exists($path)) {
		require_once ($path);
	}elseif(file_exists($defaultPath)){
		require_once ($defaultPath);		
	}else{
		return false;
	}
	return true;
}

/*
	Debug Functions
*/

if (!function_exists('debug')) {
	function debug($var,$backtrace = true){		
		echo "<pre>";
		if ($backtrace) {
			$bt = debug_backtrace();
			unset($bt[0]);
			
			foreach ($bt as $t) {
				// var_dump($bt);die;
				echo "\n====\n";
				if(isset($t['file'])) echo " file: {$t['file']}";
				if(isset($t['line'])) echo " line: {$t['line']}\n";
				echo "\n function: {$t['function']}";
				if (isset($t['class'])) {
					echo "\n class: {$t['class']}";
				}
				echo "\n====\n";
			}
		}		
		print_r($var);
		echo "</pre>";
	}
}

if (!function_exists('pr')) {
	function pr($var){		
		debug($var,false);
	}
	function pd($var){
		debug($var);die;
	}
	function hpr($var){
		pr(htmlspecialchars($var));
	}
	function jpr($var,$name = 'jprDebug'){
		$var = json_encode($var);
		echo "<script type=\"text/javascript\" charset=\"utf-8\">var $name = $var; console.debug($name);</script>";
	}
}

/**
* This function can be thought of as a hybrid between PHP's array_merge and array_merge_recursive. The difference
* to the two is that if an array key contains another array then the function behaves recursive (unlike array_merge)
* but does not do if for keys containing strings (unlike array_merge_recursive). See the unit test for more information.
*
* Note: This function will work with an unlimited amount of arguments and typecasts non-array parameters into arrays.
*
* Taken from Cake's Source Code http://api.cakephp.org/view_source/set/#line-52
*
* @param array $arr1 Array to be merged
* @param array $arr2 Array to merge with
* @return array Merged array
* @access public
*/
function set_merge($arr1, $arr2 = null) {
	$args = func_get_args();
 
	$r = (array)current($args);
	while (($arg = next($args)) !== false) {
		foreach ((array)$arg as $key => $val) {
			if (is_array($val) && isset($r[$key]) && is_array($r[$key])) {
				$r[$key] = set_merge($r[$key], $val);
			} elseif (is_int($key)) {
				$r[] = $val;
			} else {
				$r[$key] = $val;
			}
		}
	}
	return $r;
}

/**
 * Underscores a camelCasedword. Also, taken from Cake: http://api.cakephp.org/view_source/inflector/#line-427
 *
 * @param string $camelCasedWord 
 * @return void
 * @author Armando Sosa
 */
function underscorize($camelCasedWord) {
	return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $camelCasedWord));
}


function phraseize($camelCasedWord) {
	return preg_replace('/(?<=\\w)([A-Z])/', ' \\1', $camelCasedWord);
}




?>