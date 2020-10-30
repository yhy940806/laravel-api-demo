<?php

namespace App\Events\Soundblock;

use Util;
use App\Models\Soundblock\Team;
use Illuminate\Support\Collection;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class InviteTeam {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Collection
     */
    public Collection $arrInfo;
    /**
     * @var Team
     */
    public Team $team;

    /**
     * Create a new event instance.
     * @param array|Collection $arrInfo
     *      $arrInfo = [
     *          'email'     => (string) Member email required.
     *          'user_role' => (string) Member role required.
     *      ]
     * @param Team $team
     *
     * @return void
     * @throws \Exception
     */
    public function __construct($arrInfo, Team $team) {
        if ($arrInfo instanceof Collection) {
            $this->arrInfo = $arrInfo;
        } else if (is_array($arrInfo) && Util::array_keys_exists(["email", "user_role"], $arrInfo)) {
            $this->arrInfo = collect()->push($arrInfo);
        } else {
            throw new \Exception("arrInfo is invalid parameter.", 417);
        }

        $this->team = $team;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn() {
        return new PrivateChannel('channel-name');
    }
}
