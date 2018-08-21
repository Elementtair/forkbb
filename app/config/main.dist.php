<?php

# development
#\error_reporting(\E_ALL);
#\ini_set('display_errors', 1);
#\ini_set('log_errors', 1);

return [
    'BASE_URL'    => '_BASE_URL_',
    'EOL'         => PHP_EOL, // Define line breaks in mail headers; possible values can be PHP_EOL, "\r\n", "\n" or "\r"
    'DB_DSN'      => '_DB_DSN_',
    'DB_USERNAME' => '_DB_USERNAME_',
    'DB_PASSWORD' => '_DB_PASSWORD_',
    'DB_OPTIONS'  => [],
    'DB_PREFIX'   => '_DB_PREFIX_',
    'COOKIE' => [
        'prefix' => '_COOKIE_PREFIX_',
        'domain' => '',
        'path'   => '/',
        'secure' => false,
        'time'   => 31536000,
        'key1'   => '_COOKIE_KEY1_',
        'key2'   => '_COOKIE_KEY2_',
    ],
    'HMAC' => [
        'algo' => 'sha1',
        'salt' => '_SALT_FOR_HMAC_',
    ],
    'JQUERY_LINK'      => '//ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js',
    'DEBUG'            => 2,
    'MAINTENANCE_OFF'  => false,
    'GROUP_ADMIN'      => 1,
    'GROUP_MOD'        => 2,
    'GROUP_GUEST'      => 3,
    'GROUP_MEMBER'     => 4,
    'BBCODE_INFO' => [
        'forSign'  => ['b', 'i', 'u', 'color', 'colour', 'email', 'img', 'url'],
        'smTpl'    => '<img src="{url}" alt="{alt}">',
        'smTplTag' => 'img',
        'smTplBl'  => ['url'],
    ],
    'MAX_POST_SIZE' => 65536,
    'MAX_IMG_SIZE'  => '2M',
    'MAX_FILE_SIZE' => '2M',

    'shared' => [
        'DB' => [
            'class' => \ForkBB\Core\DB::class,
            'dsn'      => '%DB_DSN%',
            'username' => '%DB_USERNAME%',
            'password' => '%DB_PASSWORD%',
            'options'  => '%DB_OPTIONS%',
            'prefix'   => '%DB_PREFIX%',
        ],
        'Secury' => [
            'class' => \ForkBB\Core\Secury::class,
            'hmac'  => '%HMAC%',
        ],
        'FileCache' => [
            'class'     => \ForkBB\Core\Cache\FileCache::class,
            'cache_dir' => '%DIR_CACHE%',
        ],
        'Cache' => [
            'class'    => \ForkBB\Core\Cache::class,
            'provider' => '@FileCache',
        ],
        'Validator' => \ForkBB\Core\Validator::class,
        'View' => [
            'class'     => \ForkBB\Core\View::class,
            'cache_dir' => '%DIR_CACHE%',
            'views_dir' => '%DIR_VIEWS%',
        ],
        'Router' => [
            'class'    => \ForkBB\Core\Router::class,
            'base_url' => '%BASE_URL%',
        ],
        'Lang' => \ForkBB\Core\Lang::class,
        'Mail' => [
            'class' => \ForkBB\Core\Mail::class,
            'host'  => '%config.o_smtp_host%',
            'user'  => '%config.o_smtp_user%',
            'pass'  => '%config.o_smtp_pass%',
            'ssl'   => '%config.o_smtp_ssl%',
            'eol'   => '%EOL%',
        ],
        'Func' => \ForkBB\Core\Func::class,

        'config'     => '@ConfigModel:init',
        'bans'       => '@ModelBanList:init',
        'censorship' => '@CensorshipModel:init',
        'stats'      => '@ModelStats:init',
        'admins'     => '@ModelAdminList:init',
        'smilies'    => '@ModelSmileyList:init',
        'dbMap'      => '@ModelDBMap:init',
        'stopwords'  => '@ModelStopwords:init',
        'forums'     => '@ForumManager:init',
        'topics'     => \ForkBB\Models\Topic\Manager::class,
        'posts'      => \ForkBB\Models\Post\Manager::class,
        'user'       => '@users:current',
        'users'      => \ForkBB\Models\User\Manager::class,
        'groups'     => '@GroupManager:init',
        'categories' => '@CategoriesManager:init',
        'search'     => \ForkBB\Models\Search\Model::class,

        'Csrf' => [
            'class'  => \ForkBB\Core\Csrf::class,
            'Secury' => '@Secury',
            'key'    => '%user.password%%user.ip%%user.id%%BASE_URL%',
        ],
        'Online' => \ForkBB\Models\Online::class,
        'Cookie' => [
            'class'   => \ForkBB\Models\Cookie::class,
            'options' => '%COOKIE%',
        ],

        'Parser' => [
            'class' => \ForkBB\Core\Parser::class,
            'flag'  => ENT_HTML5,
        ],
        'Files' => [
            'class' => \ForkBB\Core\Files::class,
            'file'  => '%MAX_FILE_SIZE%',
            'img'   => '%MAX_IMG_SIZE%',
        ],

        'VLnoURL'    => \ForkBB\Models\Validators\NoURL::class,
        'VLusername' => \ForkBB\Models\Validators\Username::class,
        'VLemail'    => \ForkBB\Models\Validators\Email::class,

    ],
    'multiple'  => [
        'CtrlPrimary' => \ForkBB\Controllers\Primary::class,
        'Primary'     => '@CtrlPrimary:check',

        'CtrlRouting' => \ForkBB\Controllers\Routing::class,
        'Routing'     => '@CtrlRouting:routing',

        'Message'         => \ForkBB\Models\Pages\Message::class,
        'Index'           => \ForkBB\Models\Pages\Index::class,
        'Forum'           => \ForkBB\Models\Pages\Forum::class,
        'Topic'           => \ForkBB\Models\Pages\Topic::class,
        'Post'            => \ForkBB\Models\Pages\Post::class,
        'Edit'            => \ForkBB\Models\Pages\Edit::class,
        'Delete'          => \ForkBB\Models\Pages\Delete::class,
        'Rules'           => \ForkBB\Models\Pages\Rules::class,
        'Auth'            => \ForkBB\Models\Pages\Auth::class,
        'Userlist'        => \ForkBB\Models\Pages\Userlist::class,
        'Search'          => \ForkBB\Models\Pages\Search::class,
        'Register'        => \ForkBB\Models\Pages\Register::class,
        'Redirect'        => \ForkBB\Models\Pages\Redirect::class,
        'Maintenance'     => \ForkBB\Models\Pages\Maintenance::class,
        'Ban'             => \ForkBB\Models\Pages\Ban::class,
        'Debug'           => \ForkBB\Models\Pages\Debug::class,
        'Misc'            => \ForkBB\Models\Pages\Misc::class,
        'ProfileView'     => \ForkBB\Models\Pages\Profile\View::class,
        'ProfileEdit'     => \ForkBB\Models\Pages\Profile\Edit::class,
        'ProfileConfig'   => \ForkBB\Models\Pages\Profile\Config::class,
        'ProfilePass'     => \ForkBB\Models\Pages\Profile\Pass::class,
        'ProfileEmail'     => \ForkBB\Models\Pages\Profile\Email::class,
        'AdminIndex'      => \ForkBB\Models\Pages\Admin\Index::class,
        'AdminStatistics' => \ForkBB\Models\Pages\Admin\Statistics::class,
        'AdminOptions'    => \ForkBB\Models\Pages\Admin\Options::class,
        'AdminPermissions' => \ForkBB\Models\Pages\Admin\Permissions::class,
        'AdminCategories' => \ForkBB\Models\Pages\Admin\Categories::class,
        'AdminForums'     => \ForkBB\Models\Pages\Admin\Forums::class,
        'AdminGroups'     => \ForkBB\Models\Pages\Admin\Groups::class,
        'AdminCensoring'  => \ForkBB\Models\Pages\Admin\Censoring::class,
        'AdminMaintenance' => \ForkBB\Models\Pages\Admin\Maintenance::class,
        'AdminUsers'      => \ForkBB\Models\Pages\Admin\Users::class,
        'AdminHost'       => \ForkBB\Models\Pages\Admin\Host::class,

        'ConfigModel'     => \ForkBB\Models\Config\Model::class,
        'ConfigModelLoad' => \ForkBB\Models\Config\Load::class,
        'ConfigModelSave' => \ForkBB\Models\Config\Save::class,

        'OnlineInfo' => \ForkBB\Models\Online\Info::class,

        'ModelBanList'        => \ForkBB\Models\BanList::class,
        'BanListLoad'     => \ForkBB\Models\BanList\Load::class,
        'BanListCheck'    => \ForkBB\Models\BanList\Check::class,
        'BanListDelete'   => \ForkBB\Models\BanList\Delete::class,
        'BanListIsBanned' => \ForkBB\Models\BanList\IsBanned::class,

        'CensorshipModel'        => \ForkBB\Models\Censorship\Model::class,
        'CensorshipModelRefresh' => \ForkBB\Models\Censorship\Refresh::class,
        'CensorshipModelLoad'    => \ForkBB\Models\Censorship\Load::class,
        'CensorshipModelSave'    => \ForkBB\Models\Censorship\Save::class,

        'ModelStats'          => \ForkBB\Models\Stats::class,
        'StatsLoad' => \ForkBB\Models\Stats\Load::class,

        'ModelAdminList'      => \ForkBB\Models\AdminList::class,
        'AdminListLoad' => \ForkBB\Models\AdminList\Load::class,

        'ModelSmileyList'     => \ForkBB\Models\SmileyList::class,
        'SmileyListLoad' => \ForkBB\Models\SmileyList\Load::class,

        'ModelDBMap'          => \ForkBB\Models\DBMap::class,

        'ModelStopwords'      => \ForkBB\Models\Stopwords::class,

        'UserModel'                   => \ForkBB\Models\User\Model::class,
        'UserManagerLoad'             => \ForkBB\Models\User\Load::class,
        'UserManagerSave'             => \ForkBB\Models\User\Save::class,
        'UserManagerCurrent'          => \ForkBB\Models\User\Current::class,
        'UserManagerUpdateLastVisit'  => \ForkBB\Models\User\UpdateLastVisit::class,
        'UserManagerUpdateCountPosts' => \ForkBB\Models\User\UpdateCountPosts::class,
        'UserManagerIsUniqueName'     => \ForkBB\Models\User\IsUniqueName::class,
        'UserManagerUsersNumber'      => \ForkBB\Models\User\UsersNumber::class,
        'UserManagerPromote'          => \ForkBB\Models\User\Promote::class,
        'UserManagerFilter'           => \ForkBB\Models\User\Filter::class,

        'ForumModel'           => \ForkBB\Models\Forum\Model::class,
        'ForumModelCalcStat'   => \ForkBB\Models\Forum\CalcStat::class,
        'ForumManager'         => \ForkBB\Models\Forum\Manager::class,
        'ForumManagerRefresh'  => \ForkBB\Models\Forum\Refresh::class,
        'ForumManagerLoadTree' => \ForkBB\Models\Forum\LoadTree::class,
        'ForumManagerSave'     => \ForkBB\Models\Forum\Save::class,
        'ForumManagerDelete'   => \ForkBB\Models\Forum\Delete::class,
        'ForumManagerMarkread' => \ForkBB\Models\Forum\Markread::class,

        'TopicModel'         => \ForkBB\Models\Topic\Model::class,
        'TopicModelCalcStat' => \ForkBB\Models\Topic\CalcStat::class,
        'TopicManagerLoad'   => \ForkBB\Models\Topic\Load::class,
        'TopicManagerSave'   => \ForkBB\Models\Topic\Save::class,
        'TopicManagerDelete' => \ForkBB\Models\Topic\Delete::class,
        'TopicManagerView'   => \ForkBB\Models\Topic\View::class,

        'PostModel'               => \ForkBB\Models\Post\Model::class,
        'PostManagerLoad'         => \ForkBB\Models\Post\Load::class,
        'PostManagerSave'         => \ForkBB\Models\Post\Save::class,
        'PostManagerDelete'       => \ForkBB\Models\Post\Delete::class,
        'PostManagerPreviousPost' => \ForkBB\Models\Post\PreviousPost::class,
        'PostManagerView'         => \ForkBB\Models\Post\View::class,
        'PostManagerRebuildIndex' => \ForkBB\Models\Post\RebuildIndex::class,

        'GroupModel'         => \ForkBB\Models\Group\Model::class,
        'GroupManager'       => \ForkBB\Models\Group\Manager::class,
        'GroupManagerSave'   => \ForkBB\Models\Group\Save::class,
        'GroupManagerDelete' => \ForkBB\Models\Group\Delete::class,
        'GroupManagerPerm'   => \ForkBB\Models\Group\Perm::class,

        'CategoriesManager' => \ForkBB\Models\Categories\Manager::class,

        'SearchModelActionP' => \ForkBB\Models\Search\ActionP::class,
        'SearchModelActionT' => \ForkBB\Models\Search\ActionT::class,
        'SearchModelDelete'  => \ForkBB\Models\Search\Delete::class,
        'SearchModelIndex'   => \ForkBB\Models\Search\Index::class,
        'SearchModelTruncateIndex'   => \ForkBB\Models\Search\TruncateIndex::class,
        'SearchModelPrepare' => \ForkBB\Models\Search\Prepare::class,
        'SearchModelExecute' => \ForkBB\Models\Search\Execute::class,

        'ProfileRules' => \ForkBB\Models\Rules\Profile::class,
    ],
];
