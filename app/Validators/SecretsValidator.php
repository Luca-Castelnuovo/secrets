<?php

namespace App\Validators;

use CQ\Validators\Validator;
use Respect\Validation\Validator as v;

class SecretsValidator extends Validator
{
    /**
     * Validate json submission.
     *
     * @param object $data
     */
    public static function createStore($data)
    {
        $v = v::attribute('name', v::alnum(' ', '-')->length(1, 128))
            ->attribute('data', v::iterableType());

        self::validate($v, $data);
    }

    /**
     * Validate json submission.
     *
     * @param object $data
     */
    public static function getStore($data)
    {
        $v = v::attribute('key', v::base64());

        self::validate($v, $data);
    }

    /**
     * Validate json submission.
     *
     * @param object $data
     */
    public static function updateStore($data)
    {
        $v = v::attribute('key', v::base64())
            ->attribute('data', v::iterableType());

        self::validate($v, $data);
    }
}
