<?php

namespace App\Payload;

use Symfony\Component\Validator\Constraints\Type;

class MessagePayload {
    public function __construct(
        #[Type("string")]
        public readonly ?string $message,
        #[Type("int")]
        public readonly ?int $fileSize,
        #[Type("string")]
        public readonly ?string $filePath,
        #[Type("string")]
        public readonly ?string $fileName,
        #[Type("string")]
        public readonly ?string $imageUrl,
    ) {}
}
