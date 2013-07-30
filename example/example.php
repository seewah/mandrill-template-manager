<?php

require('../vendor/autoload.php');

use \SeeWah\MandrillTemplateManager\MandrillTemplateManager;

$manager = new MandrillTemplateManager();
$templateFile = $argv[1];
$template = file_get_contents($templateFile);
$manager->generate($template, array(), array());
echo $manager->getHtml();
echo $manager->getText();
