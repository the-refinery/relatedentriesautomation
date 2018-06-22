<?php
/**
 * relatedentriesautomation plugin for Craft CMS 3.x
 *
 * Provides a field specify selection criteria for providing related entries
 *
 * @link      http://the-refinery.io/
 * @copyright Copyright (c) 2018 The Refinery
 */

namespace therefinery\relatedentriesautomation\controllers;

use therefinery\relatedentriesautomation\Relatedentriesautomation;

use Craft;
use craft\web\Controller;

/**
 * Default Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    The Refinery
 * @package   Relatedentriesautomation
 * @since     0.2.0
 */
class DefaultController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = [];// ['index', 'do-something'];

    // Public Methods
    // =========================================================================

    /**
     * [listAvailableEntryTypes description]
     *
     * Action path: /actions/relatedEntriesAutomation/entriesinfo/listAvailableEntryTypes
     * 
     * @return [type] [description]
     */
    public function actionListAvailableEntryTypes()
    {
        // $this->requireAjaxRequest();
        // $this->requireAdmin();
        $this->requireLogin();

        $sections = array();

        $entryTypes = craft()->relatedEntriesAutomation_entriesinfo->availableEntryTypes();

        /* hierarchically organize entry types by section  */
        foreach ($entryTypes as $type) {
            $sectionHandle = $type['sectionHandle'];
            $entry = array(
                'handle' => $type['handle'],
                'name' => $type['name']
            );
            if(isset($sections[$sectionHandle])){
                $sections[$sectionHandle]['entries'][] = $entry;
            }else{
                $sections[$sectionHandle] = array(
                    'handle' => $sectionHandle,
                    'name' => $type['section'],
                    'entries' => array($entry)
                );
            }
        }
        /* strip out array keys so we can encode as JS array */
        $this->returnJson(array_values($sections));
    }

    /**
     * [actionListEntryFields description]
     *
     * Action path: /actions/relatedEntriesAutomation/entriesinfo/listEntryFields?typeHandle=events
     * 
     * @param  [type] $typeHandle [description]
     * @return [type]             [description]
     */
    public function actionListEntryFields($typeHandle)
    {
        // $this->requireAjaxRequest();
        // $this->requireAdmin();
        $this->requireLogin();

        $entryInfo = craft()->relatedEntriesAutomation_entriesinfo->ListEntryFields($typeHandle);

        $this->returnJson($entryInfo);
    }

    /**
     * [actionListCategories description]
     *
     * Action path: /actions/relatedEntriesAutomation/entriesinfo/listCategories?groupHandle=eventCategories
     * 
     * @param  [type] $groupHandle [description]
     * @return [type]              [description]
     */
    public function actionListCategories($groupHandle){
        // $this->requireAjaxRequest();
        // $this->requireAdmin();
        $this->requireLogin();

        $categories = craft()->relatedEntriesAutomation_entriesinfo->ListCategories($groupHandle);

        $this->returnJson($categories);
    }

    /**
     * [actionListAvailableEntries description]
     *
     * Action path: /actions/relatedEntriesAutomation/entriesinfo/listAvailableEntries?fieldHandle=contactInfo
     * 
     * @param  [type] $fieldHandle [description]
     * @return [type]              [description]
     */
    public function actionListAvailableEntries($fieldHandle){
        // $this->requireAdmin();
        $this->requireLogin();

        $categories = craft()->relatedEntriesAutomation_entriesinfo->ListAvailableEntries($fieldHandle);

        $this->returnJson($categories);
    }

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/relatedentriesautomation/default
     *
     * @return mixed
     */
    // public function actionIndex()
    // {
    //     $result = 'Welcome to the DefaultController actionIndex() method';

    //     return $result;
    // }

    /**
     * Handle a request going to our plugin's actionDoSomething URL,
     * e.g.: actions/relatedentriesautomation/default/do-something
     *
     * @return mixed
     */
    // public function actionDoSomething()
    // {
    //     $result = 'Welcome to the DefaultController actionDoSomething() method';

    //     return $result;
    // }
}
