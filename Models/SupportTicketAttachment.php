<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class SupportTicketAttachment extends BaseModel {
    use SoftDeletes;

    protected $table = "support_tickets_attachments";

    protected $primaryKey = "row_id";

    protected string $uuid = "row_uuid";

    protected $hidden = [
        "row_id", "row_uuid", "message_id", "ticket_id",
        BaseModel::CREATED_AT, BaseModel::UPDATED_AT,
        BaseModel::DELETED_AT, BaseModel::STAMP_DELETED, BaseModel::STAMP_DELETED_BY,
    ];

    protected $guarded = [];
}
