<?php
use Intervention\Image\ImageManager as Manager;
class ImageManager
{
    public static function delete($type, $imageTag)
    {
        $directory = __DIR__ . env('IMAGE_DIR_2', '/../../../public/images');
        if(is_dir("$directory/$type/$imageTag") && !empty($imageTag)){
            array_map('unlink', glob("$directory/$type/$imageTag/*.*"));
           //rmdir("$directory/$type/$imageTag");
        }
    }

    public static function createImage($type, $image)
    {
        $directory = __DIR__ . env('IMAGE_DIR_2', '/../../../public/images');
        $filename = random_string_generator();
        $fullDir = "$directory/$type/$filename";
        

        if(!is_dir($fullDir)){
            mkdir($fullDir, 755, true);
        }

        $manager = new Manager(array('driver' => 'imagick'));

        $image = $manager->make($image)->resize(600, 600)->encode('jpg', 70);
        file_put_contents("$fullDir/lg_$filename.jpg", $image);

        $image = $manager->make($image)->resize(200, 200)->encode('jpg', 70);
        file_put_contents("$fullDir/thumb_$filename.jpg", $image);


        return $filename;
    }
}