Example
=======

This example demonstrates how to use `MandrillTemplateManager` in real life. Although this is meant to be an example, I do actually use the `example.php` code for my own Mandrill workflow.

Here is my scenario:

 * I have a bunch of standard includes/widgets e.g. `header.mustache`.
 * I do not want any inline css in any of my mustache templates.
 * I am happy to include any media query css directly inside `<style></style>` in `header.mustache`.
 * I do not want to hardcode my static content host in my mustache templates.
 * I want to create one mustache template for each Mandrill template.
 * I want to be able to configure global as well as template-specific options.
 * I want to be able to see the output before publishing to Mandrill
 * I only want to publish templates to Mandrill as drafts.
