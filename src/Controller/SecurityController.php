<?php

namespace App\Controller;

use App\Entity\Member;
use App\Repository\MemberRepository;

use App\Form\GetemailType;
use App\Form\InscriptionType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Form\PasswordEditType;
use App\Service\Upload;

use App\Mail\MyMailer;

// Mailer a utiliser dans le .env
// MAILER_URL=gmail://c4mars@gmail.com:weekend2018@localhost?encryption=tls&auth_mode=oauth

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="login")
     */    
    public function login(AuthenticationUtils $authenticationUtils)
    {
        // Obtenir l'erreur de connexion s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // Dernier nom d'utilisateur entré par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
        ));                
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {
        return $this->render('security/logout.html.twig', [
            'controller_name' => 'SecurityController',
        ]);
    }

    /**
     * @Route("/inscription", name="inscription")
     */
    public function inscription(Request $request, ObjectManager $manager, MyMailer $myMailer, Upload $upload)
    {
        $member = new Member();
        $form = $this->createForm(InscriptionType::class, $member);

        $form->handleRequest($request);


        if($form->isSubmitted() && $form->isValid())
        {

            if(!$member->getId()){
                $member->setRegistrationDate(new \DateTime());
            }


            // Génération d'une clé aléatoire
            $cle = md5(microtime(TRUE)*100000);
            
            //Hachage du mot de passe
            $password = $member->getPassword();
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $member->setPassword($passwordHash);

            //Attribution d'un role par temporaire avant validation par mail de l'inscription
            //A PASSER EN "ROLE_INACTIVE" avant mise en prod
            $member->setRole('ROLE_MEMBER');

            //Stockage de la clé en base de données
            $member->setCle($cle);

            //Création des répertoires de stockages des médias
            //$upload->directory($this->getUser()->getId());

            $manager->persist($member);
            $manager->flush();

            //recuperation des infos du formulaire pour construire le lien d'activation
            $username   = $form['username']->getData();
            $email      = $form['email']->getData();
            
            $body       =  $this->renderView('security/registrationmail.html.twig',array(
                'username' => $username, 
                'email' => $email,
                'cle' =>$cle ));
                
            //Construction et Envoi du mail de confirmation d'inscription contenant le lien d'activation
            /*
            $mail = (new \Swift_Message('My Provence'))
            ->setFrom('c4mars@gmail.com')
            ->setTo($email)
            ->setBody($body,'text/html');

            $mailer->send($mail);
            */
            
            $myMailer->sendMail($email, 'My Provence', $body);

            return $this->redirectToRoute('attente-activation');
        }

        return $this->render('security/inscription.html.twig',[
            'form'=>$form->createView(),
        ]);
    }

    /**
     * @Route("/activation", name="activation")
     */
    public function activation(Request $request, MemberRepository $memberRepository, ObjectManager $manager)
    {
        $login = $_GET['log'];
        $cleClient  = $_GET['cle']; //recupere cle fournis ds l'url

        $memberCle = $memberRepository->findOneBy(['cle'=>$cleClient]);

        // Si la clé n'existe pas...
        if ($memberCle == null) {
            return $this->render('security/errorActivation.html.twig');
        }

        $role = $memberCle ->getRole();

        // Si aucun role de defini...
        if ($role == NULL) {
            return $this->render('security/errorActivation.html.twig');
        }

        // Si le role est deja en "member"...
        if ($role == 'ROLE_MEMBER') {
            return $this->render('security/alreadyActivat.html.twig');
        }

        // Si le role a le statut "ROLE_INACTIVE"...
        if ($role == 'ROLE_INACTIVE') {
            $role = $memberCle ->setRole('ROLE_MEMBER');
            
            $manager->flush();

            return $this->render('security/activation.html.twig',['cle' => $cleClient, 'role'=>$role, 'login'=>$login]);
        }

        // Si le role a un statut different de ROLE_INACTIVE ou ROLE_MEMBER...
        if (!in_array($role, ['ROLE_INACTIVE', 'ROLE_MEMBER']))
        {
            return $this->render('security/errorActivation.html.twig');
        }
    }

    /**
     * @Route("/attente-activation", name="attente-activation")
     */
    public function attenteActivation()
    {
        return $this->render('security/attenteActivation.html.twig'); 
    }


    /**
     * @Route("/password-revovery", name="password-revovery")
     */
    public function passwordRevovery(Request $request, ObjectManager $manager, MyMailer $myMailer)
    {
        $member = new Member();
        $form = $this->createForm(GetemailType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $emaildb = $form['email']->getData();
            
            $email = $this->getDoctrine()
                          ->getRepository(Member::class)
                          ->findOneBy(['email'=>$emaildb]);

            if(!$email){
            return $this->render('security/errorEmail.html.twig');
            }

            
            $cle = md5(microtime(TRUE)*100000);
            $email->setCle($cle);
        
            $manager->persist($email);
            $manager->flush();

            //Construction et Envoi du mail contenanle lien de modif du mot de passe
            $emailform = $form['email']->getData();
            
            /*
            // dump($emailform);
            $mail = (new \Swift_Message('My Provence'))
            ->setFrom('c4mars@gmail.com')
            ->setTo($emailform)
            ->setBody($this->renderView('security/recoveryMail.html.twig',array('cle' =>$cle )),'text/html');

            $mailer->send($mail);
            */

            $body = $this->renderView('security/recoveryMail.html.twig',array('cle' =>$cle ));
            $myMailer->sendMail($emailform, 'My Provence', $body);

            return $this->render('security/emailOk.html.twig',['email'=>$emailform]); 

        }
        
        return $this->render('security/passwordRevovery.html.twig',[
            'form'=>$form->createView()
        ]);

    }

        /**
         * @Route("/passwordEdit", name="passwordEdit")
         */
        public function editpassword(Request $request, ObjectManager $manager){

            //On crée le formulaire de changement de mot de passe
            $member = new Member();
            $form = $this->createForm(PasswordEditType::class, $member);
            $form->handleRequest($request);

            $cleClient  = $_GET['cle'];
            $password = $form['password']->getData();

            //trouver le password en base de données grace à la clé client
            $newpassword = $this->getDoctrine()
            ->getRepository(Member::class)
            ->findOneBy(['cle'=>$cleClient]);

            if(!$newpassword){
                return $this->render('security/errorEditPassword.html.twig');
            }

            if($form->isSubmitted() && $form->isValid()){

                //On modifie la clé en base de donnée par du vide se qui rend le lien de modif de mot de passe à usage unique
                $newpassword->setCle('');
                
                $manager->persist($newpassword);
                $manager->flush();

                //On hash le nouveau password (saisi ds le formulaire)
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);

                //On le remplace en base de données
                $newpassword->setPassword($passwordHash);
                
                //Persit et flush
                $em = $this->getDoctrine()->getManager();
                $em->persist($newpassword);
                $em->flush();

                return $this->render('security/sucssessEditPassword.html.twig');
                
            }

        return $this->render('security/passwordEdit.html.twig',[
            'form'=>$form->createView()
        ]);

    }
    
}
