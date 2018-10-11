<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 03/10/2018
 * Time: 08:45
 */

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Upload
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param $userDir
     */
    public function directory( $userDir)
    {
        $fileSystem = new Filesystem();
        $fileSystem->mkdir($this->container->getParameter('upload_directory') . $userDir);
        $fileSystem->mkdir($this->container->getParameter('mini_upload_directory') . $userDir);
        $fileSystem->mkdir($this->container->getParameter('hd_upload_directory') . $userDir);

        return;
    }

    /**
     * @param $filename
     * @param $userDir
     * @param $file
     */
    public function upload( $filename, $userDir, $file)
    {
        $path=$userDir."/";
        // Sauvegarde de l'image l'original dans le repertoire upload
        $file->getFile()->move($this->container->getParameter('upload_directory').$path, $filename.".jpg");
        $file->setUrl("upload" . $path . $filename.".jpg");

        $this->image_fix_orientation($this->container->getParameter('upload_directory').$path.$filename.".jpg");
        // Appel de la fonction resizePicture() pour créer une image en vignette et une en HD
        $this->resizePicture($path,$filename,'mini_upload_directory',$x=300,$y=200);
        $this->resizePicture($path,$filename,'hd_upload_directory',$x=1080,$y=720);
    }

    /**
     * @param $filename
     */
    public function image_fix_orientation( $filename) {

        $exif = exif_read_data($filename);
        if (!empty($exif['Orientation'])) {
            $image = imagecreatefromjpeg($filename);
            switch ($exif['Orientation']) {
                case 3:
                    $image = imagerotate($image, 180, 0);
                    break;

                case 6:
                    $image = imagerotate($image, -90, 0);
                    break;

                case 8:
                    $image = imagerotate($image, 90, 0);
                    break;
            }

            imagejpeg($image,$filename);
        }
    }

    /**
     * @param $path
     * @param $filename
     * @param $url
     * @param $x
     * @param $y
     */
    private function resizePicture( $path, $filename, $url, $x, $y)
    {
        // Définition de la largeur et de la hauteur maximale
        $width = $x;
        $height = $y;

        // Content type
        header('Content-Type: image/jpeg');
        // Cacul des nouvelles dimensions
        list($width_orig, $height_orig) = getimagesize($this->container->getParameter('upload_directory').$path.$filename.".jpg");

        $ratio_orig = $width_orig/$height_orig;

        if ($width/$height > $ratio_orig) {
            $width = $height*$ratio_orig;
        } else {
            $height = $width/$ratio_orig;
        }

// Redimensionnement
        $image_p = imagecreatetruecolor($width, $height);
        $image = imagecreatefromjpeg($this->container->getParameter('upload_directory').$path.$filename.".jpg");
        imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);

// Affichage
        imagejpeg($image_p, $this->container->getParameter($url).$path .$filename. ".jpg", 100);
        return ;
    }




}