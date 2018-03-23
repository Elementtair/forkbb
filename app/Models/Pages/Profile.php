<?php

namespace ForkBB\Models\Pages;

use ForkBB\Models\Page;
use ForkBB\Models\User\Model as User;

class Profile extends Page
{
    use CrumbTrait;

    /**
     * Подготавливает данные для шаблона
     *
     * @param array $args
     * @param string $method
     *
     * @return Page
     */
    public function view(array $args, $method)
    {
        $curUser = $this->c->users->load((int) $args['id']);
        if (! $curUser instanceof User || ($curUser->isUnverified && ! $this->user->isAdmMod)) {
            return $this->c->Message->message('Bad request');
        }

        $this->c->Lang->load('profile');

        $myProf   = $curUser->id === $this->user->id;
        $isEdit   = false;
        $clSuffix = $isEdit ? '-edit' : '';

        if ($isEdit) {
            $form = [
                'action' => $this->c->Router->link(''),
                'hidden' => [
                    'token' => $this->c->Csrf->create(''),
                ],
                'sets'   => [],
                'btns'   => [
                    'save' => [
                        'type'      => 'submit',
                        'value'     => \ForkBB\__('Save changes'),
                        'accesskey' => 's',
                    ],
                ],
            ];
        } else {
            $form = ['sets' => []];
        }

        $fieldset = [];
        $fieldset[] = [
            'class' => 'usertitle',
            'type'  => 'wrap',
        ];
        $fieldset['username'] = [
            'id'        => 'username',
            'type'      => 'text',
            'maxlength' => 25,
            'caption'   => \ForkBB\__('Username'),
            'required'  => true,
            'pattern'   => '^.{2,25}$',
            'value'     => $curUser->username,
        ];
        $fieldset['title'] = [
            'id'        => 'title',
            'type'      => 'text',
            'maxlength' => 50,
            'caption'   => \ForkBB\__('Title'),
            'value'     => $isEdit ? $curUser->title : $curUser->title(),
        ];
        $fieldset[] = [
            'type' => 'endwrap',
        ];
        if ('1' == $this->c->config->o_avatars && $curUser->avatar) {
            $fieldset['avatar'] = [
                'id'      => 'avatar',
                'type'    => 'yield',
                'caption' => \ForkBB\__('Avatar'),
                'value'   => 'avatar',
            ];
        }
        $form['sets'][] = [
            'id'     => 'header',
            'class'  => 'header' . $clSuffix,
#            'legend' => \ForkBB\__('Options'),
            'fields' => $fieldset,
        ];

        if ($this->user->isAdmMod && ($isEdit || '' != $curUser->admin_note)) {
            $fieldset = [];
            $fieldset['admin_note'] = [
                'id'        => 'admin_note',
                'type'      => 'text',
                'maxlength' => 30,
                'caption'   => \ForkBB\__('Admin note'),
                'value'     => $curUser->admin_note,
            ];
            $form['sets'][] = [
                'id'     => 'note',
                'class'  => 'data' . $clSuffix,
                'legend' => \ForkBB\__('Admin note'),
                'fields' => $fieldset,
            ];
        }

        $fieldset = [];
        if ($isEdit || '' != $curUser->realname) {
            $fieldset['realname'] = [
                'id'        => 'realname',
                'type'      => 'text',
                'maxlength' => 40,
                'caption'   => \ForkBB\__('Realname'),
                'value'     => $isEdit ? $curUser->realname : \ForkBB\cens($curUser->realname),
            ];
        }
        if ($isEdit || $curUser->gender) {
            $fieldset['gender'] = [
                'id'      => 'gender',
                'type'    => 'radio',
                'value'   => $curUser->gender,
                'values'  => [
                    0 => \ForkBB\__('Unknown'),
                    1 => \ForkBB\__('Male'),
                    2 => \ForkBB\__('Female'),
                ],
                'caption' => \ForkBB\__('Gender'),
            ];
        }
        if ($isEdit || '' != $curUser->location) {
            $fieldset['location'] = [
                'id'        => 'location',
                'type'      => 'text',
                'maxlength' => 40,
                'caption'   => \ForkBB\__('Location'),
                'value'     => $isEdit ? $curUser->location : \ForkBB\cens($curUser->location),
            ];
        }
        if ($isEdit) {
            $fieldset['url'] = [
                'id'        => 'website',
                'type'      => 'text',
                'maxlength' => 100,
                'caption'   => \ForkBB\__('Website'),
                'value'     => $curUser->url
            ];
        } elseif ($curUser->url) {
            $fieldset['url'] = [
                'id'      => 'website',
                'type'    => 'link',
                'caption' => \ForkBB\__('Website'),
                'value'   => \ForkBB\cens($curUser->url),
                'href'    => \ForkBB\cens($curUser->url),
            ];
        }
        if (! empty($fieldset)) {
            $form['sets'][] = [
                'id'     => 'personal',
                'class'  => 'data' . $clSuffix,
                'legend' => \ForkBB\__('Section personal'),
                'fields' => $fieldset,
            ];
        }

        $fieldset = [];
        if ($isEdit) {
            $fieldset['signature'] = [
                'id'      => 'signature',
                'type'    => 'textarea',
                'value'   => $curUser->signature,
                'caption' => \ForkBB\__('Signature'),
            ];
        } elseif ('' != $curUser->signature) {
            $fieldset['signature'] = [
                'id'      => 'signature',
                'type'    => 'yield',
                'caption' => \ForkBB\__('Signature'),
                'value'   => 'signature',
            ];
        }
        if (! empty($fieldset)) {
            $form['sets'][] = [
                'id'     => 'signature',
                'class'  => 'data' . $clSuffix,
                'legend' => \ForkBB\__('Signature'),
                'fields' => $fieldset,
            ];
        }

        $fieldset = [];
        $fieldset['registered'] = [
            'id'      => 'registered',
            'type'    => 'str',
            'value'   => \ForkBB\dt($curUser->registered, true),
            'caption' => \ForkBB\__('Registered info'),
        ];
        if ($myProf || $this->user->isAdmMod) {
            $fieldset['lastvisit'] = [
                'id'      => 'lastvisit',
                'type'    => 'str',
                'value'   => \ForkBB\dt($curUser->last_visit, true),
                'caption' => \ForkBB\__('Last visit info'),
            ];
        }
        $fieldset['lastpost'] = [
            'id'      => 'lastpost',
            'type'    => 'str',
            'value'   => \ForkBB\dt($curUser->last_post, true),
            'caption' => \ForkBB\__('Last post info'),
        ];
        if ($curUser->num_posts) {
            if ('1' == $this->user->g_search) {
                $fieldset['posts'] = [
                    'id'      => 'posts',
                    'type'    => 'link',
                    'caption' => \ForkBB\__('Posts info'),
                    'value'   => $this->user->showPostCount ? \ForkBB\num($curUser->num_posts) : \ForkBB\__('Show posts'),
                    'href'    => '',
                    'title'   => \ForkBB\__('Show posts'),
                ];
                $fieldset['topics'] = [
                    'id'      => 'topics',
                    'type'    => 'link',
                    'caption' => \ForkBB\__('Topics info'),
                    'value'   => $this->user->showPostCount ? \ForkBB\num($curUser->num_topics) : \ForkBB\__('Show topics'),
                    'href'    => '',
                    'title'   => \ForkBB\__('Show topics'),
                ];
            } elseif ($this->user->showPostCount) {
                $fieldset['posts'] = [
                    'id'      => 'posts',
                    'type'    => 'str',
                    'caption' => \ForkBB\__('Posts info'),
                    'value'   => \ForkBB\num($curUser->num_posts),
                ];
                $fieldset['topics'] = [
                    'id'      => 'topics',
                    'type'    => 'str',
                    'caption' => \ForkBB\__('Topics info'),
                    'value'   => \ForkBB\num($curUser->num_topics),
                ];
            }
        }
        $form['sets'][] = [
            'id'     => 'activity',
            'class'  => 'data' . $clSuffix,
            'legend' => \ForkBB\__('User activity'),
            'fields' => $fieldset,
        ];

        $this->fIndex    = $myProf ? 'profile' : 'userlist';
        $this->nameTpl   = 'profile';
        $this->onlinePos = 'profile-' . $curUser->id; // ????
        $this->canonical = $curUser->link;
        $this->title     = \ForkBB\__('%s\'s profile', $curUser->username);
        $this->crumbs    = $this->crumbs([$curUser->link, $this->title], [$this->c->Router->link('Userlist'), \ForkBB\__('User list')]);
        $this->form      = $form;
        $this->curUser   = $curUser;

        $this->linkEditProfile  = $this->c->Router->link('EditUserProfile',  ['id' => $curUser->id]);
        $this->linkEditSettings = $this->c->Router->link('EditUserSettings', ['id' => $curUser->id]);

        return $this;
    }
}
