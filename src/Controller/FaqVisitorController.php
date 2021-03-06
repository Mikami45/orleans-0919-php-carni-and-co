<?php

namespace App\Controller;

use App\Entity\Faq;
use App\Repository\FaqRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/faq")
 */
class FaqVisitorController extends AbstractController
{
    /**
     * @Route("/", name="faq_visitor", methods={"GET"})
     */
    public function index(FaqRepository $faqRepository): Response
    {
        return $this->render('/user/faq/index.html.twig', [
            'faqs' => $faqRepository->findAll(),
        ]);
    }
}
