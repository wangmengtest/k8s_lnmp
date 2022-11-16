<?php


namespace App\Component\room\src\constants;

class RoomJoinRoleNameConstant
{
    const HOST      = 1;

    const GUEST     = 4;

    const ASSISTANT = 3;

    const USER      = 2;

    const FLYING    = 5;//飞手

    //不包括主持人的role_ids
    const ROLES_WITHOUT_HOST = [2, 3, 4];

    const ROLES_ALL = [1, 2, 3, 4];
}
