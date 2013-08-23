<?php

use \SeeWah\MandrillTemplateManager\MandrillTemplateManager;

class MandrillTemplateManagerTest extends PHPUnit_Framework_TestCase {

	/**
	 * Tests generate.
	 */
	public function testGenerate() {

		$manager = new MandrillTemplateManager();

		$mustache = $this->getMock('Mustache_Engine', array(), array(), '', false);
		$mustache->expects($this->once())->method('setPartials')->with($this->equalTo(array('a' => 'a', 'b' => 'b')));
		$mustache->expects($this->once())->method('render')
			->with($this->equalTo('{{> a}}<p>{{name}}</p>{{> b}}'), $this->equalTo(array('name' => 'See Wah')))
			->will($this->returnValue('a<p>See Wah</p>b'));

		$cssInliner = $this->getMock('\TijsVerkoyen\CssToInlineStyles\CssToInlineStyles', array(), array(), '', false);
		$cssInliner->expects($this->once())->method('setHTML')->with($this->equalTo('a<p>See Wah</p>b'));
		$cssInliner->expects($this->once())->method('setCSS')->with($this->equalTo('body {}' . PHP_EOL . 'a {}'));
		$cssInliner->expects($this->once())->method('convert')->will($this->returnValue('a<p>See Wah</p>b'));

		$manager->setMustache($mustache);
		$manager->setCssInliner($cssInliner);
		$manager->generate('{{> a}}<p>{{name}}</p>{{> b}}', array('a' => 'a', 'b' => 'b'), array('name' => 'See Wah'), array('body {}', 'a {}'), true);

		$this->assertEquals('a<p>See Wah</p>b', $manager->getHtml());
	}

	/**
	 * Tests publish - new template.
	 */
	public function testPublishNewTemplate() {
		$manager = new MandrillTemplateManager();
		$manager->setHtml('<p>Read this now!</p>');
		$service = $this->getMock('Mandrill_Templates', array(), array(), '', false);
		$service->expects($this->once())->method('getList')->will($this->returnValue(array(array('name' => 'old template'))));
		$service->expects($this->once())->method('add')->with(
			$this->equalTo('my fav template'),
			$this->equalTo('a@b.com'),
			$this->equalTo('Mr C'),
			$this->equalTo('Amazing subject...'),
			$this->equalTo('<p>Read this now!</p>'),
			$this->equalTo(''),
			$this->equalTo(true));
		$service->expects($this->never())->method('update');
		$manager->setMandrillTemplatesService($service);
		$manager->publish('my_key', 'my fav template', 'a@b.com', 'Mr C', 'Amazing subject...', true);
	}

	/**
	 * Tests publish - existing template.
	 */
	public function testPublishExistingTemplate() {
		$manager = new MandrillTemplateManager();
		$manager->setHtml('<p>Read this now!</p>');
		$service = $this->getMock('Mandrill_Templates', array(), array(), '', false);
		$service->expects($this->once())->method('getList')->will($this->returnValue(array(array('name' => 'my fav template'))));
		$service->expects($this->once())->method('update')->with(
			$this->equalTo('my fav template'),
			$this->equalTo('a@b.com'),
			$this->equalTo('Mr C'),
			$this->equalTo('Amazing subject...'),
			$this->equalTo('<p>Read this now!</p>'),
			$this->equalTo(''),
			$this->equalTo(true));
		$service->expects($this->never())->method('add');
		$manager->setMandrillTemplatesService($service);
		$manager->publish('my_key', 'my fav template', 'a@b.com', 'Mr C', 'Amazing subject...', true);
	}
}
