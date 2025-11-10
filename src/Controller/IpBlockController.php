<?php

namespace App\Controller;

use App\Entity\IpBlock;
use App\Form\IpBlockType;
use App\Repository\IpBlockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/manage/ip/block')]
final class IpBlockController extends AbstractController
{
    #[Route(name: 'app_ip_block_index', methods: ['GET'])]
    public function index(IpBlockRepository $ipBlockRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $ip = $request->query->get('ip');
        $qb = $ipBlockRepository->getMany();

        if ($ip) {
            $qb->where("ib.ip = :ip");
            $qb->setParameter("ip", $ip);
        }

        return $this->render('ip_block/index.html.twig', [
            'ip' => $ip,
            'pagination' => $paginator->paginate(
                $qb,
                $request->query->getInt("page", 1),
                10,
            ),
        ]);
    }

    #[Route('/new', name: 'app_ip_block_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ipBlock = new IpBlock();

        if ($request->query->get('ip')) {
            $ipBlock->setIp($request->query->get('ip'));
        }

        $form = $this->createForm(IpBlockType::class, $ipBlock);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ipBlock);
            $entityManager->flush();

            return $this->redirectToRoute('app_ip_block_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ip_block/new.html.twig', [
            'ip_block' => $ipBlock,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ip_block_show', methods: ['GET'])]
    public function show(IpBlock $ipBlock): Response
    {
        return $this->render('ip_block/show.html.twig', [
            'ip_block' => $ipBlock,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_ip_block_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, IpBlock $ipBlock, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(IpBlockType::class, $ipBlock);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_ip_block_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ip_block/edit.html.twig', [
            'ip_block' => $ipBlock,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_ip_block_delete', methods: ['POST'])]
    public function delete(Request $request, IpBlock $ipBlock, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ipBlock->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($ipBlock);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_ip_block_index', [], Response::HTTP_SEE_OTHER);
    }
}
