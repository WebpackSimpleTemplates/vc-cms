<?php

namespace App\Payload;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class ConsultantStatusPayload
{
    public function __construct(
        #[NotBlank()]
        #[Type('string')]
        public readonly string $status,
        #[NotBlank()]
        #[Type('int')]
        public readonly int $increment,
        #[Type('int')]
        public readonly ?int $callId,
    )
    {}
}
