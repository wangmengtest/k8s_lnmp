<?php


namespace vhallComponent\invitecard\models;

use vhallComponent\decouple\models\WebBaseModel;

/**
 * @package App\Models
 */

class RoomInviteModel extends WebBaseModel
{
    protected $table = 'room_invites';

    protected $primaryKey = 'id';

    protected $attributes = [
        'room_id' => '',
        'invite_id' => '',
        'be_invited_id' => '',
        'created_at' => '1970-01-01 00:00:00',
        'updated_at' => '1970-01-01 00:00:00'
    ];

    public function createInvite($roomId, $inviteId, $beInviteId)
    {
        $arr = ['room_id'=>$roomId, 'invite_id'=> $inviteId, 'be_invited_id' => $beInviteId];
        $invite = $this->where($arr)->first();
        if (empty($invite)) {
            $this->create($arr);
        }
        return true;
    }
}
