Example
=======

This example demonstrates how to use `MandrillTemplateManager` in real life. Although this is meant to be an example, I do actually use the `example.php` script for my own Mandrill workflow.

Here is my scenario:

 * I have a bunch of standard includes/widgets e.g. `header.mustache`.
 * I do not want any inline css in any of my mustache templates.
 * I am happy to include any media query css directly inside `<style></style>` in `header.mustache`.
 * I do not want to hardcode my static content host in my mustache templates.
 * I want to create one mustache template for each Mandrill template.
 * I want to be able to configure global as well as template-specific options.
 * I want to be able to see the output before publishing to Mandrill.
 * I want to publish templates to Mandrill as drafts before publishing as live.
 * I want to be able to add suffixes to my templates to denote that they are staging templates, e.g.

Try it out
----------

First update `config.json` where

 * `mandrillApiKey` is your Mandrill API Key.
 * `includeBaseDir` points to the "includes" folder e.g. `/.../mandrill-template-manager/example/includes/`.
 * `cssBaseDir` points to the "styles" folder e.g. `/.../mandrill-template-manager/example/styles/`.

Run `php example.php welcome.mustache welcome.json draft -test` (WARNING: this will create a "Template Manager Test-test" template in your Mandrill account!)

 * `welcome.mustache` is the mustache template for the example Mandrill template.
 * `welcome.json` represents template-specific options, which will override the global options in `config.json`.
 * With the default configuration, the script will sort out the two includes, apply the two css files, create the output files, and publish the Mandrill template as a draft.
