<?php

namespace App\Payload;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class QualityPayload
{
    public function __construct(
        #[NotBlank()]
        #[Type("int")]
        public readonly int $quality,
    )
    {}
}
