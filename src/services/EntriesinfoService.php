<?php
/**
 * relatedentriesautomation plugin for Craft CMS 3.x
 *
 * Provides a field specify selection criteria for providing related entries
 *
 * @link      http://the-refinery.io/
 * @copyright Copyright (c) 2018 The Refinery
 */

namespace therefinery\relatedentriesautomation\services;

use therefinery\relatedentriesautomation\Relatedentriesautomation;

use Craft;
use craft\base\Component;

/**
 * EntriesinfoService Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    The Refinery
 * @package   Relatedentriesautomation
 * @since     0.2.0
 */
class EntriesinfoService extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     Relatedentriesautomation::$plugin->entriesinfoService->exampleService()
     *
     * @return mixed
     */
    public function exampleService()
    {
        $result = 'something';

        return $result;
    }
}
