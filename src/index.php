<?php 

$perfdata = new stdClass();
$perfdata->start = microtime(true);

// Phase 1: Autoloading
require("../vendor/autoload.php");
require("./lib/TomlConfig.php");
require("./lib/NightInk.php");
require("./lib/full_url.php");

$renderer = new \SBRL\NightInk();

$perfdata->autoload = microtime(true);

// Phase 2: Loading Settings
if(!file_exists("../data"))
	mkdir("../data", 0700);
$settings = new \SBRL\TomlConfig("../data/settings.toml", "../settings.default.toml");

$perfdata->settings_load = microtime(true);

$endpoint = full_url_noquery();

// Phase 2.5: Auth
if($settings->get("auth.require_secret")) {
	$specified_secret = $_GET["secret"] ?? null;
	if($specified_secret !== $settings->get("auth.secret")) {
		http_response_code(401);
		exit($renderer->render_file("./templates/wrong_secret.html", []));
	}
}

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
		echo($renderer->render_file("./templates/list.html", [
			"name" => $settings->get("name"),
			"description" => $settings->get("description"),
			"search_engines" => $settings->get("search_engine"),
			"secret" => $settings->get("auth.secret")
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
			http_response_code(307);
			header("content-type: text/plain");
			header("location: ?action=help");
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
	
	
	/*
	 * ██████  ██████  ███████ ███    ██ ███████ ███████  █████  ██████   ██████ ██   ██
	 *██    ██ ██   ██ ██      ████   ██ ██      ██      ██   ██ ██   ██ ██      ██   ██
	 *██    ██ ██████  █████   ██ ██  ██ ███████ █████   ███████ ██████  ██      ███████
	 *██    ██ ██      ██      ██  ██ ██      ██ ██      ██   ██ ██   ██ ██      ██   ██
	 * ██████  ██      ███████ ██   ████ ███████ ███████ ██   ██ ██   ██  ██████ ██   ██
	 */
	case "opensearch":
		header("content-type: application/opensearchdescription+xml");
		echo($renderer->render_file("./templates/opensearch.xml", [
			"base_url" => $endpoint,
			"name" => $settings->get("name"),
			"description" => $settings->get("description")
		]));
		break;
}
