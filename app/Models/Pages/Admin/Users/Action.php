<?php
/**
 * This file is part of the ForkBB <https://forkbb.ru, https://github.com/forkbb>.
 *
 * @copyright (c) Visman <mio.visman@yandex.ru, https://github.com/MioVisman>
 * @license   The MIT License (MIT)
 */

declare(strict_types=1);

namespace ForkBB\Models\Pages\Admin\Users;

use ForkBB\Core\Validator;
use ForkBB\Models\Page;
use ForkBB\Models\Pages\Admin\Users;
use SensitiveParameter;
use RuntimeException;
use function \ForkBB\__;

class Action extends Users
{
    /**
     * Возвращает список имен пользователей
     */
    protected function nameList(array $users): array
    {
        $result = [];

        foreach ($users as $user) {
            $result[] = $user->username;
        }

        \sort($result, \SORT_STRING | \SORT_FLAG_CASE);

        return $result;
    }

    /**
     * Подготавливает данные для шаблона(ов) действия
     */
    public function view(array $args, string $method): Page
    {
        if (isset($args['token'])) {
            if (! $this->c->Csrf->verify($args['token'], 'AdminUsersAction', $args)) {
                return $this->c->Message->message($this->c->Csrf->getError());
            }

            $profile = true;

        } else {
            $profile = false;
        }

        $error = false;

        switch ($args['action']) {
/*
            case self::ACTION_BAN:
                if (! $this->userRules->banUsers) {
                    $error = true;
                }
                break;
*/
            case self::ACTION_DEL:
                if (! $this->userRules->deleteUsers) {
                    $error = true;
                }

                break;
            case self::ACTION_CHG:
                if (
                    $profile
                    && ! $this->userRules->canChangeGroup($this->c->users->load((int) $args['ids']), true)
                ) {
                    $error = true;

                } elseif (
                    ! $profile
                    && ! $this->userRules->changeGroup
                ) {
                    $error = true;
                }

                break;
            default:
                $error = true;
        }

        if ($error) {
            return $this->c->Message->message('Bad request');
        }

        $ids = $this->checkSelected(\explode('-', $args['ids']), $args['action'], $profile);

        if (false === $ids) {
            $message = $this->c->Message->message('Action not available');
            $message->fIswev = $this->fIswev; // тут идет дополнение, а не замена

            return $message;
        }

        $this->userList = $this->c->users->loadByIds($ids);

        switch ($args['action']) {
/*
            case self::ACTION_BAN:
                return $this->ban($args, $method);
*/
            case self::ACTION_DEL:
                return $this->delete($args, $method);
            case self::ACTION_CHG:
                return $this->change($args, $method, $profile);
            default:
                throw new RuntimeException("The action {$args['action']} is unavailable");
        }
    }

    /**
     * Удаляет пользователей
     */
    protected function delete(array $args, string $method): Page
    {
        if ('POST' === $method) {
            $v = $this->c->Validator->reset()
                ->addRules([
                    'token'        => 'token:AdminUsersAction',
                    'confirm'      => 'required|integer|in:0,1',
                    'delete_posts' => 'required|integer|in:0,1',
                    'delete'       => 'required|string',
                ])->addAliases([
                ])->addArguments([
                    'token' => $args,
                ]);

            if (
                ! $v->validation($_POST)
                || 1 !== $v->confirm
            ) {
                return $this->c->Redirect->page('AdminUsers')->message('No confirm redirect', FORK_MESS_WARN);
            }

            if (1 === $v->delete_posts) {
                foreach ($this->userList as $user) {
                    $user->__deleteAllPost = true;
                }
            }

            $this->c->users->delete(...$this->userList);

            $this->c->forums->reset();

            return $this->c->Redirect->page('AdminUsers')->message('Users delete redirect', FORK_MESS_SUCC);
        }

        $this->nameTpl   = 'admin/form';
        $this->classForm = ['delete-users'];
        $this->titleForm = 'Deleting users';
        $this->aCrumbs[] = [$this->c->Router->link('AdminUsersAction', $args), 'Deleting users'];
        $this->form      = $this->formDelete($args);

        return $this;
    }

    /**
     * Создает массив данных для формы удаления пользователей
     */
    protected function formDelete(array $args): array
    {
        $yn    = [1 => __('Yes'), 0 => __('No')];
        $names = \implode(', ', $this->nameList($this->userList));
        $form  = [
            'action' => $this->c->Router->link('AdminUsersAction', $args),
            'hidden' => [
                'token' => $this->c->Csrf->create('AdminUsersAction', $args),
            ],
            'sets'   => [
                'options' => [
                    'fields' => [
                        'confirm' => [
                            'type'    => 'radio',
                            'value'   => 0,
                            'values'  => $yn,
                            'caption' => 'Delete users',
                            'help'    => ['Confirm delete info', $names],
                        ],
                        'delete_posts' => [
                            'type'    => 'radio',
                            'value'   => 0,
                            'values'  => $yn,
                            'caption' => 'Delete posts',
                        ],
                    ],
                ],
                'info2' => [
                    'inform' => [
                        [
                            'message' => 'Delete warning',
                        ],
                    ],
                ],
            ],
            'btns'   => [
                'delete'  => [
                    'type'  => 'submit',
                    'value' => __('Delete users'),
                ],
                'cancel'  => [
                    'type'  => 'btn',
                    'value' => __('Cancel'),
                    'href'  => $this->c->Router->link('AdminUsers'),
                ],
            ],
        ];

        return $form;
    }

    /**
     * Возвращает список групп доступных для замены
     */
    protected function groupListForChange(bool $profile): array
    {
        $list = [];

        foreach ($this->c->groups->repository as $id => $group) {
                $list[$id] = $group->g_title;
        }

        unset($list[FORK_GROUP_GUEST]);

        if (! $profile) {
            unset($list[FORK_GROUP_ADMIN]);

        } elseif (! $this->user->isAdmin) {
            $list = [FORK_GROUP_MEMBER => $list[FORK_GROUP_MEMBER]];
        }

        return $list;
    }

    /**
     * Изменяет группу пользователей
     */
    protected function change(array $args, string $method, bool $profile): Page
    {
        $rulePass = 'absent';

        if ($profile) {
            $user = $this->c->users->load((int) $args['ids']);
            $link = $this->c->Router->link(
                'EditUserProfile',
                [
                    'id' => $user->id,
                ]
            );

            if (
                $user->isAdmin
                || $user->id === $this->user->id
            ) {
                $rulePass = 'required|string:trim|max:100000|check_password';
            }

        } else {
            $link = $this->c->Router->link('AdminUsers');
        }

        if ('POST' === $method) {
            $v = $this->c->Validator->reset()
                ->addValidators([
                    'check_password' => [$this, 'vCheckPassword'],
                ])->addRules([
                    'token'     => 'token:AdminUsersAction',
                    'new_group' => 'required|integer|in:' . \implode(',', \array_keys($this->groupListForChange($profile))),
                    'confirm'   => 'required|integer|in:0,1',
                    'password'  => $rulePass,
                    'move'      => 'required|string',
                ])->addAliases([
                ])->addArguments([
                    'token' => $args,
                ]);

            $redirect = $this->c->Redirect;

            if ($v->validation($_POST)) {
                if (1 !== $v->confirm) {
                    return $redirect->url($link)->message('No confirm redirect', FORK_MESS_WARN);
                }

                $this->c->users->changeGroup($v->new_group, ...$this->userList);

                $this->c->forums->reset();

                if ($profile) {
                    if ($this->c->ProfileRules->setUser($user)->editProfile) {
                        $redirect->url($link);

                    } else {
                        $redirect->url($user->link);
                    }

                } else {
                    $redirect->page('AdminUsers');
                }

                return $redirect->message('Users move redirect', FORK_MESS_SUCC);

            }

            $this->fIswev = $v->getErrors();
        }

        $this->nameTpl   = 'admin/form';
        $this->classForm = ['change-group'];
        $this->titleForm = 'Change user group';
        $this->aCrumbs[] = [$this->c->Router->link('AdminUsersAction', $args), 'Change user group'];
        $this->form      = $this->formChange($args, $profile, $link, 'absent' !== $rulePass);

        return $this;
    }

    /**
     * Проверяет пароль на совпадение с текущим пользователем
     */
    public function vCheckPassword(Validator $v, #[SensitiveParameter] string $password): string
    {
        if (! \password_verify($password, $this->user->password)) {
            $v->addError('Invalid passphrase');
        }

        return $password;
    }

    /**
     * Создает массив данных для формы изменения группы пользователей
     */
    protected function formChange(array $args, bool $profile, string $linkCancel, bool $checkPass): array
    {
        $yn    = [1 => __('Yes'), 0 => __('No')];
        $names = \implode(', ', $this->nameList($this->userList));
        $form  = [
            'action' => $this->c->Router->link('AdminUsersAction', $args),
            'hidden' => [
                'token' => $this->c->Csrf->create('AdminUsersAction', $args),
            ],
            'sets'   => [
                'options' => [
                    'fields' => [
                        'new_group' => [
                            'type'      => 'select',
                            'options'   => $this->groupListForChange($profile),
                            'value'     => $this->c->config->i_default_user_group,
                            'caption'   => 'New group label',
                            'help'      => ['New group help', $names],
                        ],
                        'confirm' => [
                            'type'    => 'radio',
                            'value'   => 0,
                            'values'  => $yn,
                            'caption' => 'Move users',
                        ],
                    ],
                ],
            ],
            'btns'   => [
                'move'  => [
                    'type'  => 'submit',
                    'value' => __('Move users'),
                ],
                'cancel'  => [
                    'type'  => 'btn',
                    'value' => __('Cancel'),
                    'href'  => $linkCancel,
                ],
            ],
        ];

        if ($checkPass) {
            $form['sets']['options']['fields']['password'] = [
                'type'      => 'password',
                'caption'   => 'Your passphrase',
                'required'  => true,
            ];
        }

        return $form;
    }
}
