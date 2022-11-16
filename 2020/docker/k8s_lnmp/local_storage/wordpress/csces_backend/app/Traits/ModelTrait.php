<?php

namespace App\Traits;

use App\Component\account\src\models\AccountsModel;
use App\Component\account\src\models\AnchorExtendsModel;
use App\Component\export\src\models\ExportModel;
use App\Component\room\src\models\RoomAttendsAllModel;
use App\Component\room\src\models\RoomInvitedModel;
use vhallComponent\access\models\AccessModel;
use vhallComponent\access\models\AccessOpLogModel;
use vhallComponent\access\models\GroupAccessModel;
use vhallComponent\access\models\GroupModel;
use vhallComponent\access\models\RoleAccessModel;
use vhallComponent\access\models\UserGroupModel;
use vhallComponent\access\models\UserRoleModel;
use vhallComponent\action\models\ActionsModel;
use vhallComponent\action\models\RoleActionsModel;
use vhallComponent\action\models\RoleMenuesModel;
use vhallComponent\action\models\RoleModel;
use vhallComponent\admin\models\AdminsModel;
use vhallComponent\anchorManage\models\AnchorManageModel;
use vhallComponent\anchorManage\models\AnchorRoomLkModel;
use vhallComponent\broadcast\models\RebroadCastModel;
use vhallComponent\config\models\ConfigInfoModel;
use vhallComponent\document\models\DocumentStatusModel;
use vhallComponent\document\models\RoomDocumentsModel;
use vhallComponent\exam\models\ExamAnswersModel;
use vhallComponent\exam\models\ExamsModel;
use vhallComponent\exam\models\RoomExamLkModel;
use vhallComponent\filterWord\models\FilterWordsLogModel;
use vhallComponent\filterWord\models\FilterWordsModel;
use vhallComponent\invitecard\models\InviteCardModel;
use vhallComponent\invitecard\models\RoomInviteModel;
use vhallComponent\lottery\models\LotteryUserModel;
use vhallComponent\menu\models\MenuesModel;
use vhallComponent\order\models\IncomeModel;
use vhallComponent\order\models\OrderDetailModel;
use vhallComponent\paas\models\NoticeModel;
use vhallComponent\pendant\models\PendantModel;
use vhallComponent\pendant\models\PendantOperateRecordModel;
use vhallComponent\pendant\models\PendantStatsModel;
use vhallComponent\photosignin\models\PhotoSignImgModel;
use vhallComponent\photosignin\models\PhotoSignRecordModel;
use vhallComponent\photosignin\models\PhotoSignTaskModel;
use vhallComponent\question\models\QuestionAnswerLogsModel;
use vhallComponent\question\models\QuestionAnswersModel;
use vhallComponent\question\models\QuestionLogsModel;
use vhallComponent\question\models\QuestionsModel;
use vhallComponent\question\models\RoomQuestionLkModel;
use App\Component\record\src\models\RecordAttendsModel;
use App\Component\record\src\models\RecordModel;
use App\Component\record\src\models\RecordStatsModel;
use App\Component\room\src\models\DibblingModel;
use App\Component\room\src\models\InavStatsModel;
use App\Component\room\src\models\RoomAttendsModel;
use App\Component\room\src\models\RoomConnectCountsModel;
use App\Component\room\src\models\RoomExtendsModel;
use App\Component\room\src\models\RoomJoinsModel;
use App\Component\room\src\models\RoomStatsModel;
use App\Component\room\src\models\RoomSupplyModel;
use App\Component\room\src\models\RoomsModel;
use App\Component\room\src\models\ThirdStreamModel;
use vhallComponent\roomlike\models\RoomLikeModel;
use vhallComponent\scrolling\models\ScrollingModel;
use vhallComponent\tag\models\TagModel;
use vhallComponent\vote\models\RoomVoteLkModel;
use vhallComponent\vote\models\VoteAnswersModel;
use vhallComponent\vote\models\VoteOptionCountModel;
use vhallComponent\vote\models\VotesModel;
use App\Component\watchlimit\src\models\ApplyModel;
use App\Component\watchlimit\src\models\ApplyUsersModel;
use App\Component\watchlimit\src\models\WhiteAccountsModel;
use App\Component\account\src\models\AccountOrgModel;
use App\Http\Modules\Health\models\OpsMonitorModel;

trait ModelTrait
{

    /**
     * @return AccountOrgModel
     */
    public function getAccountOrgModel(): AccountOrgModel
    {
        return new AccountOrgModel();
    }

    /**
     * @return AccountsModel
     */
    public function getAccountsModel(): AccountsModel
    {
        return new AccountsModel();
    }

    /**
     * @return AnchorExtendsModel
     */
    public function getAnchorExtendsModel(): AnchorExtendsModel
    {
        return new AnchorExtendsModel();
    }

    /**
     * @return ExportModel
     */
    public function getExportModel(): ExportModel
    {
        return new ExportModel();
    }

    /**
     * @return DibblingModel
     */
    public function getDibblingModel(): DibblingModel
    {
        return new DibblingModel();
    }

    /**
     * @return InavStatsModel
     */
    public function getInavStatsModel(): InavStatsModel
    {
        return new InavStatsModel();
    }

    /**
     * @return RoomAttendsModel
     */
    public function getRoomAttendsModel(): RoomAttendsModel
    {
        return new RoomAttendsModel();
    }

    /**
     * @return RoomConnectCountsModel
     */
    public function getRoomConnectCountsModel(): RoomConnectCountsModel
    {
        return new RoomConnectCountsModel();
    }

    /**
     * @return RoomExtendsModel
     */
    public function getRoomExtendsModel(): RoomExtendsModel
    {
        return new RoomExtendsModel();
    }

    /**
     * @return RoomInvitedModel
     */
    public function getRoomInvitedModel(): RoomInvitedModel
    {
        return new RoomInvitedModel();
    }

    /**
     * @return RoomJoinsModel
     */
    public function getRoomJoinsModel(): RoomJoinsModel
    {
        return new RoomJoinsModel();
    }

    /**
     * @return RoomStatsModel
     */
    public function getRoomStatsModel(): RoomStatsModel
    {
        return new RoomStatsModel();
    }

    /**
     * @return RoomSupplyModel
     */
    public function getRoomSupplyModel(): RoomSupplyModel
    {
        return new RoomSupplyModel();
    }

    /**
     * @return RoomsModel
     */
    public function getRoomsModel(): RoomsModel
    {
        return new RoomsModel();
    }

    /**
     * @return ThirdStreamModel
     */
    public function getThirdStreamModel(): ThirdStreamModel
    {
        return new ThirdStreamModel();
    }

    /**
     * @return AccessModel
     */
    public function getAccessModel(): AccessModel
    {
        return new AccessModel();
    }

    /**
     * @return AccessOpLogModel
     */
    public function getAccessOpLogModel(): AccessOpLogModel
    {
        return new AccessOpLogModel();
    }

    /**
     * @return GroupAccessModel
     */
    public function getGroupAccessModel(): GroupAccessModel
    {
        return new GroupAccessModel();
    }

    /**
     * @return GroupModel
     */
    public function getGroupModel(): GroupModel
    {
        return new GroupModel();
    }

    /**
     * @return RoleAccessModel
     */
    public function getRoleAccessModel(): RoleAccessModel
    {
        return new RoleAccessModel();
    }

    /**
     * @return UserGroupModel
     */
    public function getUserGroupModel(): UserGroupModel
    {
        return new UserGroupModel();
    }

    /**
     * @return UserRoleModel
     */
    public function getUserRoleModel(): UserRoleModel
    {
        return new UserRoleModel();
    }

    /**
     * @return ActionsModel
     */
    public function getActionsModel(): ActionsModel
    {
        return new ActionsModel();
    }

    /**
     * @return RoleActionsModel
     */
    public function getRoleActionsModel(): RoleActionsModel
    {
        return new RoleActionsModel();
    }

    /**
     * @return RoleMenuesModel
     */
    public function getRoleMenuesModel(): RoleMenuesModel
    {
        return new RoleMenuesModel();
    }

    /**
     * @return RoleModel
     */
    public function getRoleModel(): RoleModel
    {
        return new RoleModel();
    }

    /**
     * @return AdminsModel
     */
    public function getAdminsModel(): AdminsModel
    {
        return new AdminsModel();
    }

    /**
     * @return AnchorManageModel
     */
    public function getAnchorManageModel(): AnchorManageModel
    {
        return new AnchorManageModel();
    }

    /**
     * @return AnchorRoomLkModel
     */
    public function getAnchorRoomLkModel(): AnchorRoomLkModel
    {
        return new AnchorRoomLkModel();
    }

    /**
     * @return RebroadCastModel
     */
    public function getRebroadCastModel(): RebroadCastModel
    {
        return new RebroadCastModel();
    }

    /**
     * @return ConfigInfoModel
     */
    public function getConfigInfoModel(): ConfigInfoModel
    {
        return new ConfigInfoModel();
    }

    /**
     * @return DocumentStatusModel
     */
    public function getDocumentStatusModel(): DocumentStatusModel
    {
        return new DocumentStatusModel();
    }

    /**
     * @return RoomDocumentsModel
     */
    public function getRoomDocumentsModel(): RoomDocumentsModel
    {
        return new RoomDocumentsModel();
    }

    /**
     * @return ExamAnswersModel
     */
    public function getExamAnswersModel(): ExamAnswersModel
    {
        return new ExamAnswersModel();
    }

    /**
     * @return ExamsModel
     */
    public function getExamsModel(): ExamsModel
    {
        return new ExamsModel();
    }

    /**
     * @return RoomExamLkModel
     */
    public function getRoomExamLkModel(): RoomExamLkModel
    {
        return new RoomExamLkModel();
    }

    /**
     * @return FilterWordsLogModel
     */
    public function getFilterWordsLogModel(): FilterWordsLogModel
    {
        return new FilterWordsLogModel();
    }

    /**
     * @return FilterWordsModel
     */
    public function getFilterWordsModel(): FilterWordsModel
    {
        return new FilterWordsModel();
    }

    /**
     * @return InviteCardModel
     */
    public function getInviteCardModel(): InviteCardModel
    {
        return new InviteCardModel();
    }

    /**
     * @return RoomInviteModel
     */
    public function getRoomInviteModel(): RoomInviteModel
    {
        return new RoomInviteModel();
    }

    /**
     * @return LotteryUserModel
     */
    public function getLotteryUserModel(): LotteryUserModel
    {
        return new LotteryUserModel();
    }

    /**
     * @return MenuesModel
     */
    public function getMenuesModel(): MenuesModel
    {
        return new MenuesModel();
    }

    /**
     * @return IncomeModel
     */
    public function getIncomeModel(): IncomeModel
    {
        return new IncomeModel();
    }

    /**
     * @return OrderDetailModel
     */
    public function getOrderDetailModel(): OrderDetailModel
    {
        return new OrderDetailModel();
    }

    /**
     * @return NoticeModel
     */
    public function getNoticeModel(): NoticeModel
    {
        return new NoticeModel();
    }

    /**
     * @return PendantModel
     */
    public function getPendantModel(): PendantModel
    {
        return new PendantModel();
    }

    /**
     * @return PendantOperateRecordModel
     */
    public function getPendantOperateRecordModel(): PendantOperateRecordModel
    {
        return new PendantOperateRecordModel();
    }

    /**
     * @return PendantStatsModel
     */
    public function getPendantStatsModel(): PendantStatsModel
    {
        return new PendantStatsModel();
    }

    /**
     * @return PhotoSignImgModel
     */
    public function getPhotoSignImgModel(): PhotoSignImgModel
    {
        return new PhotoSignImgModel();
    }

    /**
     * @return PhotoSignRecordModel
     */
    public function getPhotoSignRecordModel(): PhotoSignRecordModel
    {
        return new PhotoSignRecordModel();
    }

    /**
     * @return PhotoSignTaskModel
     */
    public function getPhotoSignTaskModel(): PhotoSignTaskModel
    {
        return new PhotoSignTaskModel();
    }

    /**
     * @return QuestionAnswerLogsModel
     */
    public function getQuestionAnswerLogsModel(): QuestionAnswerLogsModel
    {
        return new QuestionAnswerLogsModel();
    }

    /**
     * @return QuestionAnswersModel
     */
    public function getQuestionAnswersModel(): QuestionAnswersModel
    {
        return new QuestionAnswersModel();
    }

    /**
     * @return QuestionLogsModel
     */
    public function getQuestionLogsModel(): QuestionLogsModel
    {
        return new QuestionLogsModel();
    }

    /**
     * @return QuestionsModel
     */
    public function getQuestionsModel(): QuestionsModel
    {
        return new QuestionsModel();
    }

    /**
     * @return RoomQuestionLkModel
     */
    public function getRoomQuestionLkModel(): RoomQuestionLkModel
    {
        return new RoomQuestionLkModel();
    }

    /**
     * @return RecordAttendsModel
     */
    public function getRecordAttendsModel(): RecordAttendsModel
    {
        return new RecordAttendsModel();
    }

    /**
     * @return RoomAttendsAllModel
     */
    public function getRoomAttendsAllModel(): RoomAttendsAllModel
    {
        return new RoomAttendsAllModel();
    }

    /**
     * @return RecordModel
     */
    public function getRecordModel(): RecordModel
    {
        return new RecordModel();
    }

    /**
     * @return RecordStatsModel
     */
    public function getRecordStatsModel(): RecordStatsModel
    {
        return new RecordStatsModel();
    }

    /**
     * @return RoomLikeModel
     */
    public function getRoomLikeModel(): RoomLikeModel
    {
        return new RoomLikeModel();
    }

    /**
     * @return ScrollingModel
     */
    public function getScrollingModel(): ScrollingModel
    {
        return new ScrollingModel();
    }

    /**
     * @return TagModel
     */
    public function getTagModel(): TagModel
    {
        return new TagModel();
    }

    /**
     * @return RoomVoteLkModel
     */
    public function getRoomVoteLkModel(): RoomVoteLkModel
    {
        return new RoomVoteLkModel();
    }

    /**
     * @return VoteAnswersModel
     */
    public function getVoteAnswersModel(): VoteAnswersModel
    {
        return new VoteAnswersModel();
    }

    /**
     * @return VoteOptionCountModel
     */
    public function getVoteOptionCountModel(): VoteOptionCountModel
    {
        return new VoteOptionCountModel();
    }

    /**
     * @return VotesModel
     */
    public function getVotesModel(): VotesModel
    {
        return new VotesModel();
    }

    /**
     * @return ApplyModel
     */
    public function getApplyModel(): ApplyModel
    {
        return new ApplyModel();
    }

    /**
     * @return ApplyUsersModel
     */
    public function getApplyUsersModel(): ApplyUsersModel
    {
        return new ApplyUsersModel();
    }

    /**
     * @return WhiteAccountsModel
     */
    public function getWhiteAccountsModel(): WhiteAccountsModel
    {
        return new WhiteAccountsModel();
    }

    /**
     * @return OpsMonitorModel
     */
    public function getOpsMonitorModel(): OpsMonitorModel
    {
        return new OpsMonitorModel();
    }
}
