<?php

namespace Hs\Redmine;

/**
 * Связь между задачами
 *
 * @package Hs\Redmine
 * @property Hs\Redmine\Issue $fromIssue Задача от которой связь
 * @property Hs\Redmine\Issue $toIssue Задача к которой связь
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class IssueRelation extends \RemappedActiveRecord
{
    public $id;
    public $fromIssueId;
    public $toIssueId;
    public $type;
    public $delay;

    const RELATION_TYPE_RELATES = "relates";
    const RELATION_TYPE_BLOCKS = "blocks";
    const RELATION_TYPE_PRECEDES = "precedes";
    const RELATION_TYPE_COPIED_TO = "copied_to";

    /**
     * Получение массива конвертации имен полей
     * @return string[]
     */
    public function getMappings()
    {
        $arr = [];
        $arr["relation_type"] = "type";
        $arr["issue_from_id"] = "fromIssueId";
        $arr["issue_to_id"] = "toIssueId";
        return $arr;
    }

    public function relations()
    {
        $arr = parent::relations();
        $arr["fromIssue"] = [self::BELONGS_TO, "Hs\Redmine\Issue", "issue_from_id"];
        $arr["toIssue"] = [self::BELONGS_TO, "Hs\Redmine\Issue", "issue_to_id"];
        return $arr;
    }

    public function tableName()
    {
        return "issue_relations";
    }

}
