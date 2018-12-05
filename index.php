<?php 

$perfdata = new stdClass();
$perfdata->start = microtime(true);

// Phase 1: Autoloading
require("./vendor/autoload.php");
require("./TomlConfig.php");
require("./NightInk.php");

$perfdata->autoload = microtime(true);

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
	case "help":
		$renderer = new \SBRL\NightInk();
		echo($renderer->render_file("./templates/list.html", [
			"search_engines" => $settings->get("search_engine")
		]));
		break;
	
	
	/*
	 * ██████  ███████ ██████  ██ ██████  ███████  ██████ ████████
	 * ██   ██ ██      ██   ██ ██ ██   ██ ██      ██         ██
	 * ██████  █████   ██   ██ ██ ██████  █████   ██         ██
	 * ██   ██ ██      ██   ██ ██ ██   ██ ██      ██         ██
	 * ██   ██ ███████ ██████  ██ ██   ██ ███████  ██████    ██
	 */
	case "redirect":
		if(!isset($_GET["query"])) {
			http_response_code(422);
			header("content-type: text/plain");
			exit("Error: Required 'query' GET parameter wasn't specified.");
		}
		
		$query = $_GET["query"];
		$bang = preg_match(
			"/!([a-zA-Z0-9_-]+)|([a-zA-Z0-9_-]+)!/",
			$query,
			$bang_matches
		);
		
		
		$destination = $settings->get("basic.default_template");
		// Process the bang
		if(!empty($bang_matches)) {
			$bang = $bang_matches[0];
			$bang_trimmed = trim($bang, "!");
			// TODO: Replace this with a lookup table - maybe in the toml settings file if we can figure out how to structure this?
			foreach($settings->get("search_engine") as $engine) {
				if($engine["bang"] != $bang_trimmed)
					continue;
				
				// We've found a match
				$destination = $engine["url_template"];
			}
			
			// Remove the !bang
			$query = preg_replace(
				"/\s*".preg_quote($bang)."\s*/", "",
				$query
			);
		}
		
		// Insert the query string
		$destination = str_replace(
			"{{{s}}}", rawurlencode($query),
			$destination
		);
		
		http_response_code(308);
		header("location: $destination");
		header("x-perfdata: " .
			round(microtime(true) - $perfdata->start, 5) . "ms total | " .
			round($perfdata->autoload - $perfdata->start, 5) . "ms composer, " .
			round($perfdata->settings_load - $perfdata->autoload, 5) . "ms settings, " .
			round(microtime(true) - $perfdata->settings_load, 5) . "ms destination calc"
		);
		break;
}
