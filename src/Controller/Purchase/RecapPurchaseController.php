<?php

namespace App\Controller\Purchase;

use App\Entity\ContentListProduct;
use App\Entity\ListProduct;
use App\Entity\Purchase;
use App\Form\PurchaseType;
use App\Services\Cart\HandleCart;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route; 
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils; 
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class RecapPurchaseController extends AbstractController
{
    #[Route('/boutique/commande/recap', name: 'boutique_commande_recap')]
    public function recap(HandleCart $handleCart, Request $request, EntityManagerInterface $em, AuthenticationUtils $authenticationUtils)
    {
        $detailPanier = $handleCart->detailPanier();

        $purchase = new Purchase();

        $form = $this->createForm(PurchaseType::class, $purchase);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->getUser()) {
                // If user is not logged in, redirect to login form
                $request->getSession()->set('_security.main.target_path', 'boutique_commande_recap');
                return $this->redirectToRoute('app_login');
            }

            $purchase->setUser($this->getUser());

            $em->persist($purchase);

            $listProduct = new ListProduct();

            $listProduct->setPurchase($purchase);

            $em->persist($listProduct);

            foreach ($detailPanier as $item) {
                $contentList = new ContentListProduct();

                $contentList->setProduct($item->getProduct());
                $contentList->setQuantity($item->getQty());
                $contentList->setListProduct($listProduct);

                $em->persist($contentList);
            }

            $em->flush();

            return $this->redirectToRoute("boutique_stripe_session");
        }

        $total = $handleCart->getTotalPanier();

        // Get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        return $this->render("customer/purchase/recap.html.twig", [
            'form' => $form->createView(),
            'detailProducts' => $detailPanier,
            'totalPrixPanier' => $total,
            'error' => $error, // Pass any authentication error to the template
        ]);
    }
}
