<?php 

namespace App\Controller;

use App\Entity\User;
use App\Form\ContactSupportType;
use App\Form\UploadImageType;
use App\Form\EditAccountPasswordType;
use App\Repository\PurchaseRepository;
use App\Services\Mail\SendPreparedMail;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfileController extends AbstractController
{
    #[Route('/profile/detail', name: 'profile_detail')]
    public function profile()
    {
        return $this->render("customer/profile/detail.html.twig");
    }

    #[Route('/profile/image/edit', name: 'profile_image_edit')]
    public function changePhotoProfile(EntityManagerInterface $em, Request $request,SluggerInterface $slugger)
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(UploadImageType::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            /** @var UploadedFile $file */
            $file = $form->get('image')->getData();

            //Je vais d'abord récupérer le nom : 
            $originalFilename = pathinfo($file->getClientOriginalName(),PATHINFO_FILENAME);

            //Je dois sluggifier le nom pour le rendre safe :
            $safeFileName = $slugger->slug($originalFilename);

            //Je dois changer le nom pour le rendre unique
            $uniqFilename = $safeFileName . '-' .  md5(uniqid()) . '.' . $file->guessExtension();

            //Je prend le file et je l envoie dans sa maison(/public/uploads/images)
            $file->move(
                $this->getParameter('app_images_directory'),
                $uniqFilename
            );

            //J'enregistre le chemin qui me ramene vers ce fichier en bdd
            //Je set le ImagePath (propriété de catégorie)
            $user->setImagePath('/uploads/images/' . $uniqFilename);

            $em->flush();

            $this->addFlash("success","La photo de profil a été mise à jour.");
            return $this->redirectToRoute("profile_detail");
    
        }

        return $this->render("customer/profile/image_update.html.twig",[
            'form' => $form->createView()
        ]);
    }

    #[Route('/profile/purchase/list', name: 'profile_purchase_list')]
    public function purchaselist()
    {
        /** @var User $user */
        $user = $this->getUser();

        $purchases = $user->getPurchases();

        return $this->render("customer/purchase/purchase_list.html.twig",[
            'purchases' => $purchases
        ]);
    }

    #[Route('/profile/purchase/show/{id}', name: 'profile_purchase_show')]
    public function purchaseShow(int $id,PurchaseRepository $purchaseRepository)
    {

        $purchase = $purchaseRepository->find($id);

        return $this->render("customer/purchase/purchase_detail.html.twig",[
            'purchase' => $purchase
        ]);
    }

    #[Route('/profile/edit/password', name: 'profile_edit_password')]
    public function changePassword(Request $request,
                EntityManagerInterface $em,UserPasswordHasherInterface $userPasswordHasher)
    {
           /** @var User $user */
            $user = $this->getUser();

            $formPassword = $this->createForm(EditAccountPasswordType::class);

            $formPassword->handleRequest($request);

            if($formPassword->isSubmitted() && $formPassword->isValid())
            {
               // encode the plain password
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $formPassword->get('plainPassword')->getData()
                    )
                );

                $em->flush();

                $this->addFlash("success","Votre mot de passe a bien été modifié.");
                return $this->redirectToRoute("profile_detail");
            }

            return $this->render("customer/profile/edit_password.html.twig",[
                'form' => $formPassword->createView(),
            ]);
    }

    #[Route('/profile/contactsupport', name: 'profile_contact_support')]
    public function contactSupport(SendPreparedMail $sendPreparedMail,Request $request)
    {
        $form = $this->createForm(ContactSupportType::class);

        $form->handleRequest($request);

        /** @var User $user */
        $user = $this->getUser();

        if($form->isSubmitted() && $form->isValid())
        {
            $contentMessage = $form->get('contentMessage')->getData();
            $subjectMessage = $form->get('subjectMessage')->getData();

            $sendPreparedMail->sendMailToSupport($user->getEmail(),$contentMessage,$subjectMessage);

            $this->addFlash("success","Le mail a bien été envoyé");
            return $this->redirectToRoute("profile_detail");
        }

        return $this->render("customer/profile/contact_support.html.twig",[
            'form' => $form->createView(),
        ]);


    }
}

?>