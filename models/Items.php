<?php

namespace Test;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Message;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Uniqueness;
use Phalcon\Validation\Validator\InclusionIn;

class Items extends Model
{
    public function validation()
    {
        $validator = new Validation();

        // Robot name must be unique
        $validator->add(
            'id',
            new Uniqueness(
                [
                    'field'   => 'id',
                    'message' => "Item's id should not be same!",
                ]
            )
        );
	}
}
