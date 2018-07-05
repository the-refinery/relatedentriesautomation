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
use craft\db\Query;

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
    private $allowedFieldTypes = array(
        'craft\fields\Lightswitch',
        'craft\fields\PlainText',
        'craft\fields\Date',
        // 'RichText',
        'craft\redactor\Field',
        // 'Matrix',
        'craft\fields\Categories',
        'craft\fields\Entries',
        'craft\fields\Assets',
        'craft\fields\Dropdown',
        'craft\fields\RadioButtons',
        'craft\fields\Checkboxes',
        'craft\fields\Number'
    );

    // Public Methods
    // =========================================================================
    public function availableEntryTypes()
    {
        $sections = (new Query())
        ->from('{{%sections}}')
        ->select('craft_sections.id, craft_sections.name as section, craft_sections.handle as sectionHandle, craft_sections.type, craft_entrytypes.name as name, craft_entrytypes.handle as handle')
        ->where('type != "single"')
        ->join('join', '{{%entrytypes}}', 'craft_entrytypes.sectionId=craft_sections.id')
        ->addOrderBy('{{%sections}}.name')
        ->all();

        return $sections;
    }

    public function ListEntryFields($typeHandle){
        /**
         * title and postDate fields are not returned by `getFieldLayout` so we'll fake it
         */
        // $titleField = new FieldModel(array(
        //     'id' => 0,
        //     'type' => 'PlainText',
        //     'name' => 'Title',
        //     'handle' => 'title'
        // ));
        $titleField = array(
            'id' => 0,
            'type' => 'PlainText',
            'name' => 'Title',
            'handle' => 'title'
        );
        $entryInfo = array($titleField);

        // $postDate = new FieldModel(array(
        //     'id' => 0,
        //     'type' => 'Date',
        //     'name' => 'Post Date',
        //     'handle' => 'postDate'
        // ));
        // $entryInfo[] = $postDate;

        /**
         * Retrieve any entrytypes that match the $typeHandle
         */
        $entryType = Craft::$app->sections->getEntryTypesByHandle($typeHandle);

        if(count($entryType)){
            // $entryType[0] is an EntryModel
            $entryFields = $entryType[0]->getFieldLayout()->getFields();
            // $entryFields is an array of FieldLayoutFieldModel[]
            foreach ($entryFields as $fieldModel) {
                // $field = $fieldModel->getField(); // returns FieldModel
                // if(in_array($field->type, $this->allowedFieldTypes)){
                //     $entryInfo[] = $field;
                // }
                if(in_array(get_class($fieldModel), $this->allowedFieldTypes)){
                    $entryInfo[] = $fieldModel;
                }
                // $entryInfo[] = $fieldModel;
            }
        }

        return $entryInfo;
    }

    public function DumpEntryFields($typeHandle){
        $entryType = Craft::$app->sections->getEntryTypesByHandle($typeHandle);

        $fields = array();
        $entryFields = $entryType[0]->getFieldLayout()->getFields();
        foreach ($entryFields as $fieldModel) {
            $fields[] = get_class($fieldModel);
        }

        return $fields;
    }

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
