<?php

namespace App\Providers;

use App\Listeners\{PaginationListener,
    Common\CreateServiceListener,
    Common\JobExceptionOccuredListener,
    Common\MessageSentListener,
    Common\PrivateNotificationListener,
    Accounting\CreateTransactionListener,
    Office\UpdateDeploymentListener,
    Office\Support\TicketAttachListener,
    Soundblock\CreateContractListener,
    Soundblock\InviteGroupListener,
    Soundblock\CreateProjectListener,
    Soundblock\InviteTeamListener,
    Soundblock\OnHistoryListener,
    Soundblock\ProjectGroupListener,
    Soundblock\ProjectNoteAttachListener,
    Soundblock\ServiceNoteAttachListener,
    User\DeleteEmailListener,
    User\UserNoteAttachListener,
    User\DeleteUserNoteListener};
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        "Dingo\Api\Event\ResponseWasMorphed" => [
            PaginationListener::class
        ],

        "App\Events\Soundblock\CreateProject" => [
            CreateProjectListener::class
        ],
        "App\Events\Common\CreateService" => [
            CreateServiceListener::class
        ],
        "App\Events\Soundblock\OnHistory" => [
            OnHistoryListener::class
        ],
        "App\Events\Common\PrivateNotification" => [
            PrivateNotificationListener::class
        ],
        "App\Events\Soundblock\CreateContract" => [
            CreateContractListener::class
        ],
        "App\Events\Soundblock\InviteGroup" => [
            InviteGroupListener::class
        ],
        "App\Events\Soundblock\InviteTeam" => [
            InviteTeamListener::class
        ],
        "App\Events\Office\UpdateDeployment" => [
            UpdateDeploymentListener::class
        ],
        "App\Events\Soundblock\ProjectNoteAttach" => [
            ProjectNoteAttachListener::class
        ],
        "App\Events\Soundblock\ServiceNoteAttach" => [
            ServiceNoteAttachListener::class
        ],
        "App\Events\User\UserNoteAttach" => [
            UserNoteAttachListener::class
        ],
        "App\Events\Office\Support\TicketAttach" => [
            TicketAttachListener::class
        ],
        "App\Events\User\DeleteUserNote" => [
            DeleteUserNoteListener::class
        ],
        "App\Events\User\DeleteEmail" => [
            DeleteEmailListener::class
        ],
        "App\Events\Accounting\CreateTransaction" => [
            CreateTransactionListener::class
        ],
        "App\Events\Soundblock\ProjectGroup" => [
            ProjectGroupListener::class
        ],
        "Illuminate\Queue\Events\JobExceptionOccurred" => [
            JobExceptionOccuredListener::class
        ],
        "Illuminate\Queue\Events\JobFailed" => [
            JobExceptionOccuredListener::class
        ],
        "Illuminate\Mail\Events\MessageSent" => [
            MessageSentListener::class
        ]
    ];

    protected $subscribe = [];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
