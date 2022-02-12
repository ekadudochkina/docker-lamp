<?php

namespace Hs\Redmine;

/**
 * Время затраченное на задачи
 *
 * @package Hs\Redmine
 * @property Hs\Redmine\User $user Пользователь
 * @property Hs\Redmine\Issue $issue Задача
 * @property Hs\Redmine\Project $project Проект
 * @author Sarychev Aleksey <freddis336@gmail.com>
 */
class SpentTime extends \RemappedActiveRecord
{

    public $issueId;
    public $projectId;
    public $userId;
    public $hours;
    public $description;
    public $activityId;
    public $date;
    public $year;
    public $month;
    public $week;
    public $creationDate;
    public $updateDate;

    /**
     * Получение массива конвертации имен полей
     * @return string[]
     */
    public function getMappings()
    {
        $arr = [];
        $arr["issue_id"] = "issueId";
        $arr["user_id"] = "userId";
        $arr["project_id"] = "projectId";
        //$arr["hours"] = "hours";
        $arr["comments"] = "description";
        $arr["activity_id"] = "activityId";
        $arr["spent_on"] = "date";
        $arr["tyear"] = "year";
        $arr["tmonth"] = "month";
        $arr["tweek"] = "week";
        $arr["created_on"] = "creationDate";
        $arr["updated_on"] = "updateDate";
        return $arr;
    }

    public function relations()
    {
        $arr = parent::relations();
        $arr["user"] = [self::BELONGS_TO, "Hs\Redmine\User", "user_id"];
        $arr["issue"] = [self::BELONGS_TO, "Hs\Redmine\Issue", "issue_id"];
        $arr["project"] = [self::BELONGS_TO, "Hs\Redmine\Project", "project_id"];
        return $arr;
    }

    public function tableName()
    {
        return "time_entries";
    }

}
