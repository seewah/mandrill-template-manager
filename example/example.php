<?php

// usage: php example.php welcome.mustache welcome.json

require(dirname(__FILE__) . '/../vendor/autoload.php');

use \SeeWah\MandrillTemplateManager\MandrillTemplateManager;

$manager = new MandrillTemplateManager();

// global config
$config = json_decode(file_get_contents(dirname(__FILE__) . '/config.json'), true);

// template file
$template = file_get_contents($argv[1]);

// template-specific config
$templateSpecificConfig = json_decode(file_get_contents($argv[2]), true);
$config = array_merge($config, $templateSpecificConfig);
if(isset($config["additionalIncludes"])) {
	$config["includes"] = array_merge($config["includes"], $config["additionalIncludes"]);
	unset($config["additionalIncludes"]);
}
if(isset($config["additionalCss"])) {
	$config["css"] = array_merge($config["css"], $config["additionalCss"]);
	unset($config["additionalCss"]);
}

// preparing final partials(includes) and css list
$includes = array_map(function($item) {
	global $config;
	return file_get_contents($config["includeBaseDir"] . $item);
}, $config["includes"]);
$css = array_map(function($item) {
	global $config;
	return file_get_contents($config["cssBaseDir"] . $item);
}, $config["css"]);

$manager->generate($template, $includes, $config, $css);
$manager->save('output.html', 'output.txt'); // we are not actually required to save before publishing to mandrill
$manager->publishAsDraft($config['mandrillApiKey'], $config['templateName'], $config['fromEmail'], $config['fromName'], $config['subject']);
