<?php

namespace SeeWah\MandrillTemplateManager;

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
	 * Generated html
	 */
	private $html;

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
	 * Resets.
	 */
	public function reset() {
		$this->html = null;
	}

	/**
	 * Sets the html value.
	 * @param string $html
	 */
	public function setHtml($html) {
		$this->html = $html;
	}

	/**
	 * Returns the generated html.
	 * @return string
	 */
	public function getHtml() {
		return $this->html;
	}

	/**
	 * Generates the html.
	 * @param string $template mustache template
	 * @param array $partials mustache partials
	 * @param array $data mustache data
	 * @param array $css css's
	 */
	public function generate($template, array $partials, array $data, array $css = array()) {

		if(!$this->mustache) $this->setMustache(new Mustache_Engine());

		$this->mustache->setPartials($partials);
		$this->html = $this->mustache->render($template, $data);
		if(count($css)) {
			if(!$this->cssInliner) $this->setCssInliner(new CssToInlineStyles());
			$this->cssInliner->setHTML($this->html);
			$this->cssInliner->setCSS(implode(PHP_EOL, $css));
			$this->html = $this->cssInliner->convert(true);
			// to resolve this css inliner bug! https://github.com/dgaidula/CssToInlineStyles/commit/447d666ebb9c7c49a2afb22b5e7755bc29db9736
			if(substr($this->html, 0, 1) == '>') $this->html = substr($this->html, 1);
		}
	}

	/**
	 * Saves generated html and plain text as files.
	 * @param string $htmlFile file path
	 */
	public function save($htmlFile) {
		file_put_contents($htmlFile, $this->html);
	}

	/**
	 * Publishes the generated template to Mandrill as a draft template.
	 * Note: It is NOT actually a requirement to call MandrillTemplateManager::save before publishing to mandrill!
	 * @param string $apiKey api key
	 * @param string $templateName template name
	 * @param string $fromEmail from email
	 * @param string $fromName from name
	 * @param string $subject subject
	 * @param bool $live to publish live
	 * @throws Mandrill_Error
	 */
	public function publish($apiKey, $templateName, $fromEmail, $fromName, $subject = '', $live = false) {

		if(!$this->mandrillTemplatesService) {
			$mandrill = new Mandrill($apiKey);
			$this->setMandrillTemplatesService($mandrill->templates);
		}

		$lists = $this->mandrillTemplatesService->getList();
		if(is_array($lists)) {
			foreach($lists as $template) {
				if($template['name'] == $templateName) {
					$this->mandrillTemplatesService->update($templateName, $fromEmail, $fromName, $subject, $this->html, '', $live);
					return;
				}
			}
		}
		$this->mandrillTemplatesService->add($templateName, $fromEmail, $fromName, $subject, $this->html, '', $live);
	}
}
