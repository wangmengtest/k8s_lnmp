<?php
/**
 * This file is config part of project.
 *
 * This config this compatible vhall legacy component, because depend on Illuminate database package
 * So add class alias for hyperf database adapter, that works well the package is loaded via composer
 * Collect the aliases in a separate config file, and include it additionally with composer
 */

@class_alias('App\Component\room\src\constants\CachePrefixConstant', 'vhallComponent\room\constants\CachePrefixConstant');
@class_alias('App\Component\room\src\constants\InavGlobalConstant', 'vhallComponent\room\constants\InavGlobalConstant');
@class_alias('App\Component\room\src\constants\RoomAttendsConstant', 'vhallComponent\room\constants\RoomAttendsConstant');
@class_alias('App\Component\room\src\constants\RoomConstant', 'vhallComponent\room\constants\RoomConstant');
@class_alias('App\Component\room\src\constants\RoomJoinRoleNameConstant', 'vhallComponent\room\constants\RoomJoinRoleNameConstant');
@class_alias('App\Component\room\src\constants\RspStructConstant', 'vhallComponent\room\constants\RspStructConstant');
@class_alias('App\Component\room\src\constants\TokenConstant', 'vhallComponent\room\constants\TokenConstant');
@class_alias('App\Component\watchlimit\src\constants\WatchlimitConstant', 'vhallComponent\watchlimit\constants\WatchlimitConstant');
@class_alias('App\Component\record\src\constants\RecordConstant', 'vhallComponent\record\constants\RecordConstant');
@class_alias('App\Component\chat\src\constants\ChatConstant', 'vhallComponent\chat\constants\ChatConstant');
@class_alias('App\Component\common\src\constants\KeyPrefix', 'vhallComponent\common\constants\KeyPrefix');
@class_alias('App\Component\perfctl\src\constants\PerfctlConstants', 'vhallComponent\perfctl\constants\PerfctlConstants');
