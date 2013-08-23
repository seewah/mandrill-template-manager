<?php

// usage: php example.php welcome.mustache welcome.json live|draft .staging

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

// publish status
$publishStatus = $argv[3];
if($publishStatus != 'live' && $publishStatus != 'draft') {
	echo 'third arg must be either live or draft';
	exit;
}

// template name suffix
$suffix = (count($argv) > 4) ? $argv[4] : '';

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
$manager->save('output.html'); // we are not actually required to save before publishing to mandrill
$manager->publish($config['mandrillApiKey'], $config['templateName'] . $suffix, $config['fromEmail'], $config['fromName'], $config['subject']);
