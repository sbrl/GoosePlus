<?php 

$perfdata = new stdClass();
$perfdata->start = microtime(true);

// Phase 1: Autoloading
require("./vendor/autoload.php");
require("./TomlConfig.php");
require("./NightInk.php");

$perfdata->composer_autoload = microtime(true);

// Phase 2: Loading Settings
$settings = new \SBRL\TomlConfig("settings.toml", "settings.default.toml");

$perfdata->settings_load = microtime(true);

// Phase 3: Action parsing
$action = $_GET["action"] ?? "redirect";

switch($action) {
	/*
	 * ██      ██ ███████ ████████
	 * ██      ██ ██         ██
	 * ██      ██ ███████    ██
	 * ██      ██      ██    ██
	 * ███████ ██ ███████    ██
	 */
	case "list":
		$renderer = new \SBRL\NightInk();
		echo($renderer->render_file("./templates/list.html", [
			"search_engines" => $settings->get("search_engine")
		]));
		break;
	
	case "redirect":
		
		break;
}
