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
use craft\elements\Entry;
use craft\elements\Category;

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
        'Lightswitch',
        'PlainText',
        'Date',
        // 'RichText',
        'Redactor',
        // 'Matrix',
        'Categories',
        'Entries',
        'Assets',
        'Dropdown',
        'RadioButtons',
        'Checkboxes',
        'Number'
    );

    // Public Methods
    // =========================================================================
    public function availableEntryTypes()
    {
        $sections = (new Query())
        ->from('{{%sections}}')
        ->select('craft_sections.id, craft_sections.name as section, craft_sections.handle as sectionHandle, craft_sections.type, craft_entrytypes.name as name, craft_entrytypes.handle as handle, craft_entrytypes.id as typeid')
        ->where('type != "single"')
        ->andWhere('craft_entrytypes.dateDeleted IS NULL')
        ->join('join', '{{%entrytypes}}', 'craft_entrytypes.sectionId=craft_sections.id')
        ->addOrderBy('{{%sections}}.name')
        ->all();

        return $sections;
    }

    public function ListEntryFields($typeHandle){
        // Is this ID or a handle
        $entryFields = false;
        if(is_numeric($typeHandle)){
            $entryType = Craft::$app->sections->getEntryTypeById($typeHandle);
            if(count($entryType)){
                // $entryType[0] is an EntryModel
                $entryFields = $entryType->getFieldLayout()->getFields();
            }
        } else {
            /**
             * Retrieve any entrytypes that match the $typeHandle
             */
            $entryType = Craft::$app->sections->getEntryTypesByHandle($typeHandle);
            if(count($entryType)){
                // $entryType[0] is an EntryModel
                $entryFields = $entryType[0]->getFieldLayout()->getFields();
            }
        }
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

        $postDate = array(
            'id' => 0,
            'type' => 'Date',
            'name' => 'Post Date',
            'handle' => 'postDate'
        );
        $entryInfo[] = $postDate;

        

        if(count($entryFields)){
            // $entryType[0] is an EntryModel
            // $entryFields = $entryType[0]->getFieldLayout()->getFields();
            // $entryFields is an array of FieldLayoutFieldModel[]
            foreach ($entryFields as $fieldModel) {
                // $field = $fieldModel->getField(); // returns FieldModel
                // if(in_array($field->type, $this->allowedFieldTypes)){
                //     $entryInfo[] = $field;
                // }
                $fieldType = $this->getFieldsType($fieldModel);
                if(in_array($fieldType, $this->allowedFieldTypes)){
                    // $entryInfo[] = $fieldModel;
                    $entryInfo[] = array(
                        'id' => $fieldModel['id'],
                        'type' => $fieldType,
                        'name' => $fieldModel['name'],
                        'handle' => $fieldModel['handle']
                    );
                }
                // $entryInfo[] = $fieldModel;
            }
        }

        return $entryInfo;
    }

    /**
     * Gets a script usable FieldType name based on the field's class
     * @param  Object $fieldInstance An entity field
     * @return String                Usable field name
     */
    public function getFieldsType($fieldInstance){
        $classname = get_class($fieldInstance);
        if($classname === 'craft\redactor\Field'){
            return 'Redactor';
        }
        if ($pos = strrpos($classname, '\\')){
            return substr($classname, $pos + 1);
        }
        return $pos;
    }

    public function ListAvailableEntries($fieldHandle){
        $field = Craft::$app->fields->getFieldByHandle($fieldHandle);
        $attributes = $field->getAttributes();
        $sources = array();
        $entries = array();
        if(isset($attributes['sources'])){
            $sources = $attributes['sources'];
            foreach($sources as $sourceData){
                list($type, $sid) = explode(':', $sourceData);
                if ($type === 'section') {
                    $section = Craft::$app->sections->getSectionById($sid);
                    
                    // $criteria = Craft::$app->elements->getCriteria(ElementType::Entry);
                    // $criteria->section = $section->getAttribute('handle');
                    // $criteria->limit = null;
                    // $sectionInfo = array(
                    //     'attributes' => $section->getAttributes(),
                    //     'entries' => array()
                    // );
                    $sectionInfo = $section->getAttributes();
                    $sectionInfo['entries'] = array();

                    $found = Entry::find()
                        ->section($section->handle)
                        ->orderBy('title asc')
                        ->all();

                    foreach ($found as $entry) {
                        $sectionInfo['entries'][] = array(
                            'id' => $entry->id,
                            'name' => $entry->title
                        );
                    }

                    $entries[] = $sectionInfo;
                }
            }
        }
        return $entries;
        // return array('attributes' => $attributes, 'entries' => $entries);
    }

    /**
     * for the given fieldHandle (a category field) get the field's
     * category group(s) and return a list of categories in that group 
     * @param [type] $fieldHandle [description]
     */
    public function ListCategories($fieldHandle){
        // $criteria = Craft::$app->elements->getCriteria(ElementType::Category);
        $field = Craft::$app->fields->getFieldByHandle($fieldHandle);
        // $groupHandle = $this->getCategoryFieldInfo($fieldHandle)['groupHandle'];

        $categories = Category::find()
            ->groupId(str_replace('group:', '', $field->source))
            ->all();

        // $criteria->group = $groupHandle; // this is actually the field name, we need to handle situations when the don't match

        // $categories = $criteria->find();
        
        // $categories = Category::find()
        //     ->section($section->handle)
        //     ->all();
        // $catData = array();
        $catData = $this->loopCategories($categories, 1);

        return $catData;
        // return array('fieldinfo' => $field, 'group' => $categories, 'catData' => $catData);
    }

    private function loopCategories($categories, $level){
        $catData = array();
        foreach ($categories as $catObj) {
            $catRecord = $catObj->getAttributes();
            // $catData[] = $catRecord;
            if($level === (int)$catRecord['level']){
                $catRecord['title'] = $catObj->title;
                if ($catObj->getHasDescendants()) {
                    $catRecord['descendants'] = $this->loopCategories($catObj->getDescendants(), $level + 1);
                }
                $catData[] = $catRecord;
            }
        }
        return $catData;
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
