<?php

namespace ForkBB\Models;

use ForkBB\Core\Container;
use ForkBB\Core\Validator;
use ForkBB\Models\User\Model as User;

class Validators
{
    /**
     * Контейнер
     * @var Container
     */
    protected $c;

    /**
     * Конструктор
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->c = $container;
    }

    /**
     * Дополнительная проверка username
     *
     * @param Validator $v
     * @param string $username
     * @param string $z
     * @param mixed $originalUser
     *
     * @return string
     */
    public function vCheckUsername(Validator $v, $username, $z, $originalUser)
    {
        if ($originalUser instanceof User) {
            $id   = $originalUser->id;
            $old  = $originalUser->username;
        } else {
            $id   = null;
            $old  = null;
        }

        if ($old !== $username) {

            $user = $this->c->users->create(['id' => $id, 'username' => $username]);

            // username = Гость
            if (\preg_match('%^(guest|' . \preg_quote(\ForkBB\__('Guest'), '%') . ')$%iu', $username)) { // ???? а зачем?
                $v->addError('Username guest');
            // цензура
            } elseif ($this->c->censorship->censor($username) !== $username) {
                $v->addError('Username censor');
            // username забанен
            } elseif ($this->c->bans->isBanned($user) > 0) {
                $v->addError('Banned username');
            // есть пользователь с похожим именем
            } elseif (empty($v->getErrors()) && ! $this->c->users->isUniqueName($user)) {
                $v->addError('Username not unique');
            }
        }

        return $username;
    }

    /**
     * Дополнительная проверка на отсутствие url в значении
     *
     * @param Validator $v
     * @param mixed $value
     * @param string $flag
     *
     * @return mixed
     */
    public function vNoURL(Validator $v, $value, $flag)
    {
        $flag = empty($flag) || '1' != $this->c->user->g_post_links;

        if ($flag && \preg_match('%https?://|www\.%i', $value)) {
            $v->addError('The :alias contains a link');
        }
        return $value;
    }

}
