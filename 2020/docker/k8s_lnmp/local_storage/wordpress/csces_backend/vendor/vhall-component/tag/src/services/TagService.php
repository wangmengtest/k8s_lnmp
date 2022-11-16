<?php
/**
 * 标签
 *Created by PhpStorm.
 *DATA: 2019/11/7 14:20
 */

namespace vhallComponent\tag\services;

use App\Constants\ResponseCode;
use Vss\Common\Services\WebBaseService;
use vhallComponent\tag\constants\TagConstant;

class TagService extends WebBaseService
{
    /**
     * 保存
     *
     * @param $params
     *
     * @return array|mixed|\vhallComponent\tag\models\TagModel
     *
     */
    public function save($params)
    {
        if ($params['type'] == TagConstant::TAG_TYPE_COMMON_USE) {
            $count = vss_model()->getTagModel()->getCount([
                'type' => $params['type'],
            ]);
            if ($count == 30) {
                $this->fail(ResponseCode::COMP_TAG_MAX_VALUE, [
                    'val' => 30
                ]);
            }
        }
        $condition = ['name' => $params['name'], 'type' => $params['type']];
        $info      = vss_model()->getTagModel()->getRow($condition);

        if ($info && $info->tag_id > 0) {
            return $info->toArray();
        }

        return vss_model()->getTagModel()->create($params);
    }

    /**
     *
     * 列表
     *
     * @param $params
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list($params)
    {
        $params['name']      = $params['name'] ?? '';
        $params['page_size'] = $params['page_size'] ?? TagConstant::PAGE_SIZE;
        $params['curr_page'] = $params['curr_page'] ?? TagConstant::COMMON_LABEL;

        return vss_model()->getTagModel()
            ->setKeyName('status')
            ->setPerPage($params['page_size'])
            ->getList($params, [], $params['curr_page']);
    }

    /**
     * 修改
     *
     * @param $params
     *
     * @return int
     */
    public function update($params)
    {
        $data = array_filter($params);
        unset($data['tag_id']);

        return vss_model()->getTagModel()->updateRow($params['tag_id'], $data);
    }

    /**
     * 详情
     *
     * @param $ids
     *
     * @return array
     */
    public function getInfo($ids)
    {
        if (empty($ids)) {
            return [];
        }
        //房间tag ids变化很少,可以走缓存时间默认30分,
        $tagInfo = vss_redis()->get(TagConstant::TAG_INFO_BY_IDS . $ids);
        if (empty($tagInfo)) {
            $idArr   = explode(',', $ids);
            $tagInfo = vss_model()->getTagModel()->getInfo($idArr)->toArray();
            vss_redis()->set(TagConstant::TAG_INFO_BY_IDS . $ids, $tagInfo);
        }
        return $tagInfo;
    }

    /**
     *删除
     *
     * @param $params
     *
     * @return mixed
     */
    public function delete($params)
    {
        $ids = explode(',', $params['tag_ids']);

        return vss_model()->getTagModel()->deleteIds($ids);
    }

    /**
     * @param $params
     *
     * @return bool
     */
    public function rank($params)
    {
        //$info = vss_model()->getTagModel()->whereIn('tag_id',explode(',',$params['tag_ids']))->get()->toArray();
        // $ids=array_column($info,'tag_id');
        $ids  = explode(',', $params['tag_ids']);
        $rank = explode(',', $params['rank']);
        for ($i = 0; $i < count($ids); $i++) {
            $info = vss_model()->getTagModel()->where(['tag_id' => $ids[$i]])->first();
            if ($info) {
                vss_model()->getTagModel()->where(['tag_id' => $ids[$i]])->update(['status' => $rank[$i]]);
            }
        }

        return true;
    }

    /**
     * @param $ilId
     * @param $accountId
     * @param $tags
     *
     * @return bool
     */
    public function getRealTagids($ilId, $accountId, $tags)
    {
        $condition = ['il_id' => $ilId, 'account_id' => $accountId];
        $liveList  = vss_model()->getRoomsModel()->getRow($condition);
        if ($liveList) {
            if ($liveList['topics']) {
                $liveTags = explode(',', $liveList['topics']);
                $tags     = explode(',', $tags);
                foreach ($tags as $v) {
                    if (!in_array($v, $liveTags)) {
                        $new_arr[] = $v;
                    }
                }
                if (!$new_arr) {
                    return false;
                }

                $tagid_str = implode(',', array_merge($new_arr));
                self::count(['ids' => $tagid_str]);
            }
        }

        return true;
    }

    /**
     *
     * 总计
     *
     * @param $params
     *
     * @return int
     */
    public function count($params)
    {
        $info = vss_model()->getTagModel()->whereIn('tag_id', explode(',', $params['ids']))->get()->toArray();
        if ($info) {
            $ids = array_column($info, 'tag_id');

            return vss_model()->getTagModel()->whereIn('tag_id', $ids)->increment('use_count');
        }
    }

    /**
     * 获取标签对应信息
     *
     * @param $tagIds
     *
     * @return array
     */
    public function tagsInfo($tagIds)
    {
        $tagList = $this->getInfo($tagIds);
        $new_arr = [];
        foreach ($tagList as $key => $tag) {
            $new_arr[$key]['tag_id'] = $tag['tag_id'];
            $new_arr[$key]['name']   = $tag['name'];
        }
        return $new_arr;
    }
}
