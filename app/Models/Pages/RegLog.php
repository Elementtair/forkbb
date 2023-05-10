<?php
/**
 * This file is part of the ForkBB <https://github.com/forkbb>.
 *
 * @copyright (c) Visman <mio.visman@yandex.ru, https://github.com/MioVisman>
 * @license   The MIT License (MIT)
 */

declare(strict_types=1);

namespace ForkBB\Models\Pages;

use ForkBB\Core\Validator;
use ForkBB\Core\Exceptions\MailException;
use ForkBB\Models\Page;
use ForkBB\Models\Provider\Driver;
use ForkBB\Models\User\User;
use function \ForkBB\__;

class RegLog extends Page
{
    /**
     * Обрабатывает нажатие одной из кнопок провайдеров
     */
    public function redirect(): Page
    {
        if (
            1 !== $this->c->config->b_oauth_allow
            || empty($list = $this->c->providers->active())
        ) {
            return $this->c->Message->message('Bad request');
        }

        $rules = [
            'token' => 'token:RegLogRedirect',
        ];

        foreach ($list as $name) {
            $rules[$name] = 'string';
        }

        $v = $this->c->Validator->reset()->addRules($rules);

        if (
            ! $v->validation($_POST)
            || 1 !== \count($form = $v->getData(false, ['token']))
        ) {
            return $this->c->Message->message('Bad request');
        }

        return $this->c->Redirect->url($this->c->providers->init()->get(\array_key_first($form))->linkAuth);
    }

    /**
     * Обрабатывает ответ сервера
     */
    public function callback(array $args): Page
    {
        if (
            1 !== $this->c->config->b_oauth_allow
            || empty($list = $this->c->providers->active())
            || empty($list[$args['name']])
        ) {
            return $this->c->Message->message('Bad request');
        }

        $this->c->Lang->load('admin_providers');

        $provider = $this->c->providers->init()->get($args['name']);
        $stages   = [1, 2, 3];

        foreach ($stages as $stage) {
            $result = match ($stage) {
                1 => $provider->verifyAuth($_GET),
                2 => $provider->reqAccessToken(),
                3 => $provider->reqUserInfo(),
            };

            if (true !== $result) {
                return $this->c->Message->message($provider->error);
            }
        }

        // гость
        if ($this->user->isGuest) {
            $uid = $this->c->providerUser->findUser($provider);

            // регистрация
            if (empty($uid)) {
                // на форуме есть пользователь с таким email
                if ($this->c->providerUser->findEmail($provider) > 0) {
                    $auth         = $this->c->Auth;
                    $auth->fIswev = ['i', ['Email message', __($provider->name)]];

                    return $auth->forget([], 'GET', $provider->userEmail);
                }

                if (1 !== $this->c->config->b_regs_allow) {
                    return $this->c->Message->message('No new regs');
                }

                $user = $this->c->users->create();

                $user->username        = $this->nameGenerator($provider);
                $user->password        = \password_hash($this->c->Secury->randomPass(72), \PASSWORD_DEFAULT);
                $user->group_id        = $this->c->config->i_default_user_group;
                $user->email           = $provider->userEmail;
                $user->email_confirmed = $provider->userEmailVerifed ? 1 : 0;
                $user->activate_string = '';
                $user->u_mark_all_read = \time();
                $user->email_setting   = $this->c->config->i_default_email_setting;
                $user->timezone        = $this->c->config->o_default_timezone;
                $user->language        = $this->user->language;
                $user->style           = $this->user->style;
                $user->registered      = \time();
                $user->registration_ip = $this->user->ip;
                $user->ip_check_type   = 0;
                $user->signature       = '';
                $user->location        = $provider->userLocation;
                $user->url             = $provider->userURL;

                $newUserId = $this->c->users->insert($user);

                if (true !== $this->c->providerUser->registration($user, $provider)) {
                    throw new RuntimeException('Failed to insert data'); // ??????????????????????????????????????????
                }

                $this->c->Log->info('OAuth Reg: ok', [
                    'user'     => $user->fLog(),
                    'provider' => $provider->name,
                    'userInfo' => $provider->userInfo,
                    'headers'  => true,
                ]);

            } else {
                $user = $this->c->users->load($uid);
            }

            // вход
            return $this->c->Auth->login(['user' => $user], 'POST');

        // пользователь
        } else {

        }

        exit(var_dump($provider->userId, $provider->userName, $provider->userEmail, $this->c->NormEmail->normalize($provider->userEmail)));
    }

    /**
     * Подбирает уникальное имя для регистрации пользователя
     */
    protected function nameGenerator(Driver $provider): string
    {
        $names = [];

        if ('' != $provider->userName) {
            $names[] = $provider->userName;
        }

        if ('' != $provider->userLogin) {
            $names[] = $provider->userLogin;
        }

        if ('' != ($tmp = (string) \strstr($provider->userEmail, '@', true))) {
            $names[] = $tmp;
        }

        $names[] = 'user' . \time();
        $v       = $this->c->Validator->reset()->addRules(['name' => 'required|string:trim|username|noURL:1']);
        $end     = '';
        $i       = 0;

        while ($i < 100) {
            foreach ($names as $name) {
                if ($v->validation(['name' => $name . $end])) {
                    return $v->name;
                }
            }

            $end = '_' . $this->c->Secury->randomHash(4);
            ++$i;
        }

        throw new RuntimeException('Failed to generate unique username');
    }
}