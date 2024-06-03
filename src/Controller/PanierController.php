<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class PanierController extends AbstractController
{
    #[Route('/panier/add/{id}', name: 'panier_add', methods: ['POST'])]
    public function addToCart(SessionInterface $session, ArticleRepository $articleRepository, int $id): Response
    {
        $article = $articleRepository->find($id);

        if (!$article) {
            return new JsonResponse(['message' => 'Le produit n\'existe pas'], Response::HTTP_NOT_FOUND);
        }

        $panier = $session->get('panier', []);

        if (!isset($panier[$id])) {
            $panier[$id] = [
                'article' => $article,
                'quantity' => 0,
            ];
        }

        $panier[$id]['quantity']++;

        $session->set('panier', $panier);

        return new JsonResponse(['message' => 'Article ajouté au panier !'], Response::HTTP_OK);
    }

    #[Route('/panier/increase/{id}', name: 'panier_increase', methods: ['POST'])]
    public function increaseQuantity(SessionInterface $session, int $id): Response
    {
        $panier = $session->get('panier', []);

        if (isset($panier[$id]) && $panier[$id]['quantity'] < 10) {
            $panier[$id]['quantity']++;
        }

        $session->set('panier', $panier);

        return new JsonResponse([
            'quantity' => $panier[$id]['quantity'],
            'totalItem' => $panier[$id]['article']->getPrix() * $panier[$id]['quantity'],
            'totalPanier' => array_reduce($panier, function ($total, $item) {
                return $total + ($item['article']->getPrix() * $item['quantity']);
            }, 0),
        ], Response::HTTP_OK);
    }

    #[Route('/panier/decrease/{id}', name: 'panier_decrease', methods: ['POST'])]
    public function decreaseQuantity(SessionInterface $session, int $id): Response
    {
        $panier = $session->get('panier', []);

        if (isset($panier[$id]) && $panier[$id]['quantity'] > 1) {
            $panier[$id]['quantity']--;
        }

        $session->set('panier', $panier);

        return new JsonResponse([
            'quantity' => $panier[$id]['quantity'],
            'totalItem' => $panier[$id]['article']->getPrix() * $panier[$id]['quantity'],
            'totalPanier' => array_reduce($panier, function ($total, $item) {
                return $total + ($item['article']->getPrix() * $item['quantity']);
            }, 0),
        ], Response::HTTP_OK);
    }

    #[Route('/panier/remove/{id}', name: 'panier_remove', methods: ['POST'])]
    public function removeItem(SessionInterface $session, int $id): Response
    {
        $panier = $session->get('panier', []);

        if (isset($panier[$id])) {
            unset($panier[$id]);
        }

        $session->set('panier', $panier);

        return new JsonResponse([
            'total' => array_reduce($panier, function ($total, $item) {
                return $total + ($item['article']->getPrix() * $item['quantity']);
            }, 0),
            'message' => 'Article supprimé du panier !',
        ], Response::HTTP_OK);
    }

    #[Route('/panier/vider', name: 'panier_vider', methods: ['POST'])]
    public function viderPanier(SessionInterface $session): Response
    {
        $session->remove('panier');
        $this->addFlash('success', 'Panier vidé avec succès !');

        return $this->redirectToRoute('panier_show');
    }

    #[Route('/panier', name: 'panier_show')]
    public function showCart(SessionInterface $session): Response
    {
        $panier = $session->get('panier', []);

        return $this->render('panier/show.html.twig', [
            'panier' => $panier,
        ]);
    }

    #[Route('/commande', name: 'commande_create', methods: ['POST'])]
    public function createCommande(SessionInterface $session, ArticleRepository $articleRepository, EntityManagerInterface $entityManager): Response
    {
        $panier = $session->get('panier', []);

        if (empty($panier)) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('panier_show');
        }

        $commande = new Commande();
        $commande->setUser($this->getUser());
        $total = 0;

        foreach ($panier as $item) {
            $article = $articleRepository->find($item['article']->getId());
            if ($article) {
                $commande->addArticle($article);
                $total += $article->getPrix() * $item['quantity'] * 100; // Multiplier par 100 pour stocker en centimes
            }
        }

        $commande->setTotal($total);
        $commande->setCreatedAt(new \DateTime());
        $entityManager->persist($commande);
        $entityManager->flush();

        $session->remove('panier');

        $this->addFlash('success', 'Votre commande a été passée avec succès !');
        return $this->redirectToRoute('panier_show');
    }
}
