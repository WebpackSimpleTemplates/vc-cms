<?php

namespace App\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;

class UploadFileTransformer implements DataTransformerInterface
{
    public function __construct(
        private KernelInterface $kernel
    ) {}

    public function transform(mixed $value): mixed
    {
        if (!$value) {
            return "";
        }

        $filepath = $this->kernel->getProjectDir()."/public/".$value;
        return new File($filepath);
    }

    public function reverseTransform(mixed $value): mixed
    {
        if (!($value instanceof UploadedFile)) {
            return $value;
        }

        $filename = uniqid()."-".$value->getClientOriginalName();

        $value->move($this->kernel->getProjectDir()."/public/uploads/", $filename);

        return "/uploads"."/".$filename;
    }

}
