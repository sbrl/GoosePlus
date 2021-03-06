<?php

namespace SBRL;

/**
 * A teeny-tiny templating engine.
 * @author			Starbeamrainbowlabs
 * @version			v0.2
 * @lastModified	23rd August 2018
 * @license			https://www.mozilla.org/en-US/MPL/2.0/	Mozilla Public License 2.0
 * Changelog:
	 * v0.2:
	 	 * Don't replace if a value can't be found
 	 * v0.1:
 	 	 * Initial release
 */
class NightInk
{
	
	public function __construct() {
		
	}
	
	/**
	 * Finds an arbitrarily nested item given a trail of breadcrumbs to follow.
	 * @param	object|array	$data	The data object or array to search.
	 * @param	string[]		$parts	The keys to use to drill down and find the requested item.
	 * @return	mixed			The item at the location specified by the $parts array of keys.
	 */
	protected function locate_part($data, $parts) {
		$sub_data = $data;
		foreach($parts as $part) {
			if(is_object($sub_data) && isset($sub_data->$part))
				$sub_data = $sub_data->$part;
			else if(is_array($sub_data) && isset($sub_data[$part]))
				$sub_data = $sub_data[$part];
			else
				return null;
		}
		return $sub_data;
	}
	
	/**
	 * Like render(), but reads the template from a file instead.
	 * @param	string	$filename	The path to the file that contains the template.
	 * @param	mixed	$options	The options object/array/thing to render against.
	 * @return	string	The rendered output.
	 */
	public function render_file($filename, $options) {
		return $this->render(
			file_get_contents($filename),
			$options
		);
	}
	
	/**
	 * Renders the specified template against to provided data object or array.
	 * @param	string	$template	The template to render.
	 * @param	mixed	$options	The data object/array/thing to render against.
	 * @return	string	The rendered output.
	 */
	public function render($template, $options) {
		return preg_replace_callback(
			"/\{\{?([^{}]*)\}\}?/iu",
			function($matches) use($options) {
				// {{key}} {key} parsing
				$parts = array_filter(
					explode(".", $matches[1]),
					function($el) { return strlen($el) > 0; }
				);
				
				$sub_data = strval(count($parts) > 0 ? $this->locate_part($options, $parts) : $options);
				
				if($sub_data == null)
					return $matches[0];
				
				// The first char has to be a { anyway - so if the second char is a {, then it *has* to be {{
				if($matches[0][1] == "{")
					$sub_data = htmlentities($sub_data);
				
				return $sub_data;
			},
			preg_replace_callback(
				"/\{#each\s+([^{}]+)\}(.*)\{#endeach\}/imus",
				function($matches) use($options) {
					// {#each key} parsing
					// 0: full thing
					// 1: parts
					// 2: inner template
					
					$sub_data = $this->locate_part(
						$options,
						explode(".", $matches[1])
					);
					
					$result = "";
					foreach($sub_data as $item)
						$result .= $this->render($matches[2], $item);
					return $result;
				}, 
				$template
			)
		);
	}
}

/*** Example ***
$parser = new NightInk();
$options = [
	"seconds" =>  10,
	"model" =>  "Ariane 6",
	"payload" =>  [
		"summary" =>  "Sentinel-4",
		"owner" =>  [
			"name" =>  "Dr. Sean",
			"company" =>  "Sean's Satellites Inc."
		]
	],
	"totals" =>  [
		"weight" =>  450,
		"tomatoes" =>  56
	],
	"list" => [
		[ "name" => "Rocket", "quantity" => 1 ],
		[ "name" => "Astronaut", "quantity" => 3 ],
		[ "name" => "Fuel Cells", "quantity" => 55 ],
		[ "name" => "Launchpad", "quantity" => 1 ]
	]
];
echo($parser->render("<p>Next launch in {{seconds}}!</p>
<p>Rocket info: {{model}} carrying {{payload.summary}} for {{payload.owner.name}}, totalling {{totals.weight}} tons</p>
<p>To go to {{place_name}}, you will need:</p> 
<ul>
	{#each list}
	<li>{{quantity}} x {{name}}</li>
	{#endeach}
</ul>
", $options));
*/
