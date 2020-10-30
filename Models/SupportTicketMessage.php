<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class SupportTicketMessage extends BaseModel {
    use SoftDeletes;

    protected $table = "support_tickets_messages";

    protected $primaryKey = "message_id";

    protected string $uuid = "message_uuid";

    protected $hidden = [
        "message_id", "ticket_id", "user_id",
        BaseModel::CREATED_AT, BaseModel::UPDATED_AT,
        BaseModel::DELETED_AT, BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
    ];

    protected $guarded = [];

    public function ticket() {
        return ($this->belongsTo(SupportTicket::class, "ticket_id", "ticket_id"));
    }

    public function user() {
        return ($this->belongsTo(User::class, "user_id", "user_id"));
    }

    public function attachments() {
        return ($this->hasMany(SupportTicketAttachment::class, "message_id", "message_id"));
    }
}
