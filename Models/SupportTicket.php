<?php

namespace App\Models;

use App\Models\Core\Auth\AuthGroup;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupportTicket extends BaseModel {
    use SoftDeletes;

    protected $table = "support_tickets";

    protected $primaryKey = "ticket_id";

    protected string $uuid = "ticket_uuid";

    protected $hidden = [
        "ticket_id", "support_id", "user_id",
        BaseModel::CREATED_AT, BaseModel::UPDATED_AT,
        BaseModel::DELETED_AT, BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
    ];

    protected $guarded = [];

    public function support() {
        return ($this->belongsTo(Support::class, "support_id", "support_id"));
    }

    public function user() {
        return ($this->belongsTo(User::class, "user_id", "user_id"));
    }

    public function messages() {
        return ($this->hasMany(SupportTicketMessage::class, "ticket_id", "ticket_id"));
    }

    public function attachments() {
        return ($this->belongsToMany(SupportTicketAttachment::class, "ticket_id", "ticket_id"));
    }

    public function supportUser() {
        return $this->belongsToMany(User::class, "support_tickets_users", "ticket_id", "user_id");
    }

    public function supportGroup() {
        return $this->belongsToMany(AuthGroup::class, "support_tickets_groups", "ticket_id", "group_id");
    }
}
