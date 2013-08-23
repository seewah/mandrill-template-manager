<?php

namespace SeeWah\MandrillTemplateManager;

use \Html2Text\Html2Text;
use \Mandrill;
use \Mandrill_Error;
use \Mustache_Engine;
use \TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

/**
 * Utility class for generating and publishing Mandrill templates.
 */
class MandrillTemplateManager {

	/**
	 * Mustache object
	 */
	private $mustache;

	/**
	 * Mandrill templates service
	 */
	private $mandrillTemplatesService;

	/**
	 * Css inliner
	 */
	private $cssInliner;

	/**
	 * Plain text generator
	 */
	private $textGenerator;

	/**
	 * Generated html
	 */
	private $html;

	/**
	 * Generated plain text
	 */
	private $text;

	/**
	 * Sets mustache.
	 * @param $mustache
	 */
	public function setMustache($mustache) {
		$this->mustache = $mustache;
	}

	/**
	 * Sets mandrillTemplatesService.
	 * @param $mandrillTemplatesService
	 */
	public function setMandrillTemplatesService($mandrillTemplatesService) {
		$this->mandrillTemplatesService = $mandrillTemplatesService;
	}

	/**
	 * Sets cssInliner.
	 * @param $cssInliner
	 */
	public function setCssInliner($cssInliner) {
		$this->cssInliner = $cssInliner;
	}

	/**
	 * Sets textGenerator.
	 * @param $textGenerator
	 */
	public function setTextGenerator($textGenerator) {
		$this->textGenerator = $textGenerator;
	}

	/**
	 * Resets.
	 */
	public function reset() {
		$this->html = null;
		$this->text = null;
	}

	/**
	 * Sets the html value.
	 * @param string $html
	 */
	public function setHtml($html) {
		$this->html = $html;
	}

	/**
	 * Sets the plain text value.
	 * @param string $text
	 */
	public function setText($text) {
		$this->text = $text;
	}

	/**
	 * Returns the generated html.
	 * @return string
	 */
	public function getHtml() {
		return $this->html;
	}

	/**
	 * Returns the generated plain text.
	 * @return string
	 */
	public function getText() {
		return $this->text;
	}

	/**
	 * Generates the html and plain text.
	 * @param string $template mustache template
	 * @param array $partials mustache partials
	 * @param array $data mustache data
	 * @param array $css css's
	 * @param bool $generatePlaintext make sure the mailchimp tag values make sense for both html and plaintext versions. I would actually recommend relying on Mandrill to generate the final plaintext email contents by keeping this as false, and leaving $this->text as an empty string.
	 */
	public function generate($template, array $partials, array $data, array $css = array(), $generatePlaintext = false) {

		if(!$this->mustache) $this->setMustache(new Mustache_Engine());
		if($generatePlaintext && !$this->textGenerator) $this->setTextGenerator(new Html2Text());

		$this->mustache->setPartials($partials);
		$this->html = $this->mustache->render($template, $data);
		if($generatePlaintext) {
			$this->textGenerator->set_html($this->html);
			$this->text = $this->textGenerator->get_text();
		}
		else {
			$this->text = '';
		}
		if(count($css)) {
			if(!$this->cssInliner) $this->setCssInliner(new CssToInlineStyles());
			$this->cssInliner->setHTML($this->html);
			$this->cssInliner->setCSS(implode(PHP_EOL, $css));
			$this->html = $this->cssInliner->convert();
		}
	}

	/**
	 * Saves generated html and plain text as files.
	 * @param string $htmlFile file path
	 * @param string $textFile file path
	 */
	public function save($htmlFile, $textFile) {
		file_put_contents($htmlFile, $this->html);
		if($textFile && $this->text) file_put_contents($textFile, $this->text);
	}

	/**
	 * Publishes the generated template to Mandrill as a draft template.
	 * Note: It is NOT actually a requirement to call MandrillTemplateManager::save before publishing to mandrill!
	 * @param string $apiKey api key
	 * @param string $templateName template name
	 * @param string $fromEmail from email
	 * @param string $fromName from name
	 * @param string $subject subject
	 * @throws Mandrill_Error
	 */
	public function publishAsDraft($apiKey, $templateName, $fromEmail, $fromName, $subject = '') {

		if(!$this->mandrillTemplatesService) {
			$mandrill = new Mandrill($apiKey);
			$this->setMandrillTemplatesService($mandrill->templates);
		}

		$lists = $this->mandrillTemplatesService->getList();
		if(is_array($lists)) {
			foreach($lists as $template) {
				if($template['name'] == $templateName) {
					$this->mandrillTemplatesService->update($templateName, $fromEmail, $fromName, $subject, $this->html, $this->text, false);
					return;
				}
			}
		}
		$this->mandrillTemplatesService->add($templateName, $fromEmail, $fromName, $subject, $this->html, $this->text, false);
	}
}
