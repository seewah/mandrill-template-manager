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
	 * Mandrill library
	 */
	private $mandrill;

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
	 * Sets mandrill.
	 * @param $mandrill
	 */
	public function setMandrill($mandrill) {
		$this->mandrill = $mandrill;
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
	 */
	public function generate($template, array $partials, array $data, array $css = array()) {

		if(!$this->mustache) $this->setMustache(new Mustache_Engine());
		if(!$this->textGenerator) $this->setTextGenerator(new Html2Text());

		$this->mustache->setPartials($partials);
		$this->html = $this->mustache->render($template, $data);
		$this->textGenerator->set_html($this->html);
		$this->text = $this->textGenerator->get_text();
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
		file_put_contents($this->html, $htmlFile);
		file_put_contents($this->text, $textFile);
	}

	/**
	 * Publishes to Mandrill as a draft template.
	 * @param string $apiKey api key
	 * @param string $templateName template name
	 * @param string $fromEmail from email
	 * @param string $fromName from name
	 * @param string $subject subject
	 * @throws Mandrill_Error
	 */
	public function publishAsDraft($apiKey, $templateName, $fromEmail, $fromName, $subject = '') {

		if(!$this->mandrill) $this->setMandrill(new Mandrill($apiKey));

		$lists = $this->mandrill->templates->getList();
		if(is_array($lists)) {
			foreach($lists as $template) {
				if($template['name'] == $templateName) {
					$this->mandrill->templates->update($templateName, $fromEmail, $fromName, $subject, $this->html, $this->text, false);
					return;
				}
			}
		}
		$this->mandrill->templates->add($templateName, $fromEmail, $fromName, $subject, $this->html, $this->text, false);
	}
}
