<?php

namespace App\Controller;

use App\Entity\Composition;
use App\Entity\Product;
use App\Form\CompositionType;
use App\Form\ProductType;
use App\Form\SearchProductType;
use App\Repository\ProductRepository;
use App\Services\Calculator;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;

/**
 * @Route("/admin/produit")
 */
class AdminProductController extends AbstractController
{
    const MAX_PER_PAGE = 12;

    /**
     * @Route("/", name="admin_product_index", methods={"GET"})
     */
    public function index(
        ProductRepository $productRepository,
        Request $request,
        PaginatorInterface $paginator
    ): Response {
        $products = $productRepository->findBy([], ['reference' => 'asc']);
        $form = $this->createForm(SearchProductType::class);
        $form->handleRequest($request);
        $data = $form->getData();
        if ($form->isSubmitted() && $form->isValid()) {
            $products = $productRepository->findByReference($data['search']);
        }
        $products = $paginator->paginate(
            $products,
            $request->query->getInt('page', 1), /*page number*/
            self::MAX_PER_PAGE /*limit per page*/
        );
        return $this->render('admin/product/index.html.twig', [
            'products' => $products,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/new", name="admin_product_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $product->setUpdatedAt(new DateTime());
            $entityManager->persist($product);
            $entityManager->flush();
            $this->addFlash('success', 'Le produit a bien été ajouté!');

            return $this->redirectToRoute('admin_product_edit', ['id'=>$product->getId()]);
        }

        return $this->render('admin/product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_product_show", methods={"GET"})
     */
    public function show(Product $product): Response
    {
        return $this->render('admin/product/show.html.twig', [
            'product' => $product,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_product_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Product $product, Calculator $calculator): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $note = $calculator->calculNoteProduct($product);
            $product->setNote($note);
            $product->setUpdatedAt(new DateTime());
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Le produit a bien été modifié!');

            return $this->redirectToRoute('admin_product_index');
        }
        $composition = new Composition();
        $formComposition = $this->createForm(CompositionType::class, $composition);
        $formComposition->handleRequest($request);

        if ($formComposition->isSubmitted() && $formComposition->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $composition->setProduct($product);
            $note = $calculator->calculNoteProduct($product);
            $product->setNote($note);
            $entityManager->persist($composition);
            $entityManager->flush();
            $this->addFlash('success', 'L\'ingrédient a bien été ajouté!');

            return $this->redirectToRoute('admin_product_edit', ['id'=>$product->getId()]);
        }

        return $this->render('admin/product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
            'formComposition' => $formComposition->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_product_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Product $product): Response
    {
        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($product);
            $entityManager->flush();
            $this->addFlash('success', 'Le produit a bien été supprimé!');
        }

        return $this->redirectToRoute('admin_product_index');
    }
}
