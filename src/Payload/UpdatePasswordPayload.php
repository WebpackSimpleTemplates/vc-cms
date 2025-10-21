<?php

namespace App\Payload;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class UpdatePassswordPayload
{
    public function __construct(
        #[NotBlank()]
        #[Type('string')]
        public readonly string $password,
    )
    {}
}
