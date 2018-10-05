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
 * EntriesFilterService Service
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
class EntriesFilterService extends Component
{
    // Public Variables
    // =========================================================================
    public $entriesQuery;

    // Public Methods
    // =========================================================================

    /**
     * Main entry from template pass you're criteria field to this function to
     * get a list of entry ids
     * ```twig
     * {% set entryIds = craft.relatedentriesautomation.buildEnityQuery(block.sourceCriteria) %}
    * {% set relatedEntries = craft.entries(entryIds).visibility('not  hidden') %}
     * ```
     * @param  [type] $criteria Relatedentriesautomation field raw data
     * @return array           List of Entry IDs
     */ 
    public function buildEnityQuery($criteria){
        $result = $this->filterEntries($criteria)['result'];
        return $this->entityQueryWithIds($result);
    }


    public function filterEntries($criteria)
    {
        // $entryTypes = $this->getEntryTypes(isset($criteria['entryTypes'])?$criteria['entryTypes']:array());

        $this->entriesQuery = (new Query())
        ->select('{{%entries}}.id, {{%entries}}.sectionId, {{%entries}}.typeId, ')
        ->from('{{%entries}}')
        ->join('join', '{{%elements}}', '{{%entries}}.id = {{%elements}}.id')
        ->join('join', '{{%content}}', '{{%entries}}.id = {{%content}}.elementId')
        //->join('{{relations}}', '{{entries}}.id = {{relations}}.sourceId')
        // ->leftJoin('{{relations}}', '{{entries}}.id = {{relations}}.sourceId')
        ->where('{{%elements}}.enabled = 1 AND ({{%entries}}.expiryDate IS NULL OR {{%entries}}.expiryDate > NOW() )')
        ->andWhere('{{%entries}}.postDate < NOW()')
        ->addOrderBy($this->orderClause($criteria))
        ->addGroupBy('{{%entries}}.id')
        ->limit($criteria['limit']);

        // additional WHERE clause for each entryType
        if (isset($criteria['entryTypes']) && count($criteria['entryTypes'])) {
            // $typeIds = $this->entityQueryWithIds($entryTypes)['id'];
            // $entriesQuery->andWhere(
            //  array('IN', 'typeId', $typeIds)
            // );
            $where = join(' OR ', $this->whereClause($criteria['entryTypes']));
            $this->entriesQuery->andWhere($where);
        }

        $result = $this->entriesQuery->all();
        $query = $this->entriesQuery->getRawSql();

        return array('query' => $query, 'result' => $result);
    }
    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     Relatedentriesautomation::$plugin->entriesFilterService->exampleService()
     *
     * @return mixed
     */
    // public function exampleService()
    // {
    //     $result = 'something';

    //     return $result;
    // }
    

    // Internal methods (public for testing)
    // =========================================================================

    /**
     * [orderClause description]
     * @param  [type] $criteria [description]
     * @return [type]           [description]
     */
    public function orderClause($criteria){
        $order = '{{%entries}}.postDate';

        if(isset($criteria['order'])){
            switch ($criteria['order']) {
                case 'title':
                    $order = '{{%content}}.title';
                    break;
                case 'expiryDate':
                    $order = '{{%entries}}.expiryDate';
                    break;
                case 'dateCreated':
                    $order = '{{%entries}}.dateCreated';
                    break;
                case 'dateUpdated':
                    $order = '{{%entries}}.dateUpdated';
                    break;
                case 'random':
                    $order = 'RAND()';
                    return $order;
                    break;
                default:
                    $order = '{{%entries}}.postDate';
                    break;
            }
        }

        if(isset($criteria['orderDir'])){
            $order .= " {$criteria['orderDir']}";
        }else{
            $order .= ' desc';
        }

        return $order;
    }

    public function whereClause($entryParams){
        $where = array();
        $char = 65;

        foreach ($entryParams as $query) {
            $attributes = Craft::$app->sections->getEntryTypesByHandle($query['handle'])[0]->getAttributes();
            $clause = "`typeId` = {$attributes['id']}";
            $relations = array();

            if (isset($query['params'])) {
                foreach ($query['params'] as $param) {
                    if($param['handle'] === 'title' OR $param['handle'] === 'postDate'){
                        $fieldName = $param['handle'];
                    }else{
                        $field = Craft::$app->fields->getFieldByHandle($param['handle']);
                        $fieldId = $field->id;
                        $fieldName = 'field_' . $param['handle'];
                    }
                    $value = $param['value'];
                    if($param['operator'] === 'LIKE'){
                        $value = "%".$value.'%';
                        $clause .= " AND {{%content}}.{$fieldName} {$param['operator']} '{$value}' ";
                    }
                    if($param['operator'] === 'SWITCH'){
                        $value = $value ? 1:0;
                        // $value = "%".$value.'%';
                        $clause .= " AND {{%content}}.{$fieldName} = {$value} ";
                    }
                    if($param['operator'] == 'RELATEDTO'){
                        $joinName = 'join_' . chr($char++);
                        $this->entriesQuery->join('join', "{{%relations}} {$joinName}", "{{%entries}}.id = {$joinName}.sourceId AND {$joinName}.fieldId = {$fieldId}");
                        // $relations[] = $value;
                        $clause .= " AND {$joinName}.targetId = '{$value}' ";
                    }
                    if($param['operator'] == 'NOTRELATEDTO'){
                        $joinName = 'join_' . chr($char++);
                        $this->entriesQuery->leftJoin(
                            "{{%relations}} {$joinName}", 
                            "{{%entries}}.id = {$joinName}.sourceId AND {$joinName}.targetId = '{$value}' AND {$joinName}.fieldId = {$fieldId}"
                        );
                        // $relations[] = $value;
                        $clause .= " AND {$joinName}.targetId IS NULL ";
                    }
                    if($param['operator'] == 'ISONEOF'){
                        $joinName = 'join_' . chr($char++);
                        $this->entriesQuery->join('join', "{{%relations}} {$joinName}", "{{%entries}}.id = {$joinName}.sourceId AND {$joinName}.fieldId = {$fieldId}");
                        $clause .= " AND {$joinName}.targetId IN({$value}) ";
                    }
                    if($param['operator'] == 'ISNOTONEOF'){
                       $joinName = 'join_' . chr($char++);
                        $this->entriesQuery->join('join', "{{%relations}} {$joinName}", "{{%entries}}.id = {$joinName}.sourceId AND {$joinName}.fieldId = {$fieldId}");
                        $clause .= " AND {$joinName}.targetId NOT IN({$value}) ";
                       
                    }
                    if($param['operator'] == '+' or $param['operator'] == '-'){
                        $operator = $param['operator'] == '+' ? '<' : '>';
                        $table = "{{%content}}";
                        if ($param['handle'] === 'postDate') {
                            $table = "{{%entries}}";
                        }
                        $clause .= " AND {$table}.{$fieldName} {$operator} ( NOW() {$param['operator']} INTERVAL {$value} DAY )";
                    }
                    // check to see if asset field has one or more items
                    if($param['operator'] == 'ISSET'){
                        // need fieldId 
                        $joinName = 'join_' . chr($char++);
                        $fieldId = $this->getFieldIdByHandle($param['handle']);
                        $this->entriesQuery->join('join', "{{%relations}} {$joinName}", "{{%entries}}.id = {$joinName}.sourceId");
                        $clause .= " AND {$joinName}.fieldId = {$fieldId}";
                    }
                    // check to see if asset field is empty
                    if($param['operator'] == 'UNSET'){
                        $joinName = 'join_' . chr($char++);
                        $fieldId = $this->getFieldIdByHandle($param['handle']);
                        $this->entriesQuery->leftJoin("{{%relations}} {$joinName}", "{{%entries}}.id = {$joinName}.sourceId AND {$joinName}.fieldId = {$fieldId}");
                        $clause .= " AND {$joinName}.id IS NULL ";
                    }
                }
                // if(count($relations)){
                //     $relationsString = join(', ', $relations);
                //     $clause .= " AND {{relations}}.targetId IN ({$relationsString}) ";
                // }
            }

            $where[] = " ( " . $clause . " ) ";
        }

        return $where;
    }

    public function getFieldIdByHandle($handle){
        $fields = (new Query())
        ->from('{{%fields}}')
        ->select('{{%fields}}.id, {{%fields}}.handle')
        ->where(array('handle' => $handle))
        ->all();

        if (count($fields)) {
            return (int)$fields[0]['id'];
        }else{
            return 0;
        }
    }

    /**
     * searches the suppied entries results set and pulls out any IDs
     * it finds, returns an object with the id lists in a format 
     * suitable for use in a ElementCriteriaModel query
     * @return [type] [description]
     */
    public function entityQueryWithIds($entries){
        $ids = array_map(array($this, 'findId'), $entries);
        return array( 'id' => $ids );
    }

    public function findId($entry){
        return isset($entry['id']) ? $entry['id'] : null;
    }

}
