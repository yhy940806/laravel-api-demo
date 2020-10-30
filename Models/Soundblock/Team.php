<?php

namespace App\Models\Soundblock;

use App\Models\BaseModel;
use App\Models\User;

class Team extends BaseModel
{
    protected $table = "soundblock_projects_teams";

    protected $primaryKey = "team_id";

    protected $hidden = [
        "team_id", "user_id", BaseModel::DELETED_AT,  BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY, "project_id", "service_id",
        BaseModel::STAMP_CREATED_BY, BaseModel::STAMP_UPDATED_BY, BaseModel::CREATED_AT, BaseModel::UPDATED_AT,
        "pivot"
    ];

    protected $guarded = [];

    protected string $uuid = "team_uuid";

    public function project()
    {
        return($this->belongsTo(Project::class, "project_id", "project_id"));
    }

    public function users()
    {
        return($this->belongsToMany(User::class, "soundblock_projects_teams_users", "team_id", "user_id", "team_id", "user_id")
                    ->whereNull("soundblock_projects_teams_users." . BaseModel::STAMP_DELETED)
                    ->withPivot("team_uuid", "user_uuid", "user_payout", "user_role")
                    ->withTimestamps(BaseModel::CREATED_AT, BaseModel::UPDATED_AT));
    }

    public function usersWithTrashed()
    {
        return($this->belongsToMany(User::class, "soundblock_projects_teams_users", "team_id", "user_id", "team_id", "user_id")
                    ->withPivot("team_uuid", "user_uuid", "user_payout", "user_role")
                    ->withTimestamps(BaseModel::CREATED_AT, BaseModel::UPDATED_AT));
    }

    public function invite()
    {
        return($this->morphMany(Invites::class, "invitable", "table_name", "table_id"));
    }

}
