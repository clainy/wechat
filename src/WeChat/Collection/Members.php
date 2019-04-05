<?php
namespace Im050\WeChat\Collection;
use Im050\WeChat\Collection\Element\MemberElement;
use Im050\WeChat\Component\Utils;

/**
 * Class Members
 *
 * @package Im050\WeChat\Collection
 */
class Members
{

    const TYPE_GROUP = 'group';

    const TYPE_OFFICIAL = 'official';

    const TYPE_CONTACT = 'contact';

    const TYPE_SPECIAL = 'special';

    protected static $_instance = null;

    public $officials = null;

    public $contacts = null;

    public $groups = null;

    public $specials = null;

    public static $specialUsername = ['newsapp', 'fmessage', 'filehelper', 'weibo', 'qqmail',
        'fmessage', 'tmessage', 'qmessage', 'qqsync', 'floatbottle', 'lbsapp', 'shakeapp',
        'medianote', 'qqfriend', 'readerapp', 'blogapp', 'facebookapp', 'masssendapp',
        'meishiapp', 'feedsapp', 'voip', 'blogappweixin', 'weixin', 'brandsessionholder',
        'weixinreminder', 'wxid_novlwrv3lqwv11', 'gh_22b87fa7cb3c', 'officialaccounts',
        'notification_messages', 'wxid_novlwrv3lqwv11', 'gh_22b87fa7cb3c', 'wxitil',
        'userexperience_alarm', 'notification_messages'
    ];

    /**
     * Members constructor.
     */
    public function __construct()
    {
        //初始化特殊用户列表容器
        $this->specials = new ContactCollection();

        //初始化联系人列表容器
        $this->contacts = new ContactCollection();

        //初始化群组列表容器
        $this->groups = new ContactCollection();

        //初始化公众号列表容器
        $this->officials = new ContactCollection();
    }

    /**
     * 增加成员
     *
     * @param $item
     * @throws \Exception
     */
    public function push($item)
    {
        switch (Members::getUserType($item)) {
            case Members::TYPE_CONTACT:
                $list = $this->contacts;
                break;
            case Members::TYPE_GROUP:
                $list = $this->groups;
                break;
            case Members::TYPE_SPECIAL:
                $list = $this->specials;
                break;
            case Members::TYPE_OFFICIAL:
                $list = $this->officials;
                break;
            default:
                throw new \Exception("未能识别的用户类型");
        }

        $item['NickName'] = Utils::formatContent($item['NickName']);
        $item['RemarkName'] = Utils::formatContent($item['RemarkName']);
        $item['Signature'] = Utils::formatContent($item['Signature']);

        $list->put($item['UserName'], $item);
    }

    /**
     * 获取所有群组
     *
     * @return ContactCollection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * 获取所有特殊账号
     *
     * @return ContactCollection
     */
    public function getSpecials()
    {
        return $this->specials;
    }

    /**
     * 获取所有联系人
     *
     * @return ContactCollection
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * 获取所有公众号
     *
     * @return ContactCollection
     */
    public function getOfficials()
    {
        return $this->officials;
    }

    /**
     * 根据username得到联系人实例
     *
     * @param $username
     * @return MemberElement
     */
    public function getContactByUserName($username)
    {
        if (substr($username, 0, 2) == '@@') {
            return $this->getGroups()->getContactByUserName($username);
        } else {
            if (($user = $this->getContacts()->getContactByUserName($username)) !== null) {
                return $user;
            } else if (($user = $this->getOfficials()->getContactByUserName($username)) !== null) {
                return $user;
            } else if (($user = $this->getSpecials()->getContactByUserName($username)) !== null) {
                return $user;
            } else {
                return null;
            }
        }
    }

    /**
     * 得到用户类型
     *
     * @param $item
     * @return string
     */
    public static function getUserType($item)
    {

        if (!isset($item['VerifyFlag'])) {
            $item['VerifyFlag'] = 0;
        }

        if (self::isGroup($item['UserName'])) {
            return Members::TYPE_GROUP;
        } else if (self::isContact($item['UserName'], $item['VerifyFlag'])) {
            return Members::TYPE_CONTACT;
        } else if (self::isOfficial($item['UserName'], $item['VerifyFlag'])) {
            return Members::TYPE_OFFICIAL;
        } else {
            return Members::TYPE_SPECIAL;
        }
    }

    /**
     * 根据用户名判断是否是群组
     *
     * @param $username
     * @return boolean
     */
    public static function isGroup($username)
    {
        return substr($username, 0, 2) == '@@';
    }

    /**
     * 判断是否常规联系人
     *
     * @param $username
     * @param int $verifyFlag
     * @return boolean;
     */
    public static function isContact($username, $verifyFlag = 0)
    {
        return self::isContactOrOfficial($username, $verifyFlag, true);
    }

    /**
     * 判断是否公众号
     *
     * @param $username
     * @param int $verifyFlag
     * @return bool
     */
    public static function isOfficial($username, $verifyFlag = 0)
    {
        return self::isContactOrOfficial($username, $verifyFlag, false);
    }

    /**
     * 通用判断公众号或者常规联系人
     *
     * @param $username
     * @param $verifyFlag
     * @param bool $type
     * @return bool
     */
    public static function isContactOrOfficial($username, $verifyFlag, $type = true)
    {
        $pattern = '/^(@[a-z0-9]{1})/';
        preg_match($pattern, $username, $matches);
        if (empty($matches)) {
            return false;
        }

        if ($type == true) {
            $flag = (($verifyFlag & 8) == 0);
        } else {
            $flag = (($verifyFlag & 8) != 0);
        }

        return $flag;
    }

}