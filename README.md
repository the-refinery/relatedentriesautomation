# relatedentriesautomation plugin for Craft CMS 3.x

Provides a field specify selection criteria for providing related entries

## Using with templates
Calling the field by itself will return an object that defines the query criteria for finding related entries. To turn this raw data into a set of useful entries you have to pass it into a filter service function that can build a real-time query and return a list of entity ids for related entries. The easyist way to do that is to use the `craft.relatedentriesautomation.buildEnityQuery()` function.

```twig
{% set entryIds = craft.relatedentriesautomation.buildEnityQuery(block.sourceCriteria) %}
{% set relatedEntries = craft.entries(entryIds).visibility('not  hidden') %}
```
`relatedEntries` contains a list of entries you can iterate over using `{% for entry in relatedEntries %}` or a similar twig function.

## debuging output in templates
It's possible to outut the SQL that's used to fetch the result set to the page.

```twig
{% set queryinfo = craft.relatedentriesautomation.filterEntries(block.sourceCriteria) %}
{% set entryIds = craft.relatedentriesautomation.entityQueryWithIds(queryinfo.result) %}
<pre>{{ queryinfo | json_encode(constant('JSON_PRETTY_PRINT')) }}</pre>
{% set relatedEntries = craft.entries(entryIds).visibility('not  hidden') %}
```

## Requirements

This plugin requires Craft CMS 3.0.0 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then install the plugin:

		composer require the-refinery/relatedentriesautomation

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for RelatedEntriesAutomation.
