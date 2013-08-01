mandrill-template-manager
=========================

A PHP utility class for generating and publishing Mandrill templates.

Why?
----

I am a fan of the [Mandrill](http://mandrill.com) service. With its template support, we can iterate on email content and layout with relative ease. However, to generate templates, there are a number of steps involved in the workflow, which can be quite manual and errors can easily occur:

 * combining different html files (header, footer, etc) together to build the complete html
 * inlining css
 * generating the plain text
 * copy and pasting final html and plain text into Mandrill

So I set out to create a tool to automate all these for me. I chose [Mustache](http://mustache.github.com/) as the templating language.

In addition to being able to "include" templates ("Mustache partials"), I want to be able to use variables in my templates that do not come from the server. For example, we store images on Amazon S3. Instead of hardcoding the S3 host everywhere in my templates, I want to be able to store the host as a variable and reference the variable in my templates.

Usage
-----

As Mandrill Template Manager has been packaged up as a [Composer](http://getcomposer.org/) package, the easiest way to start using the class is to [install Composer and use the autoloader](http://getcomposer.org/doc/00-intro.md).

A quick example:

```php
<?php
$m = new \SeeWah\MandrillTemplateManager\MandrillTemplateManager;
$m->generate('{{> header}}<a href="{{siteUrl}}{{> footer}}">Welcome</a>', $partials, array('siteUrl' => 'http://seewah.com'), $css);
$m->publishAsDraft($mandrillKey, 'template name', 'no-reply@seewah.com', 'See Wah', 'Getting started');
```

For a more complete example, check out the [example](https://github.com/seewah/mandrill-template-manager/tree/master/example) to see how you may want to use the class in real life.

Including mandrill-template-manager in your own project via Composer
====================================================================

As one of the dependent packages, [html2text](https://packagist.org/packages/html2text/html2text), has not been tagged, to stop `composer install` from moaning, you would have to do the following in your project's `composer.json`.

```json
{
	"require": {
		"seewah/mandrill-template-manager": "1.0.*",
		"html2text/html2text": "@dev"
	}
}
```
