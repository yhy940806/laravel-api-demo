<?php

namespace App\Mail\Soundblock;

use App\Models\{
    Core\App,
    Soundblock\Invites,
    Soundblock\Project
};
use Illuminate\Mail\Mailable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class InviteMail extends Mailable implements ShouldQueue {
    use Queueable, SerializesModels;

    /**
     * @var Project
     */
    private Project $project;
    /**
     * @var App
     */
    private App $app;
    /**
     * @var Invites|null
     */
    private ?Invites $invite;

    /**
     * Create a new message instance.
     *
     * @param Project $project
     * @param App $app
     * @param Invites|null $invite
     */
    public function __construct(Project $project, App $app, ?Invites $invite = null) {
        $this->project = $project;
        $this->app = $app;
        $this->invite = $invite;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        $frontendUrl = app_url("soundblock", "http://localhost:8200");

        $this->withSwiftMessage(function ($message) {
            $message->app = $this->app;
        });

        if (is_null($this->invite)) {
            $frontendUrl .= "project/{$this->project->project_uuid}/contract";
        } else {
            $frontendUrl .= "invite/{$this->invite->invite_hash}";
        }

        return ($this->view("mail.soundblock.invite"))->with(["frontendUrl" => $frontendUrl]);
    }
}
