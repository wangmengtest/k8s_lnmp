<?php

namespace App\Constants;

use Vss\Traits\ResponseCodeTrait;

/**
 * 组件响应码定义
 * 每个常量必须要写文档注释，可以根据注释生成语言包文件
 * 如果不写注释，将不能正常的生成语言包
 *
 * 可以通过如下命令检查错误码是否有重复:
 * php artisan generator:lang --c
 *
 * 可以通过如下命令生成中文语言包
 * php artisan generator:lang
 *
 * 可以通过如下命令生成英文语言包，前提是已经生成中文语言包了
 * php artisan generator:lang --t
 *
 * 可以通过如下命令更新常量通过 code.json 文件, code.json 有更新时，将更新内容同步到常量文件中
 * php artisan generator:lang --r
 * - code.json 中存在， 不存在对应常量的，将会新增的该文件中
 * - code.json 中存在， 存在对应常量的，如果值相同，则跳过，否则修改常量的注释
 * - code.json 中不存在，常量存在的，不会删除常量， 可以使用 php artisan generator:lang 将常量同步到 code.json 中
 *
 * Class ResponseCode
 * @package App\Constants
 */
class ResponseCode
{
    use ResponseCodeTrait;

    /** 请求成功 */
    const SUCCESS = 'success';

    /** 请求失败 */
    const FAILED = 'failed';

    /**
     * 查询为空 EMPTY_ 开头
     */

    /** 菜单信息不存在 */
    const EMPTY_MENU = 'empty.menu';

    /** 父级菜单不存在 */
    const EMPTY_MENU_PARENT = 'empty.menu.parent';

    /** 操作信息不存在 */
    const EMPTY_ACTION = 'empty.action';

    /** 角色信息不存在 */
    const EMPTY_ROLE = 'empty.role';

    /** 房间信息不存在 */
    const EMPTY_ROOM = 'empty.room';

    /** 点播信息不存在 */
    const EMPTY_VOD = 'empty.vod';

    /** 文档信息不存在 */
    const EMPTY_DOCUMENT = 'empty.document';

    /** 管理员信息不存在 */
    const EMPTY_ADMIN = 'empty.admin';

    /** 用户信息不存在 */
    const EMPTY_ACCOUNT = 'empty.account';

    /** 问卷信息不存在 */
    const EMPTY_QUESTION = 'empty.question';

    /** 回放信息不存在 */
    const EMPTY_RECORD = 'empty.record';

    /** 视频不存在 */
    const EMPTY_VIDEO = 'empty.video';

    /** 信息不存在 */
    const EMPTY_INFO = 'empty.info';

    /** 列表为空 */
    const EMPTY_LIST = 'empty.list';

    /** token 不存在 */
    const EMPTY_TOKEN = 'empty.token';

    /** 投票不存在 */
    const EMPTY_VOTE = 'empty.vote';

    /** 商品不存在！ */
    const EMPTY_GOODS = 'empty.goods';

    /** 配置信息不存在 */
    const EMPTY_CONFIG = 'empty.config';

    /** 报名表不存在 */
    const EMPTY_SIGN_TABLE = 'empty.sign.table';

    /** 用户不存在 */
    const EMPTY_USER = 'empty.user';

    /** 白名单用户不存在 */
    const EMPTY_WHITE_USER = 'empty.white.user';

    /** 文件不存在 */
    const EMPTY_FILE = 'empty.file';

    /** 服务不存在 */
    const EMPTY_SERVICE = 'empty.service';

    /** 签到任务不存在 */
    const EMPTY_SIGN_TASK = 'empty.sign.task';

    /** 暂无数据 */
    const EMPTY_DATA = 'empty.data';

    /**
     * 鉴权错误
     * AUTH_LOGIN_ 开头的都是要重定向到登录页面的
     */

    /** 签名验证失败 */
    const AUTH_LOGIN_SIGN = 'auth.login.sign';

    /** 签名已过期失效 */
    const AUTH_LOGIN_SIGN_EXPIRE = 'auth.login.sign.expire';

    /** token 验证失败 */
    const AUTH_LOGIN_TOKEN = 'auth.login.token';

    /** token 已过期失效 */
    const AUTH_LOGIN_TOKEN_EXPIRE = 'auth.login.token.expire';

    /** 无效的 token */
    const AUTH_LOGIN_TOKEN_INVALID = 'auth.login.token.invalid';

    /** 您已在其他地方登录 */
    const AUTH_LOGIN_ALREADY = 'auth.login.already';

    /** 账号被停用,请联系客服! */
    const AUTH_LOGIN_ACCOUNT_DISABLE = 'auth.login.account.disable';

    /** 账号被停用,请联系客服! */
    const AUTH_LOGIN_ACCOUNT_DISABLE_VERIFYTOKEN = 'auth.login.account.disable.verifytoken';

    /*该账号正在直播中，请稍后再试*/
    const AUTH_LOGIN_CHECK_STREAM_STATUS = 'auth.login.check.stream.status';

    /** 登录失败  */
    const AUTH_LOGIN_FAILED = 'auth.login.failed';

    /** 角色被禁用 */
    const AUTH_LOGIN_ROLE_DISABLE = 'auth.login.role.disable';

    /**
     * AUTH_ 开头的不需要重定向到登录页
     */

    /** 权限不足 */
    const AUTH_NOT_PERMISSION = 'auth.not.permission';

    /** vss_token 不能为空 */
    const AUTH_VSS_TOKEN_NOT_EMPTY = 'auth.vss.token.not.empty';

    /** 口令输入错误！ */
    const AUTH_ROLE_PASSWORD_ERROR = 'auth.role.password.error';

    /** 非直播中房间不支持口令方式进入 */
    const AUTH_NOT_SUPPORT_ROLE_PASSWORD = 'auth.not.support.role.password';

    /** 直播间角色错误 */
    const AUTH_ROLE_ERROR = 'auth.role.error';

    /** 手机号已被使用 */
    const AUTH_PHONE_ALREADY_EXIST = 'auth.phone.already.exist';

    /** 验证码错误 */
    const AUTH_VERIFICATION_CODE_ERROR = 'auth.verification.code.error';

    /** 用户名错误 */
    const AUTH_USERNAME_ERROR = 'auth.username.error';

    /** 密码错误 */
    const AUTH_PASSWORD_ERROR = 'auth.password.error';

    /** 两次输入密码不一致 */
    const AUTH_ENTERED_PASSWORDS_DIFFER = 'auth.entered.passwords.differ';

    /** 生成TOKEN失败 */
    const AUTH_TOKEN_CREATE_FAILED = 'auth.token.create.failed';

    /** 权限不存在 */
    const AUTH_ACCESS_EMPTY = 'auth.access.empty';

    /** 权限已存在 */
    const AUTH_ACCESS_EXIST = 'auth.access.exist';

    /** 父权限不存在 */
    const AUTH_ACCESS_PARENT_EMPTY = 'auth.access.parent.empty';

    /**
     * 类型/格式错误 TYPE_ 开头
     */

    /** 无效的上传类型 */
    const TYPE_INVALID_UPLOAD = 'type.invalid.upload';

    /** 无效的存储类型 :type */
    const TYPE_INVALID_STORAGE = 'type.invalid.storage';

    /** 无效的文件类型 */
    const TYPE_INVALID_FILE = 'type.invalid.file';

    /** 无效的图片类型 */
    const TYPE_INVALID_IMAGE = 'type.invalid.image';

    /** 手机号格式错误 */
    const TYPE_PHONE = 'type.phone';

    /** 试卷类型错误 */
    const TYPE_EXAM_ERROR = 'type.exam.error';

    /** 无效的价格 */
    const TYPE_INVALID_PRICE = 'type.invalid.price';

    /** 无效的金额 */
    const TYPE_INVALID_MONEY = 'type.invalid.money';

    /** 无效的裁剪格式 */
    const TYPE_INVALID_CUT = 'type.invalid.cut';

    /** 无效的字符串 */
    const TYPE_INVALID_STRING = 'type.invalid.string';

    /** 字符串长度不在范围内 [:minLen - :maxLen] */
    const TYPE_INVALID_STRING_LEN_RANGE = 'type.invalid.string.len.range';

    /** 无效的响应内容，缺少字段 :field */
    const TYPE_INVALID_RSP = 'type.invalid.rsp';

    /**
     * 业务类错误  BUSINESS_ 开头
     */

    /** 无效的用户 */
    const BUSINESS_INVALID_USER = 'business.invalid.user';

    /** 无效参数 */
    const BUSINESS_INVALID_PARAM = 'business.invalid.param';

    /** 无效的文件 */
    const BUSINESS_INVALID_FILE = 'business.invalid.file';

    /** 请上传图片 */
    const BUSINESS_UPLOAD_IMAGE = 'business.upload.image';

    /** 创建失败 */
    const BUSINESS_CREATE_FAILED = 'business.create.failed';

    /** 删除失败 */
    const BUSINESS_DELETE_FAILED = 'business.delete.failed';

    /** 编辑失败 */
    const BUSINESS_EDIT_FAILED = 'business.edit.failed';

    /** 提交失败 */
    const BUSINESS_SUBMIT_FAILED = 'business.submit.failed';

    /** 重复提交错误 */
    const BUSINESS_SUBMIT_REPEATED = 'business.submit.repeated';

    /** 复制失败 */
    const BUSINESS_COPY_FAILED = 'business.copy.failed';

    /** 上传失败 */
    const BUSINESS_UPLOAD_FAILED = 'business.upload.failed';

    /** 保存失败*/
    const BUSINESS_SAVE_FAIL = 'business.save.fail';

    /** 修改失败*/
    const BUSINESS_UPDATE_FAIL = 'business.update.fail';

    /** 超过上传数量限制，请重试 */
    const BUSINESS_UPLOAD_OVERFLOW = 'business.upload.overflow';

    /** 下载失败 */
    const BUSINESS_DOWNLOAD_FAILED = 'business.download.failed';

    /** 下载中 */
    const BUSINESS_DOWNLOADING = 'business.downloading';

    /** 审核未通过 */
    const BUSINESS_AUDIT_REJECT = 'business.audit.reject';

    /** 审核通过 */
    const BUSINESS_AUDIT_PASS = 'business.audit.pass';

    /** 审核中 */
    const BUSINESS_AUDIT_ING = 'business.audit.ing';

    /** 审核失败，重新审核 */
    const BUSINESS_AUDIT_FAILED = 'business.audit.failed';

    /** 检查失败 */
    const BUSINESS_CHECK_FAILED = 'business.check.failed';

    /** 推送失败 */
    const BUSINESS_PUSH_FAILED = 'business.push.failed';

    /** 操作失败！ */
    const BUSINESS_OPERATION_FAILED = 'business.operation.failed';

    /** 请勿频繁操作, :waitTime 分钟之后再试！ */
    const BUSINESS_DONT_FREQUENT_OPERATION = 'business.dont.frequent.operation';

    /** 结果统计中,请稍后! */
    const BUSINESS_RESULT_STATING = 'business.result.stating';

    /** 关联失败 */
    const BUSINESS_BIND_FAILED = 'business.bind.failed';

    /** 取消关联失败！ */
    const BUSINESS_UNBIND_FAILED = 'business.unbind.failed';

    /** 导入失败 */
    const BUSINESS_IMPORT_FAILED = 'business.import.failed';

    /** 单次导入数量不能大于 :count 条 */
    const BUSINESS_IMPORT_COUNT_OVERFLOW = 'business.import.count.overflow';

    /** 导入失败，白名单人数已经超过最大限制！ */
    const BUSINESS_WHITE_COUNT_OVERFLOW = 'business.white.count.overflow';

    /** 上报没有报名 */
    const BUSINESS_NOT_SIGN_UP = 'business.not.sign.up';

    /** 白名单没有报名 */
    const BUSINESS_NOT_SIGN_UP_WITH_WHITE = 'business.not.sign.up.with.white';

    /** 白名单密码不正确 */
    const BUSINESS_INVALID_WHITE_PASSWORD = 'business.invalid.white.password';

    /** 无效的清晰度 */
    const BUSINESS_INVALID_QUALITY = 'business.invalid.quality';

    /** 无效的时间 */
    const BUSINESS_INVALID_TIME = 'business.invalid.time';

    /** 活动火爆，请稍后再试...... */
    const BUSINESS_HOT = 'business.hot';

    /** 超出系统并发上限 */
    const BUSINESS_CONCURRENT_OVERFLOW = 'business.concurrent.overflow';

    /** 并发判断参数异常 */
    const BUSINESS_CONCURRENT_JUDGE_FAILED = 'business.concurrent.judge.failed';

    /** 开播时间有误 */
    const BUSINESS_OPEN_PLAY_TIME_ERROR = 'business.open.play.time.error';

    /** 获取直播房间流状态列表信息失败 */
    const BUSINESS_GET_LIVE_STREAM_FAILED = 'business.get.live.stream.failed';

    /** 当前直播房间流状态信息格式错误 */
    const BUSINESS_LIVE_STREAM_FORMAT_ERROR = 'business.live.stream.format.error';

    /** 拉流异常 */
    const BUSINESS_PULL_STREAM_FAILED = 'business.pull.stream.failed';

    /** 开始直播失败 */
    const BUSINESS_START_LIVE_FAILED = 'business.start.live.failed';

    /** 结束直播失败 */
    const BUSINESS_END_LIVE_FAILED = 'business.end.live.failed';

    /** 房间正在直播中，无法删除! */
    const BUSINESS_LIVING_NOT_DELETE = 'business.living.not.delete';

    /** 推流失败 */
    const BUSINESS_PUSH_STREAM_FAILED = 'business.push.stream.failed';

    /** 推流中不能删除 */
    const BUSINESS_PUSH_STREAMING_NOT_DELETE = 'business.push.streaming.not.delete';

    /** 推流停止失败 */
    const BUSINESS_PUSH_STREAM_STOP_FAILED = 'business.push.stream.stop.failed';

    /** 踢出/取消踢出失败 */
    const BUSINESS_SWITCH_KICK_FAILED = 'business.switch.kick.failed';

    /** 不能重复转播 */
    const BUSINESS_NOT_REPEAT = 'business.not.repeat';

    /** 转播不能与转播源相同 */
    const BUSINESS_SOURCE_ERROR = 'business.source.error';

    /** 设置失败 */
    const BUSINESS_SET_FAILED = 'business.set.failed';

    /** 房间暂未开播 */
    const BUSINESS_SOURCE_NOT_START = 'business.source.not.start';

    /** 房间直播已结束 */
    const BUSINESS_SOURCE_END = 'business.source.end';

    /** 上麦席位已满员 */
    const BUSINESS_SPEAKER_FULL = 'business.speaker.full';

    /** 上麦席位或邀请已满员 */
    const BUSINESS_SPEAKER_OR_INVITATION_FULL = 'business.speaker.or.invitation.full';

    /** 未上麦 */
    const BUSINESS_NOT_SPEAKER = 'business.not.speaker';

    /** 角色不符 */
    const BUSINESS_ROLE_NOT_MATCH = 'business.role.not.match';

    /** 角色不存在 */
    const BUSINESS_ROLE_NOT_EXIST = 'business.role.not.exist';

    /** 未申请 */
    const BUSINESS_NOT_APPLY = 'business.not.apply';

    /** 暂未开放举手 */
    const BUSINESS_NOT_SUPPORT_RAISE = 'business.not.support.raise';

    /** 活动尚未开始 */
    const BUSINESS_NOT_START = 'business.not.start';

    /** 您已被踢出 */
    const BUSINESS_KICKED = 'business.kicked';

    /** 禁言/取消禁言失败 */
    const BUSINESS_SWITCH_MUTE_FAILED = 'business.switch.mute.failed';

    /** 管理员被禁用 */
    const BUSINESS_ADMIN_DISABLE = 'business.admin.disable';

    /** 登录名或密码错误 */
    const BUSINESS_LOGIN_FAILED = 'business.login.failed';

    /** 登录名已存在 */
    const BUSINESS_USER_NAME_EXIST = 'business.user.name.exist';

    /** 昵称已存在 */
    const BUSINESS_NICKNAME_EXIST = 'business.nickname.exist';

    /** 手机号已存在 */
    const BUSINESS_PHONE_EXIST = 'business.phone.exist';

    /** 邮箱已存在 */
    const BUSINESS_EMAIL_EXIST = 'business.email.exist';

    /** 操作已存在 */
    const BUSINESS_ACTION_EXIST = 'business.action.exist';

    /** 角色已存在 */
    const BUSINESS_ROLE_EXIST = 'business.role.exist';

    /** 保存角色信息失败 */
    const BUSINESS_ROLE_ADD_FAILED = 'business.role.add.failed';

    /** 菜单已存在 */
    const BUSINESS_MENU_EXIST = 'business.menu.exist';

    /** 菜单名称已存在 */
    const BUSINESS_MENU_NAME_EXIST = 'business.menu.name.exist';

    /** 菜单URL已存在 */
    const BUSINESS_MENU_URL_EXIST = 'business.menu.url.exist';

    /** 关键词已存在 */
    const BUSINESS_KEYWORD_EXIST = 'business.keyword.exist';

    /** 重复提交错误 */
    const BUSINESS_REPEAT_SUBMIT = 'business.repeat.submit';

    /** 重复上麦 */
    const BUSINESS_REPEAT_SPEAKER = 'business.repeat.speaker';

    /** 重复申请 */
    const BUSINESS_REPEAT_APPLY = 'business.repeat.apply';

    /** 重复创建 */
    const BUSINESS_REPEAT_CREATE = 'business.repeat.create';

    /** 重复绑定 */
    const BUSINESS_REPEAT_BIND = 'business.repeat.bind';

    /** 重复邀请 */
    const BUSINESS_REPEAT_INVITATION = 'business.repeat.invitation';

    /** 重复邀请，30s后可再次邀请 */
    const BUSINESS_REPEAT_INVITATION_THIRTY_LATER = 'business.repeat.invitation.thirty.later';

    /** 与当前序号重复！ */
    const BUSINESS_REPEAT_SERIAL_NUMBER = 'business.repeat.serial.number';

    /** 敏感词重复 */
    const BUSINESS_REPEAT_FILTER = 'business.repeat.filter';

    /** PAAS服务调用异常 */
    const BUSINESS_PAAS_SERVICE_FAILED = 'business.paas.service.failed';

    /** APPID不匹配 */
    const BUSINESS_PAAS_NO_MATCH = 'business.paas.no.match';

    /** 共享服务调用异常 */
    const BUSINESS_PUBLIC_FORWARD_SERVICE_FAILED = 'business.public.forward.service.failed';

    /** 主持人暂时离开，请稍等 */
    const BUSINESS_MASTER_OFFLINE = 'business.master.offline';

    /** 主持人已上线 */
    const BUSINESS_MASTER_ONLINE = 'business.master.online';

    /** 生成失败 */
    const BUSINESS_GENERATE_FAILED = 'business.generate.failed';

    /**
     * 各组件错误码，能使用公用错误码，尽量使用公用错误码，
     * 当公用错误码不满足， 再使用自定义组件错误码
     * 以 COMP_组件名开头
     */

    /**
     * 问卷错误码
     */

    /** 已发布的问卷不能编辑 */
    const COMP_QUESTION_NOT_EDIT = 'comp.question.not.edit';

    /** 已发布的问卷不能删除 */
    const COMP_QUESTION_NOT_DELETE = 'comp.question.not.delete';

    /** 无此操作权限 */
    const COMP_QUESTION_NOT_PERMISSION = 'comp.question.not.permission';

    /** 该问卷已和直播间解绑！ */
    const COMP_QUESTION_ROOM_ALREADY_UNBIND = 'comp.question.room.already.unbind';

    /** 根据token解析出来的用户信息中没有accountid！ */
    const COMP_QUESTION_ACCOUNT_INFO_NO_ID = 'comp.question.account.info.no.id';

    /** 该问卷和直播间绑定失败！ */
    const COMP_QUESTION_BIND_ROOM_FAILED = 'comp.question.bind.room.failed';

    /** 未发布的问卷不允许回答 */
    const COMP_QUESTION_CAN_NOT_ANSWER = 'comp.question.can.not.answer';

    /** 未发布的问卷不允许推屏 */
    const COMP_QUESTION_CAN_NOT_PUSH = 'comp.question.can.not.push';

    /** 更新问卷信息失败 */
    const COMP_QUESTION_EDIT_FAILED = 'comp.question.edit.failed';

    /**
     * 考试错误码
     */

    /** 无效的试卷 */
    const COMP_EXAM_INVALID = 'comp.exam.invalid';

    /** 正在考试中 */
    const COMP_EXAM_TAKING = 'comp.exam.taking';

    /** 试卷收取中,请稍后! */
    const COMP_EXAM_COLLECTING = 'comp.exam.collecting';

    /** 不符合考试判卷条件 */
    const COMP_EXAM_NOT_REVIEW = 'comp.exam.not.review';

    /** 不符合公布考试结果条件 */
    const COMP_EXAM_NOT_PUBLISH_RESULT = 'comp.exam.not.publish.result';

    /** 试卷尚未发布 */
    const COMP_EXAM_NOT_PUBLISH = 'comp.exam.not.publish';

    /** 考试已发布，无法进行此操作 */
    const COMP_EXAM_NOT_EDIT = 'comp.exam.not.edit';

    /** 考试信息不存在 */
    const COMP_EXAM_NOT_EXIST = 'comp.exam.not.exist';

    /** 考试已批阅完毕，无法进行此操作 */
    const COMP_EXAM_FINISH_ALREADY = 'comp.exam.finish.already';

    /**
     * 投票独有的错误码
     */

    /** 无效的投票 */
    const COMP_VOTE_INVALID = 'comp.vote.invalid';

    /** 投票尚未发布 */
    const COMP_VOTE_NOT_PUBLISH = 'comp.vote.not.publish';

    /** 投票选项失效 */
    const COMP_VOTE_OPTION_INVALID = 'comp.vote.option.invalid';

    /** 提交内容与投票内容不符 */
    const COMP_VOTE_INVALID_CONTENT = 'comp.vote.invalid.content';

    /** 选项超出规范数量 */
    const COMP_VOTE_OPTION_COUNT_OVERFLOW = 'comp.vote.option.count.overflow';

    /** 投票重复绑定错误 */
    const COMP_VOTE_REPEAT_BIND = 'comp.vote.repeat.bind';

    /** 已发布投票无法修改 */
    const COMP_VOTE_PUBLISHED_NOT_EDIT = 'comp.vote.published.not.edit';

    /** 投票已结束 */
    const COMP_VOTE_FINISHED = 'comp.vote.finished';

    /** 不符合公布投票结果条件 */
    const COMP_VOTE_NOT_PUBLISH_RESULT = 'comp.vote.not.publish.result';

    /** 投票不存在 */
    const COMP_VOTE_NOT_EXIST = 'comp.vote.not.exist';

    /** 投票结果已公布 */
    const COMP_VOTE_RESULT_PUBLISHED = 'comp.vote.result.published';

    /** 结果统计中 */
    const COMP_VOTE_RESULT_STATING = 'comp.vote.result.stating';

    /** 投票进行中，不能删除 */
    const COMP_VOTE_TAKING_NOT_DEL = 'comp.vote.taking.not.del';

    /** 投票进行中，发布不成功 */
    const COMP_VOTE_RUNNING_NOT_PUBLISH = 'comp.vote.running.not.publish';

    /** 重复提交错误 */
    const COMP_VOTE_SUBMIT_REPEATED = 'comp.vote.submit.repeated';

    /**
     * 打赏
     */

    /** 打赏金额必须大于零数字 */
    const COMP_REWARD_INVALID_MONEY = 'comp.reward.invalid.money';

    /** 打赏失败 */
    const COMP_REWARD_FAILED = 'comp.reward.failed';

    /** 打赏信息不存在 */
    const COMP_REWARD_NOT_EXIST = 'comp.reward.not.exist';

    /**
     * 邀请卡
     */

    /** 未开启邀请卡 */
    const COMP_INVITE_CARD_DISABLE = 'comp.invite.card.disable';

    /**
     * 签到
     */

    /** 发起签到失败 */
    const COMP_SIGN_INITIATE_FAILED = 'comp.sign.initiate.failed';

    /** 用户签到失败 */
    const COMP_SIGN_USER_FAILED = 'comp.sign.user.failed';

    /** 签到已结束 */
    const COMP_SIGN_FINISH = 'comp.sign.finish';

    /** 签到信息不存在 */
    const COMP_SIGN_NOT_EXIST = 'comp.sign.not.exist';

    /** 重复签到 */
    const COMP_SIGN_REPEATED = 'comp.sign.repeated';

    /** 签到已经超时 */
    const COMP_SIGN_TIMEOUT = 'comp.sign.timeout';

    /**
     * 照片签到
     */

    /** 签到照片已达到上限 */
    const COMP_PHOTO_SIGN_IMG_OVERFLOW = 'comp.photo.sign.img.overflow';

    /**
     * 商品
     */

    /** 商品已被直播间 :il_id 关联，无法删除，请取消关联后重试！ */
    const COMP_GOODS_BIND_ROOM_NOT_DELETE = 'comp.goods.bind.room.not.delete';

    /** 序号大于商品总数，请重新输入！ */
    const COMP_GOODS_INVALID_SERIAL_NUMBER = 'comp.goods.invalid.serial.number';

    /** 推送商品失败 */
    const COMP_GOODS_PUSH_FAILED = 'comp.goods.push.failed';

    /**
     * 抽奖
     */

    /** 抽奖信息不存在 */
    const COMP_LOTTERY_EMPTY = 'comp.lottery.empty';

    /** 无效的抽奖类型 */
    const COMP_LOTTERY_TYPE_INVALID = 'comp.lottery.type.invalid';

    /** 抽奖范围参数错误 */
    const COMP_LOTTERY_RANGE_ERROR = 'comp.lottery.range.error';

    /** 没有匹配到符合要求的中奖用户 */
    const COMP_LOTTERY_NOT_MATCH_USER = 'comp.lottery.not.match.user';

    /** 有预置中奖用户不能大于中奖人数 */
    const COMP_LOTTERY_LUCK_USER_OVERFLOW = 'comp.lottery.luck.user.overflow';

    /** 未添加抽奖人员信息，请添加后上传 */
    const COMP_LOTTERY_NOT_ADD_USER = 'comp.lottery.not.add.user';

    /** 中奖人员信息错 */
    const COMP_LOTTERY_USERS_CHECK_ERROR = 'comp.lottery.users.check.error';

    /** 抽奖已经结束！*/
    const COMP_LOTTERY_HAS_ENDED = 'comp.lottery.has.ended';

    /** 有请勿重复提交 */
    const COMP_LOTTERY_COMMIT_ERROR = 'comp.lottery.commit.error';

    /** 结束抽奖失败 */
    const COMP_LOTTERY_END_FAILURE = 'comp.lottery.end.failure';

    /** 结束抽奖参数异常 */
    const COMP_LOTTERY_END_PARAM_ERROR = 'comp.lottery.end.param.error';

    /** 发起抽奖extends参数异常 */
    const COMP_LOTTERY_ERROR_EXTENDS = 'comp.lottery.error.extends';

    /** 上传文件不能为空 */
    const COMP_LOTTERY_IMPORT_NO_FILE_ERROR = 'comp.lottery.import.no.file.error';

    /** 中奖用户超出中奖人数 */
    const COMP_LOTTERY_NUMBER_ERROR = 'comp.lottery.number.error';

    /** lottery_number超过所选范围的数值 */
    const COMP_LOTTERY_NUMBERS_OVER_RANGE = 'comp.lottery.numbers.over.range';

    /** 超过上传数量限制3000，请重试 */
    const COMP_LOTTERY_UPLOAD_LIMIT_FAILURE = 'comp.lottery.upload.limit.failure';

    /**
     * 红包
     */

    /** 红包已过期 */
    const COMP_RED_PACKET_EXPIRE = 'comp.red.packet.expire';

    /**
     * 短信
     */

    /** 一分钟内只能发送一次验证码,请勿重复发送! */
    const COMP_SMS_THRESHOLD_WARING = 'comp.sms.threshold.waring';

    /**
     * 标签
     */

    /** 最大不能超过 :val 个 */
    const COMP_TAG_MAX_VALUE = 'comp.tag.max.value';

    /** 不允许的直播模式! */
    const COMP_TAG_MODE_NOT_ALLOWED = 'comp.tag.mode.not.allowed';

    /** 不允许的状态! */
    const COMP_TAG_STATUS_NOT_ALLOWED = 'comp.tag.status.not.allowed';

    /**
     * 挂件
     */

    /** 设置固定悬浮失败！*/
    const COMP_PENDANT_SET_FIXED_FAILED = 'comp.pendant.set.fixed.failed';

    /** 挂件推屏失败！*/
    const COMP_PENDANT_PUSH_FAILED = 'comp.pendant.push.failed';

    /**
     * 主播管理
     */

    /** 主播存在房间关联关系 */
    const COMP_ANCHOR_EXIST_ROOM_LK = 'comp.anchor.exist.room.lk';

    /** 用户名或者验证码错误！ */
    const COMP_ANCHOR_PHONE_NOT_EXIST = 'comp.anchor.phone.not.exist';

    /** 正在直播中，请稍后登录 */
    const COMP_ANCHOR_IS_LIVING = 'comp.anchor.is.living';

    /** 网络异常，3分钟后再试 */
    const COMP_ANCHOR_SMS_RISK = 'comp.anchor.sms.risk';

    /** 无效文档 */
    const COMP_DOC_EMPTY = 'comp.doc.empty';

    /** 无此操作权限 */
    const BUSINESS_NO_PERMISSION = 'business.no.permission';

    /** 不允许的类型! */
    const BUSINESS_TYPE_NOT_ALLOWED = 'business.type.not.allowed';

    /** 无此操作权限 */
    const COMP_EXAM_NO_ACCESS = 'comp.exam.no.access';

    /** 考试时间必须设置 */
    const COMP_EXAM_TIME_NOT_SET = 'comp.exam.time.not.set';

    /** 单次导入数量不能大于1000 */
    const COMP_FILTER_IMPORT_OVERFLOW = 'comp.filter.import.overflow';

    /** 敏感词重复 */
    const COMP_FILTER_KEY_REPEAT = 'comp.filter.key.repeat';

    /** 未添加敏感词信息，请添加后上传 */
    const COMP_FILTER_UPLOAD_NULL = 'comp.filter.upload.null';

    /** 创建礼物失败！ */
    const COMP_GIFT_ADD_FAILURE = 'comp.gift.add.failure';

    /** 删除礼物失败！ */
    const COMP_GIFT_DELETE_FAILURE = 'comp.gift.delete.failure';

    /** 编辑礼物失败！ */
    const COMP_GIFT_EDIT_FAILURE = 'comp.gift.edit.failure';

    /** 保存礼物关联关系失败！ */
    const COMP_GIFT_MAPPING_SAVE_FAILURE = 'comp.gift.mapping.save.failure';

    /** 礼物信息不存在！ */
    const COMP_GIFT_NOT_FOUND = 'comp.gift.not.found';

    /** 用户还未送礼物！ */
    const COMP_GIFT_NOT_SEND = 'comp.gift.not.send';

    /** 更改礼物支付状态失败！ */
    const COMP_GIFT_PAY_STATUS_SET_FAILURE = 'comp.gift.pay.status.set.failure';

    /** 礼物价格只能为大于零的数字！ */
    const COMP_GIFT_PRICE = 'comp.gift.price';

    /** 赠送礼物失败！ */
    const COMP_GIFT_SEND_FAILURE = 'comp.gift.send.failure';

    /** 中奖用户信息不存在 */
    const COMP_LOTTERY_USER_NOT_FOUND = 'comp.lottery.user.not.found';

    /** 订单金额不允许修改 */
    const COMP_PAY_AMOUNT_UNEDITABLE = 'comp.pay.amount.uneditable';

    /** 第三方下单失败，请联系开发人员 */
    const COMP_PAY_ORDER_FAILED = 'comp.pay.order.failed';

    /** 订单已经支付，无法再次申请支付 */
    const COMP_PAY_ORDER_PAYED = 'comp.pay.order.payed';

    /** 未配置对应支付方式参数，请联系开发人员配置支付参数 */
    const COMP_PAY_PARAM_UNSET = 'comp.pay.param.unset';

    /** 微信下单失败 */
    const COMP_PAY_WX_ORDER_FAILED = 'comp.pay.wx.order.failed';

    /** 获取微信支付参数时返回的参数错误 */
    const COMP_PAY_WX_PARAM_FAILED = 'comp.pay.wx.param.failed';

    /** 当service_code为JSAPI时open_id不能为空 */
    const COMP_PAY_WX_OPENID_EMPTY = 'comp.pay.wx.openid.empty';

    /** 回答创建失败！ */
    const COMP_QA_ANSWER_CREATE_FAILED = 'comp.qa.answer.create.failed';

    /** 回复不存在！ */
    const COMP_QA_ANSWER_NOT_FOUND = 'comp.qa.answer.not.found';

    /** 提问创建失败！ */
    const COMP_QA_CREATE_FAILED = 'comp.qa.create.failed';

    /** 提问ID不能为空！ */
    const COMP_QA_ID_IS_NULL = 'comp.qa.id.is.null';

    /** 问答信息不存在！ */
    const COMP_QA_NOT_FOUND = 'comp.qa.not.found';

    /** 不是当前用户的问卷不允许删除！ */
    const COMP_QUESTION_NO_DELETE_ABILITY = 'comp.question.no.delete.ability';

    /** 更新问卷为发布状态失败 */
    const COMP_QUESTION_PUBLISH_FAILED = 'comp.question.publish.failed';

    /** 推送失败 */
    const COMP_QUESTION_PUSH_FAILED = 'comp.question.push.failed';

    /** 房间直播已结束 */
    const COMP_REBROADCAST_LIVE_END = 'comp.rebroadcast.live.end';

    /** 房间暂未开播 */
    const COMP_REBROADCAST_LIVE_NOT_START = 'comp.rebroadcast.live.not.start';

    /** 转播不能与转播源相同 */
    const COMP_REBROADCAST_ORIGIN_SAME = 'comp.rebroadcast.origin.same';

    /** 不能重复转播 */
    const COMP_REBROADCAST_REPEATED = 'comp.rebroadcast.repeated';

    /** 设置转播记录失败 */
    const COMP_REBROADCAST_SET_FAILED = 'comp.rebroadcast.set.failed';

    /** 保存回放失败 */
    const COMP_RECORD_INSERT_FAILED = 'comp.record.insert.failed';

    /** 暂无回放数据 */
    const COMP_RECORD_NOT_EXIST = 'comp.record.not.exist';

    /** 更新房间默认回放失败 */
    const COMP_RECORD_UPDATE_FAILED = 'comp.record.update.failed';

    /** 支付渠道不正确 */
    const COMP_REDPACKET_CHANNEL_ERROR = 'comp.redpacket.channel.error';

    /** 不符合红包领取条件 */
    const COMP_REDPACKET_CONDITION_ERROR = 'comp.redpacket.condition.error';

    /** 创建红包失败 */
    const COMP_REDPACKET_CREATE_ERROR = 'comp.redpacket.create.error';

    /** 红包已删除或者不存在 */
    const COMP_REDPACKET_DELETE_ERROR = 'comp.redpacket.delete.error';

    /** 红包数量必须为大于零的整数 */
    const COMP_REDPACKET_NUMS_ERROR = 'comp.redpacket.nums.error';

    /** 红包还未开始不能抢 */
    const COMP_REDPACKET_START_ERROR = 'comp.redpacket.start.error';

    /** 红包解锁失败 */
    const COMP_REDPACKET_UNLOCK_ERROR = 'comp.redpacket.unlock.error';

    /** 红包已经被抢光了 */
    const COMP_REDPACKET_ZERO_ERROR = 'comp.redpacket.zero.error';

    /** 导入失败，白名单人数单次导入数量已经超过最大限制5000！ */
    const COMP_WATCHLIMIT_COUNT_OVERFLOW_SINGLE = 'comp.watchlimit.count.overflow.single';

    /** 导入失败，白名单人数累计导入数量已经超过最大限制50000！ */
    const COMP_WATCHLIMIT_COUNT_OVERFLOW_TOTAL = 'comp.watchlimit.count.overflow.total';

    /** 信息不存在 */
    const COMP_WATCHLIMIT_ROW_EMPTY = 'comp.watchlimit.row.empty';

    /** 导入失败，请检查数据格式 */
    const COMP_WATCHLIMIT_ROW_INVALID = 'comp.watchlimit.row.invalid';

    /** 网络有波动，请稍后再试 */
    const COMP_ROOM_ADDSPEAKER_TIMEOUT = 'comp.room.addspeaker.timeout';

    /** 观看者以达到上限请稍后再试" */
    const  CHECK_MAXONLINECOUNT_FAIL = 'check_maxonlinecount_fail';

    /** 暂无观看权限 */
    const  CHECK_IS_CAN_WATCH_LIVE = 'check_is_can_wathclive_fail';

    /** 开播时间已过,无法进行恢复预告设置 */
    const CHECK_IS_CAN_STATUS_TO_WAITING = 'check_is_can_status_to_waiting';

    /*观众最多可以添加200人，当前已超过观众人数上限*/
    const  INVITED_AUDIENCE_MAX = 'invited.audience.max';

    /*助理最多可以添加5人，当前已超过助理人数上限*/
    const  INVITED_ASSISTANT_MAX = 'invited.assistant.max';

    /*嘉宾最多可以添加5人，当前已超过嘉宾人数上限*/
    const  INVITED_GUEST_MAX = 'invited.guest.max';

    /** 请取消默认回放再操作 */
    const COMP_RECORD_DELETE_FAILED = 'comp.record.delete.failed';

    /** 数据库插入失败！ */
    const MYSQL_INSERT_FAILED = 'mysql.insert.failed';

    /** 数据库未查询到数据！ */
    const MYSQL_SELECT_FAILED = 'mysql.select.failed';

    /** 数据库操作异常！ */
    const MYSQL_DML_FAILED = 'mysql.dml.failed';

    /** redis操作异常！ */
    const REDIS_DML_FAILED = 'redis.dml.failed';

    /** redis写入失败！ */
    const REDIS_INSERT_FAILED = 'redis.insert.failed';

    /** 签名验证失败！ */
    const HEALTH_SIGN_FAILED = 'health.sign.failed';
}
