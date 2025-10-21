<?php

namespace App\Controller;

use App\Repository\PushRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/files')]
final class ApiFilesController extends AbstractController
{
    #[Route('/test')]
    public function test(PushRepository $pushRepository)
    {
        $pushRepository->push("test/", "test", ["data" => "test"]);

        return new Response(null, 204);
    }

    #[Route('/upload', name: 'api_upload_file')]
    public function index(Request $request, KernelInterface $kernel): JsonResponse
    {
        /** @var UploadedFile $file */
        $file = $request->files->get("file");

        if (!$file) {
            return $this->json([ "error" => "field 'file' is required, and must be file" ], 422);
        }

        $filename = uniqid()."-".$file->getClientOriginalName();

        $file->move($kernel->getProjectDir()."/public/uploads/", $filename);

        return $this->json([
            "url" => "/uploads"."/".$filename,
            "name" => $filename,
            "size" => filesize($kernel->getProjectDir()."/public/uploads/".$filename)
        ]);
    }
}
