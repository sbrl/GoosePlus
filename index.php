<?php 

$perfdata = new stdClass();
$perfdata->start = +new Date();

// Phase 1: Autoloading
require("./vendor/autoload.php");
require("./TomlConfig.php");
require("./NightInk.php");

$perfdata->composer_autoload = +new Date();

// Phase 2: Loading Settings
$settings = new \SBRL\TomlConfig("settings.toml", "settings.default.toml");

$perfdata->settings_load = +new Date();

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
		$result = "";
		foreach($settings->search_engine as $engine) {
			$result .= "<tr>";
			
			$result .= "<td><img src='$engine->icon_url' /></td>";
			$result .= "<td>!$engine->bang</td>";
			$result .= "<td>$engine->name</td>";
			$result .= "<td>$engine->url_template</td>";
			
			$result .= "</tr>";
		}
		
		$renderer = new \SBRL\NightInk();
		echo($renderer->render_file("./templates/list.html", [
			"search_engines" => $result
		]));
		break;
	
	case "redirect":
		
		break;
}
