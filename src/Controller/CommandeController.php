<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Repository\ArticleRepository;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CommandeController extends AbstractController
{
    #[Route('/commande', name: 'commande_create', methods: ['POST'])]
    public function createCommande(SessionInterface $session, ArticleRepository $articleRepository, EntityManagerInterface $entityManager, Request $request): Response
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
                $total += $article->getPrix() * $item['quantity'];
            }
        }

        $commande->setTotal($total);
        $commande->setCreatedAt(new \DateTime());
        $entityManager->persist($commande);
        $entityManager->flush();

        $session->remove('panier');

        $this->addFlash('success', 'Votre commande a été passée avec succès!');
        return $this->redirectToRoute('panier_show');
    }

    #[Route('/commande/initier-paiement', name: 'commande_initiate_payment', methods: ['POST'])]
    public function initierPaiement(SessionInterface $session, ArticleRepository $articleRepository, EntityManagerInterface $entityManager): Response
    {
        $panier = $session->get('panier', []);

        if (empty($panier)) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('panier_show');
        }

        $commande = new Commande();
        $commande->setUser($this->getUser());
        $commande->setStatut('pending');
        $total = 0;

        $lineItems = [];

        foreach ($panier as $item) {
            $article = $articleRepository->find($item['article']->getId());
            if ($article) {
                $commande->addArticle($article);
                $total += $article->getPrix() * $item['quantity'];

                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => $article->getTitre(),
                        ],
                        'unit_amount' => $article->getPrix() * 100,
                    ],
                    'quantity' => $item['quantity'],
                ];
            }
        }

        $commande->setTotal($total);
        $commande->setCreatedAt(new \DateTime());
        $entityManager->persist($commande);
        $entityManager->flush();

        Stripe::setApiKey('sk_test_51PL3AmCy78PEFla7afKmrvQvoSxdqD2mCK4PfQ7FwZmvNAqsIKqkSPqRHyZqVqaJxQgqm5mAQFp134Q2RfbxCgVb00Ppf1liFE');

        try {
            $checkoutSession = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => $this->generateUrl('payment_success', ['id' => $commande->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                'cancel_url' => $this->generateUrl('payment_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);

            $commande->setStripeSessionId($checkoutSession->id);
            $entityManager->flush();

            return new JsonResponse(['id' => $checkoutSession->id]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la création de la session de paiement: ' . $e->getMessage());
            return $this->redirectToRoute('panier_show');
        }
    }

    #[Route('/commande/success', name: 'payment_success')]
    public function paymentSuccess(EntityManagerInterface $entityManager, CommandeRepository $commandeRepository, Request $request): Response
    {
        $sessionId = $request->get('session_id');
        $commande = $commandeRepository->findOneBy(['stripeSessionId' => $sessionId]);

        if ($commande) {
            $commande->setStatut('paid');
            $entityManager->flush();
            $this->addFlash('success', 'Votre paiement a été effectué avec succès!');
        } else {
            $this->addFlash('error', 'La commande n\'a pas été trouvée.');
        }

        return $this->redirectToRoute('panier_show');
    }

    #[Route('/commande/cancel', name: 'payment_cancel')]
    public function paymentCancel(): Response
    {
        $this->addFlash('error', 'Le paiement a été annulé.');
        return $this->redirectToRoute('panier_show');
    }
}
