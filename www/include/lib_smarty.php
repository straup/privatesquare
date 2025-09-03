<?php

	$GLOBALS['timings']['smarty_comp_count'] = 0;
	$GLOBALS['timings']['smarty_comp_time']	 = 0;

	# 5.0.0, required for PHP 8.2 and higher

	# START OF hoop jumping to load Smarty without Composer
	# https://github.com/smarty-php/smarty/issues/999#issuecomment-2109190898
	
	define('SMARTY_DIR', dirname(__FILE__) . "/smarty-5.5.1/");
	
	require_once(SMARTY_DIR . "functions.php");
	
	spl_autoload_register(function ($class) {
		// Class prefix
		$prefix = 'Smarty\\';
		
		// If we are not a member of above class skip
		if (!str_starts_with($class, $prefix)) { return; }

		// Hack off the prefix part
		$relative_class = substr($class, strlen($prefix));

		// Build a path to the include file
		$file = SMARTY_DIR . str_replace('\\', '/', $relative_class) . '.php';
		
		// If the file exists, require it
		if (file_exists($file))  { require_once($file); }
	});

	
	# END OF hoop jumping to load Smarty without Composer
	
	$GLOBALS['smarty'] = new Smarty\Smarty();

	$GLOBALS['smarty']->setTemplateDir($GLOBALS['cfg']['smarty_template_dir']);
	$GLOBALS['smarty']->setCompileDir($GLOBALS['cfg']['smarty_compile_dir']);

	$GLOBALS['smarty']->compile_check = $GLOBALS['cfg']['smarty_compile'];
	$GLOBALS['smarty']->force_compile = $GLOBALS['cfg']['smarty_force_compile'];

	$GLOBALS['smarty']->assign('cfg', $GLOBALS['cfg']);

	#######################################################################################

	function smarty_timings(){

		$GLOBALS['timings']['smarty_timings_out'] = microtime_ms();

		echo "<div class=\"admin-timings-wrapper\">\n";
		echo "<table class=\"admin-timings\">\n";

		# we add this one last so it goes at the bottom of the list
		$GLOBALS['timing_keys']['smarty_comp'] = 'Templates Compiled';

		foreach ($GLOBALS['timing_keys'] as $k => $v){
			$c = intval($GLOBALS['timings']["{$k}_count"]);
			$t = intval($GLOBALS['timings']["{$k}_time"]);
			echo "<tr><td>$v</td><td class=\"tar\">$c</td><td class=\"tar\">$t ms</td></tr>\n";
		}

		$map2 = array(
			array("Startup &amp; Libraries", $GLOBALS['timings']['init_end'] - $GLOBALS['timings']['execution_start']),
			# smarty_start_output is never actually defined anywhere... (20240214/thisisaaronland)				       
			# array("Page Execution", $GLOBALS['timings']['smarty_start_output'] - $GLOBALS['timings']['init_end']),
			# array("Smarty Output", $GLOBALS['timings']['smarty_timings_out'] - $GLOBALS['timings']['smarty_start_output']),
			array("<b>Total</b>", $GLOBALS['timings']['smarty_timings_out'] - $GLOBALS['timings']['execution_start']),
		);

		foreach ($map2 as $a){
			echo "<tr><td colspan=\"2\">$a[0]</td><td class=\"tar\">$a[1] ms</td></tr>\n";
		}

		echo "</table>\n";
		echo "</div>\n";
	}

	#######################################################################################

	# This exists because $smarty.get.PARAMETER triggers warnings if
	# PARAMTER is not set in $_GET
	
	function smarty_get($k){

		if (array_key_exists($k, $_GET)){
			return $_GET[$k];
		}
	}
	
	#######################################################################################
	
	$GLOBALS['smarty']->registerPlugin('function', 'timings', 'smarty_timings');
	$GLOBALS['smarty']->registerPlugin('modifier', 'smarty_get', 'smarty_get');	

	#######################################################################################

	# the end