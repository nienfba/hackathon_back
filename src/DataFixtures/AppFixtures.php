<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

use App\Entity\Member;
use App\Entity\Category;
use App\Entity\Info;
use App\Entity\Message;
use App\Entity\Media;

use App\Service\Upload;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Faker;

class AppFixtures extends Fixture
{
    public function __construct(Upload $upload, ContainerInterface $container)
    {
        $this->upload = $upload;
    }


    private function cleanUpload ()
    {
        // EFFACER LES FICHIERS
        // FIXME
        $baseAssets = "/home/ubuntu/workspace/myprovence/public/assets";
        $tabFolder = [ "upload", "hd-upload", "mini-upload" ];
        foreach($tabFolder as $folder) {
            shell_exec("rm -rf $baseAssets/$folder/*");
        }
        
    }
    
    public function load(ObjectManager $manager)
    {
        
        // LANCER EN LIGNE DE COMMANDE        
        // php bin/console doctrine:fixtures:load

        $this->cleanUpload();

        // https://github.com/fzaninotto/Faker
        $faker          = Faker\Factory::create();
        $tabMember      = [];
        $tabInfo        = [];
        $tabCategory    = [];
        $tabMessage     = [];
        
        // MEMBER
        $nbMember = 50;
        for($m=0; $m<$nbMember; $m++) {
            $member         = new Member;
            $role           = "ROLE_MEMBER";

            $username = str_replace(".", "", $faker->userName);
            $member->setUsername($username);
            $member->setEmail("$username@mars13.fr");
            $member->setPasswordHash("$username@c4m");
            $member->setRole($role);

            $manager->persist($member);

            $tabMember[] = $member;
        }
        // ON A BESOIN DES id DES MEMBERS POUR CREER LEURS DOSSIERS UPLOAD/...
        $manager->flush();

        // CATEGORY
        $tabCatName     = [
            "Musique"           => "musique", 
            "Restauration"      => "restauration", 
            "Logement"          => "logement", 
            "Divertissement"    => "divertissement", 
            "Sport"             => "sport", 
            "Culture"           => "culture", 
            "Promenade"         => "promenade", 
            "Shopping"          => "shopping",
            ];
        foreach($tabCatName as $catName => $urlName) {
            $category = new Category;
            $category->setName($catName);
            $category->setUrlName($urlName);
            $category->setDescription($faker->paragraph());
            $tabCategory[] = $category;
            
            $manager->persist($category);
        }
        $nbCat          = count($tabCategory);

        $tabIcon = array_values([
                        'question'          => 'question',
                        'child'             => 'child',
                        'cocktail'          => 'cocktail',
                        'eye'               => 'eye',
                        'thumbs-up'         => 'thumbs-up',
                        'umbrella-beach'    => 'umbrella-beach',
                        'swimmer'           => 'swimmer',
                        'futbol'            => 'futbol',
                        'fish'              => 'fish',
                        'kiwi-bird'         => 'kiwi-bird',
                        'smile'             => 'smile',
                        'camera'            => 'camera',
            ]);
        $nbIcon     = count($tabIcon);

        $tabContenu = glob(__DIR__."/contenu/*");
        $nbContenu  = count($tabContenu);
        
        $nbInfo     = 10;
        for($i=0; $i < $nbInfo; $i++) {
            $secondsBefore = $nbInfo * ($i - $nbInfo) + mt_rand(0, $nbInfo);
            $timeBefore = new \DateTime("$secondsBefore seconds");
            $curMember = $tabMember[mt_rand(0, $nbMember-1)];
            
            $info   = new Info;
            
            $contenu    = $tabContenu[($i % $nbContenu)];
            
            if(!is_file("$contenu/title.txt")) 
                file_put_contents("$contenu/title.txt", $faker->sentence());
            if(!is_file("$contenu/description.txt")) 
                file_put_contents("$contenu/description.txt", $faker->paragraph());
            
            
            $tabImage   = glob("$contenu/*.jpg");
            $imagePath  = $tabImage[0] ?? "";
            if (is_file($imagePath)) {
                $imageDir = dirname($imagePath);
                $media  = new Media;
                $file   = new File($imagePath);
                $media->setFile($file);
                $info->addMedium($media);

                $filename =  md5(uniqid());  //génération d'un nom de fichier hashé
                $userDir = "/" . $curMember->getId();
                // création des 3 repertoires d'upload des médias
                $this->upload->directory($userDir);

                // KEEP THE IMAGE FOR NEXT FIXTURE...
                copy($imagePath, "$imageDir/$filename.jpg");
                $this->upload->upload($filename, $userDir, $media);

                $manager->persist($media);
            }
            
            $title       = file_get_contents("$contenu/title.txt");
            $description = file_get_contents("$contenu/description.txt");
            
            $info->setTitle($title);
            $info->setDescription($description);
            $info->setMember($curMember);
            $info->setLatitude($faker->latitude(43, 44.3));
            $info->setLongitude($faker->longitude(4, 6.8));
            $info->setPublicationDate($timeBefore);
            
            // icon
            $info->setIcon($tabIcon[mt_rand(0, $nbIcon-1)]);
            
            // categories
            foreach($tabCategory as $cat) {
                if (mt_rand(0, 100) < (100/$nbCat)) {
                    $info->addCategory($cat);
                }                    
            }
            
            $manager->persist($info);

            $tabInfo[] = $info;
        }
        
        
        // MESSAGES
        $nbMessage = 50 * $nbMember;
        for ($m=0; $m < $nbMessage; $m++) {
            $message    = new Message;
            $author     = $tabMember[mt_rand(0, $nbMember-1)];
            $message->setAuthor($author);

            $toInfoId = null;
            $toInfo     = $tabInfo[mt_rand(0, 2 * $nbInfo)] ?? null;
            if ($toInfo != null) {
                $message->setInfo($toInfo);

                $toInfoId   = $toInfo->getId();
                $to         = $toInfo->getMember();
            }
            else {
                $to         = $tabMember[mt_rand(0, $nbMember-1)];
            }
            
            $username   = $to->getUsername();
            $message->setContent("@$username ".$faker->paragraph());
            
            $message->setStatus("public");
            $message->setIp($faker->ipv4);

            $secondsBefore = $nbMessage * ($m - $nbMessage) + mt_rand(0, $nbMessage);
            $timeBefore = new \DateTime("$secondsBefore seconds");
            $message->setPublicationDate($timeBefore);
            
            
            
            //$manager->persist($message);
            $tabMessage[] = $message;
        }
        
        // php bin/console doctrine:fixtures:load

        // MEMBER 1
        $email          = "member1@mars13.fr";        
        $username       = "member1";
        $password       = "member1";
        $role           = "ROLE_MEMBER";
        
        $member         = new Member;
        $passwordHash   = password_hash($password, PASSWORD_BCRYPT);

        $member->setUsername($username);
        $member->setEmail($email);
        $member->setPassword($passwordHash);
        $member->setRole($role);
        
        $manager->persist($member);

        // MEMBER 2
        $email          = "member2@mars13.fr";        
        $username       = "member2";
        $password       = "member2";
        $role           = "ROLE_MEMBER";
        
        $member         = new Member;
        $passwordHash   = password_hash($password, PASSWORD_BCRYPT);

        $member->setUsername($username);
        $member->setEmail($email);
        $member->setPassword($passwordHash);
        $member->setRole($role);
        
        $manager->persist($member);

        // VISITEUR
        $email          = "visiteur@mars13.fr";        
        $username       = "visiteur";
        $password       = "visiteur@c4m";
        $role           = "ROLE_MEMBER";
        
        $member         = new Member;
        $passwordHash   = password_hash($password, PASSWORD_BCRYPT);

        $member->setUsername($username);
        $member->setEmail($email);
        $member->setPassword($passwordHash);
        $member->setRole($role);
        
        $manager->persist($member);
        
        // ADMIN 1
        $email          = "admin1@mars13.fr";        
        $username       = "admin1";
        $password       = "admin1";
        $role           = "ROLE_ADMIN";
        
        $member         = new Member;
        $passwordHash   = password_hash($password, PASSWORD_BCRYPT);

        $member->setUsername($username);
        $member->setEmail($email);
        $member->setPassword($passwordHash);
        $member->setRole($role);
        
        $manager->persist($member);

        // ADMIN 2
        $email          = "admin2@mars13.fr";        
        $username       = "admin2";
        $password       = "admin2";
        $role           = "ROLE_ADMIN";
        
        $member         = new Member;
        $passwordHash   = password_hash($password, PASSWORD_BCRYPT);

        $member->setUsername($username);
        $member->setEmail($email);
        $member->setPassword($passwordHash);
        $member->setRole($role);
        
        $manager->persist($member);

        // INACTIVE 1
        $email          = "inactive1@mars13.fr";        
        $username       = "inactive1";
        $password       = "inactive1";
        $role           = "ROLE_INACTIVE";
        
        $member         = new Member;
        $passwordHash   = password_hash($password, PASSWORD_BCRYPT);

        $member->setUsername($username);
        $member->setEmail($email);
        $member->setPassword($passwordHash);
        $member->setRole($role);
        
        $manager->persist($member);
        
        
        
        
        $manager->flush();
    }
}