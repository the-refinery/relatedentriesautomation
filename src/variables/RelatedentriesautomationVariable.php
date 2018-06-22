<?php
/**
 * relatedentriesautomation plugin for Craft CMS 3.x
 *
 * Provides a field specify selection criteria for providing related entries
 *
 * @link      http://the-refinery.io/
 * @copyright Copyright (c) 2018 The Refinery
 */

namespace therefinery\relatedentriesautomation\variables;

use therefinery\relatedentriesautomation\Relatedentriesautomation;

use Craft;

/**
 * relatedentriesautomation Variable
 *
 * Craft allows plugins to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.relatedentriesautomation }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    The Refinery
 * @package   Relatedentriesautomation
 * @since     0.2.0
 */
class RelatedentriesautomationVariable
{
    // Public Methods
    // =========================================================================

    /**
     * Whatever you want to output to a Twig template can go into a Variable method.
     * You can have as many variable functions as you want.  From any Twig template,
     * call it like this:
     *
     *     {{ craft.relatedentriesautomation.exampleVariable }}
     *
     * Or, if your variable requires parameters from Twig:
     *
     *     {{ craft.relatedentriesautomation.exampleVariable(twigValue) }}
     *
     * @param null $optional
     * @return string
     */
    public function exampleVariable($optional = null)
    {
        $result = "And away we go to the Twig template...";
        if ($optional) {
            $result = "I'm feeling optional today...";
        }
        return $result;
    }
}
