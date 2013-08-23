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

		$textGenerator = $this->getMock('\Html2Text\Html2Text', array(), array(), '', false);
		$textGenerator->expects($this->once())->method('set_html')->with($this->equalTo('a<p>See Wah</p>b'));
		$textGenerator->expects($this->once())->method('get_text')->will($this->returnValue('a See Wah b'));

		$cssInliner = $this->getMock('\TijsVerkoyen\CssToInlineStyles\CssToInlineStyles', array(), array(), '', false);
		$cssInliner->expects($this->once())->method('setHTML')->with($this->equalTo('a<p>See Wah</p>b'));
		$cssInliner->expects($this->once())->method('setCSS')->with($this->equalTo('body {}' . PHP_EOL . 'a {}'));
		$cssInliner->expects($this->once())->method('convert')->will($this->returnValue('a<p>See Wah</p>b'));

		$manager->setMustache($mustache);
		$manager->setTextGenerator($textGenerator);
		$manager->setCssInliner($cssInliner);
		$manager->generate('{{> a}}<p>{{name}}</p>{{> b}}', array('a' => 'a', 'b' => 'b'), array('name' => 'See Wah'), array('body {}', 'a {}'), true);

		$this->assertEquals('a<p>See Wah</p>b', $manager->getHtml());
		$this->assertEquals('a See Wah b', $manager->getText());
	}

	/**
	 * Tests publishAsDraft - new template.
	 */
	public function testPublishAsDraftNewTemplate() {
		$manager = new MandrillTemplateManager();
		$manager->setHtml('<p>Read this now!</p>');
		$manager->setText('Read this now!');
		$service = $this->getMock('Mandrill_Templates', array(), array(), '', false);
		$service->expects($this->once())->method('getList')->will($this->returnValue(array(array('name' => 'old template'))));
		$service->expects($this->once())->method('add')->with(
			$this->equalTo('my fav template'),
			$this->equalTo('a@b.com'),
			$this->equalTo('Mr C'),
			$this->equalTo('Amazing subject...'),
			$this->equalTo('<p>Read this now!</p>'),
			$this->equalTo('Read this now!'),
			$this->equalTo(false));
		$service->expects($this->never())->method('update');
		$manager->setMandrillTemplatesService($service);
		$manager->publishAsDraft('my_key', 'my fav template', 'a@b.com', 'Mr C', 'Amazing subject...');
	}

	/**
	 * Tests publishAsDraft - existing template.
	 */
	public function testPublishAsDraftExistingTemplate() {
		$manager = new MandrillTemplateManager();
		$manager->setHtml('<p>Read this now!</p>');
		$manager->setText('Read this now!');
		$service = $this->getMock('Mandrill_Templates', array(), array(), '', false);
		$service->expects($this->once())->method('getList')->will($this->returnValue(array(array('name' => 'my fav template'))));
		$service->expects($this->once())->method('update')->with(
			$this->equalTo('my fav template'),
			$this->equalTo('a@b.com'),
			$this->equalTo('Mr C'),
			$this->equalTo('Amazing subject...'),
			$this->equalTo('<p>Read this now!</p>'),
			$this->equalTo('Read this now!'),
			$this->equalTo(false));
		$service->expects($this->never())->method('add');
		$manager->setMandrillTemplatesService($service);
		$manager->publishAsDraft('my_key', 'my fav template', 'a@b.com', 'Mr C', 'Amazing subject...');
	}
}
