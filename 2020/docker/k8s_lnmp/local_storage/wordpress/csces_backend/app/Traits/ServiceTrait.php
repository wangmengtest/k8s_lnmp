<?php

namespace App\Traits;

use App\Component\account\src\services\AccountFormatService;
use App\Component\account\src\services\cache\CacheOrgService;
use App\Component\room\src\services\cache\CacheRoomInvitedService;
use App\Component\room\src\services\cache\CacheRoomListService;
use App\Component\room\src\services\inav\CheckOnlineService;
use App\Component\room\src\services\InavService;
use App\Component\room\src\services\lists\RoomListService;
use App\Component\room\src\services\notify\RoomNoticeService;
use App\Component\room\src\services\RoomService;
use App\Component\room\src\services\script\RoomSyncService;
use App\Component\room\src\services\StatService;
use App\Component\room\src\services\ThirdStreamService;
use App\Component\room\src\services\TokenService;
use App\Component\account\src\services\AccountOrgService;
use App\Component\account\src\services\AccountSyncService;
use App\Component\export\src\services\ExportService;
use App\Component\room\src\services\format\RoomFormatService;
use App\Component\room\src\services\invited\RoomInvitedService;
use vhallComponent\access\services\AccessService;
use vhallComponent\access\services\GroupService;
use vhallComponent\access\services\RolesService;
use App\Component\account\src\services\AccountService;
use vhallComponent\action\services\ActionService;
use vhallComponent\action\services\RoleService;
use vhallComponent\admin\services\AdminService;
use vhallComponent\anchorManage\services\AnchorManageService;
use vhallComponent\broadcast\services\RebroadcastService;
use App\Component\chat\src\services\ChatService;
use App\Component\common\src\services\BigDataServices;
use App\Component\common\src\services\ExportProxyService;
use App\Component\common\src\services\FormService;
use App\Component\common\src\services\ReportServices;
use App\Component\common\src\services\UploadService;
use vhallComponent\config\services\ConfigInfoService;
use vhallComponent\cut\services\CutService;
use vhallComponent\diypage\services\DiypageService;
use vhallComponent\document\services\DocumentService;
use vhallComponent\exam\services\ExamService;
use vhallComponent\filterWord\services\FilterWordsService;
use vhallComponent\gift\services\GiftService;
use vhallComponent\invitecard\services\InvitecardService;
use vhallComponent\lottery\services\LotteryService;
use vhallComponent\menu\services\MenuService;
use vhallComponent\order\services\IncomeService;
use vhallComponent\order\services\OrderService;
use vhallComponent\paas\services\PaasChannelServices;
use vhallComponent\paas\services\PaasService;
use vhallComponent\pay\services\PayService;
use vhallComponent\pendant\services\PendantService;
use App\Component\perfctl\src\services\ConnectctlService;
use vhallComponent\photosignin\services\PhotoSignService;
use vhallComponent\publicforward\services\PublicforwardService;
use vhallComponent\qa\services\QaService;
use vhallComponent\question\services\QuestionService;
use App\Component\record\src\services\RecordService;
use vhallComponent\redpacket\services\RedpacketService;
use vhallComponent\reward\services\RewardService;
use vhallComponent\roomlike\services\RoomlikeService;
use vhallComponent\scrolling\services\ScrollingService;
use vhallComponent\sign\services\SignService;
use App\Component\sms\src\services\CodeService;
use vhallComponent\tag\services\TagService;
use vhallComponent\vote\services\VoteService;
use App\Component\watchlimit\src\services\WatchlimitService;
use Illuminate\Contracts\Container\BindingResolutionException;

trait ServiceTrait
{

    /**
     * @return AccountOrgService
     *
     * @throws BindingResolutionException
     */
    public function getAccountOrgService(): AccountOrgService
    {
        return app()->make(AccountOrgService::class);
    }

    /**
     * @return CacheOrgService
     *
     * @throws BindingResolutionException
     */
    public function getCacheOrgService(): CacheOrgService
    {
        return app()->make(CacheOrgService::class);
    }

    /**
     * @return AccountService
     *
     * @throws BindingResolutionException
     */
    public function getAccountsService(): AccountService
    {
        return app()->make(AccountService::class);
    }

    /**
     * @return AccountFormatService
     *
     * @throws BindingResolutionException
     */
    public function getAccountFormatService(): AccountFormatService
    {
        return app()->make(AccountFormatService::class);
    }

    /**
     * @return AccountSyncService
     *
     * @throws BindingResolutionException
     */
    public function getAccountSyncService(): AccountSyncService
    {
        return app()->make(AccountSyncService::class);
    }

    /**
     * @return ExportService
     *
     * @throws BindingResolutionException
     */
    public function getExportService(): ExportService
    {
        return app()->make(ExportService::class);
    }

    /**
     * @return InavService
     *
     * @throws BindingResolutionException
     */
    public function getInavService(): InavService
    {
        return app()->make(InavService::class);
    }

    /**
     * @return CheckOnlineService
     *
     * @throws BindingResolutionException
     */
    public function getCheckOnlineService(): CheckOnlineService
    {
        return app()->make(CheckOnlineService::class);
    }

    /**
     * @return RoomFormatService
     *
     * @throws BindingResolutionException
     */
    public function getRoomFormatService(): RoomFormatService
    {
        return app()->make(RoomFormatService::class);
    }

    /**
     * @return RoomSyncService
     *
     * @throws BindingResolutionException
     */
    public function getRoomSyncService(): RoomSyncService
    {
        return app()->make(RoomSyncService::class);
    }

    /**
     * @return RoomListService
     *
     * @throws BindingResolutionException
     */
    public function getRoomListService(): RoomListService
    {
        return app()->make(RoomListService::class);
    }


    /**
     * @return CacheRoomListService
     *
     * @throws BindingResolutionException
     */
    public function getCacheRoomListService(): CacheRoomListService
    {
        return app()->make(CacheRoomListService::class);
    }

    /**
     * @return RoomInvitedService
     *
     * @throws BindingResolutionException
     */
    public function getRoomInvitedService(): RoomInvitedService
    {
        return app()->make(RoomInvitedService::class);
    }


    /**
     * @return CacheRoomInvitedService
     *
     * @throws BindingResolutionException
     */
    public function getCacheRoomInvitedService(): CacheRoomInvitedService
    {
        return app()->make(CacheRoomInvitedService::class);
    }

    /**
     * @return RoomNoticeService
     *
     * @throws BindingResolutionException
     */
    public function getRoomNoticeService(): RoomNoticeService
    {
        return app()->make(RoomNoticeService::class);
    }

    /**
     * @return RoomService
     *
     * @throws BindingResolutionException
     */
    public function getRoomService(): RoomService
    {
        return app()->make(RoomService::class);
    }

    /**
     * @return StatService
     *
     * @throws BindingResolutionException
     */
    public function getStatService(): StatService
    {
        return app()->make(StatService::class);
    }

    /**
     * @return ThirdStreamService
     *
     * @throws BindingResolutionException
     */
    public function getThirdStreamService(): ThirdStreamService
    {
        return app()->make(ThirdStreamService::class);
    }

    /**
     * @return TokenService
     *
     * @throws BindingResolutionException
     */
    public function getTokenService(): TokenService
    {
        return app()->make(TokenService::class);
    }

    /**
     * @return AccessService
     *
     * @throws BindingResolutionException
     */
    public function getAccessService(): AccessService
    {
        return app()->make(AccessService::class);
    }

    /**
     * @return GroupService
     *
     * @throws BindingResolutionException
     */
    public function getGroupService(): GroupService
    {
        return app()->make(GroupService::class);
    }

    /**
     * @return RolesService
     *
     * @throws BindingResolutionException
     */
    public function getRolesService(): RolesService
    {
        return app()->make(RolesService::class);
    }

    /**
     * @return ActionService
     *
     * @throws BindingResolutionException
     */
    public function getActionService(): ActionService
    {
        return app()->make(ActionService::class);
    }

    /**
     * @return RoleService
     *
     * @throws BindingResolutionException
     */
    public function getRoleService(): RoleService
    {
        return app()->make(RoleService::class);
    }

    /**
     * @return AdminService
     *
     * @throws BindingResolutionException
     */
    public function getAdminService(): AdminService
    {
        return app()->make(AdminService::class);
    }

    /**
     * @return AnchorManageService
     *
     * @throws BindingResolutionException
     */
    public function getAnchorManageService(): AnchorManageService
    {
        return app()->make(AnchorManageService::class);
    }

    /**
     * @return RebroadcastService
     *
     * @throws BindingResolutionException
     */
    public function getRebroadcastService(): RebroadcastService
    {
        return app()->make(RebroadcastService::class);
    }

    /**
     * @return ChatService
     *
     * @throws BindingResolutionException
     */
    public function getChatService(): ChatService
    {
        return app()->make(ChatService::class);
    }

    /**
     * @return BigDataServices
     *
     * @throws BindingResolutionException
     */
    public function getBigDataService(): BigDataServices
    {
        return app()->make(BigDataServices::class);
    }

    /**
     * @return ExportProxyService
     *
     * @throws BindingResolutionException
     */
    public function getExportProxyService(): ExportProxyService
    {
        return app()->make(ExportProxyService::class);
    }

    /**
     * @return FormService
     *
     * @throws BindingResolutionException
     */
    public function getFormService(): FormService
    {
        return app()->make(FormService::class);
    }

    /**
     * @return ReportServices
     *
     * @throws BindingResolutionException
     */
    public function getReportService(): ReportServices
    {
        return app()->make(ReportServices::class);
    }

    /**
     * @return UploadService
     *
     * @throws BindingResolutionException
     */
    public function getUploadService(): UploadService
    {
        return app()->make(UploadService::class);
    }

    /**
     * @return ConfigInfoService
     *
     * @throws BindingResolutionException
     */
    public function getConfigInfoService(): ConfigInfoService
    {
        return app()->make(ConfigInfoService::class);
    }

    /**
     * @return CutService
     *
     * @throws BindingResolutionException
     */
    public function getCutService(): CutService
    {
        return app()->make(CutService::class);
    }

    /**
     * @return DiypageService
     *
     * @throws BindingResolutionException
     */
    public function getDiypageService(): DiypageService
    {
        return app()->make(DiypageService::class);
    }

    /**
     * @return DocumentService
     *
     * @throws BindingResolutionException
     */
    public function getDocumentService(): DocumentService
    {
        return app()->make(DocumentService::class);
    }

    /**
     * @return ExamService
     *
     * @throws BindingResolutionException
     */
    public function getExamService(): ExamService
    {
        return app()->make(ExamService::class);
    }

    /**
     * @return FilterWordsService
     *
     * @throws BindingResolutionException
     */
    public function getFilterWordsService(): FilterWordsService
    {
        return app()->make(FilterWordsService::class);
    }

    /**
     * @return GiftService
     *
     * @throws BindingResolutionException
     */
    public function getGiftService(): GiftService
    {
        return app()->make(GiftService::class);
    }

    /**
     * @return InvitecardService
     *
     * @throws BindingResolutionException
     */
    public function getInviteCardService(): InvitecardService
    {
        return app()->make(InvitecardService::class);
    }

    /**
     * @return LotteryService
     *
     * @throws BindingResolutionException
     */
    public function getLotteryService(): LotteryService
    {
        return app()->make(LotteryService::class);
    }

    /**
     * @return MenuService
     *
     * @throws BindingResolutionException
     */
    public function getMenuService(): MenuService
    {
        return app()->make(MenuService::class);
    }

    /**
     * @return IncomeService
     *
     * @throws BindingResolutionException
     */
    public function getIncomeService(): IncomeService
    {
        return app()->make(IncomeService::class);
    }

    /**
     * @return OrderService
     *
     * @throws BindingResolutionException
     */
    public function getOrderService(): OrderService
    {
        return app()->make(OrderService::class);
    }

    /**
     * @return PaasChannelServices
     *
     * @throws BindingResolutionException
     */
    public function getPaasChannelService(): PaasChannelServices
    {
        return app()->make(PaasChannelServices::class);
    }

    /**
     * @return PaasService
     *
     * @throws BindingResolutionException
     */
    public function getPaasService(): PaasService
    {
        return app()->make(PaasService::class);
    }

    /**
     * @return PayService
     *
     * @throws BindingResolutionException
     */
    public function getPayService(): PayService
    {
        return app()->make(PayService::class);
    }

    /**
     * @return PendantService
     *
     * @throws BindingResolutionException
     */
    public function getPendantService(): PendantService
    {
        return app()->make(PendantService::class);
    }

    /**
     * @return ConnectctlService
     *
     * @throws BindingResolutionException
     */
    public function getConnectctlService(): ConnectctlService
    {
        return app()->make(ConnectctlService::class);
    }

    /**
     * @return PhotoSignService
     *
     * @throws BindingResolutionException
     */
    public function getPhotoSignService(): PhotoSignService
    {
        return app()->make(PhotoSignService::class);
    }

    /**
     * @return PublicforwardService
     *
     * @throws BindingResolutionException
     */
    public function getPublicForwardService(): PublicforwardService
    {
        return app()->make(PublicforwardService::class);
    }

    /**
     * @return QaService
     *
     * @throws BindingResolutionException
     */
    public function getQaService(): QaService
    {
        return app()->make(QaService::class);
    }

    /**
     * @return QuestionService
     *
     * @throws BindingResolutionException
     */
    public function getQuestionService(): QuestionService
    {
        return app()->make(QuestionService::class);
    }

    /**
     * @return RecordService
     *
     * @throws BindingResolutionException
     */
    public function getRecordService(): RecordService
    {
        return app()->make(RecordService::class);
    }

    /**
     * @return RedpacketService
     *
     * @throws BindingResolutionException
     */
    public function getRedpacketService(): RedpacketService
    {
        return app()->make(RedpacketService::class);
    }

    /**
     * @return RewardService
     *
     * @throws BindingResolutionException
     */
    public function getRewardService(): RewardService
    {
        return app()->make(RewardService::class);
    }

    /**
     * @return RoomlikeService
     *
     * @throws BindingResolutionException
     */
    public function getRoomlikeService(): RoomlikeService
    {
        return app()->make(RoomlikeService::class);
    }

    /**
     * @return ScrollingService
     *
     * @throws BindingResolutionException
     */
    public function getScrollingService(): ScrollingService
    {
        return app()->make(ScrollingService::class);
    }

    /**
     * @return SignService
     *
     * @throws BindingResolutionException
     */
    public function getSignService(): SignService
    {
        return app()->make(SignService::class);
    }

    /**
     * @return CodeService
     *
     * @throws BindingResolutionException
     */
    public function getCodeService(): CodeService
    {
        return app()->make(CodeService::class);
    }

    /**
     * @return TagService
     *
     * @throws BindingResolutionException
     */
    public function getTagService(): TagService
    {
        return app()->make(TagService::class);
    }

    /**
     * @return VoteService
     *
     * @throws BindingResolutionException
     */
    public function getVoteService(): VoteService
    {
        return app()->make(VoteService::class);
    }

    /**
     * @return WatchlimitService
     *
     * @throws BindingResolutionException
     */
    public function getWatchlimitService(): WatchlimitService
    {
        return app()->make(WatchlimitService::class);
    }
}
