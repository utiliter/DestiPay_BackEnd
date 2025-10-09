<?php
namespace App\Core;
use Exception;


class FileManager
{

   public function getContent($path)
   {
      if (!file_exists($path)) {
         throw new Exception("File '$path' does not exist.");
      }


      $content = file_get_contents($path);

      if ($content === false) {
         throw new Exception("Could't open file '$path'");
      }

      return $content;
   }


   public function unlink($dirPath)
   {


      $this->removeFile($dirPath);


   }

   public function getPhpContent($path)
   {
      if (!file_exists($path)) {
         throw new Exception("File '$path' does not exist.");
      }

      if (strtolower(substr($path, -4)) !== ".php") {
         throw new Exception("File '$path' is not PHP file.");
      }

      return require $path;
   }


   public function putPhpConent($path, $data)
   {
      file_put_contents($path, "<?php \n return " . var_export($data, true) . ";");
   }


   public function removeFile($filePath, $dirPath = null)
   {

      if (file_exists($filePath) && is_file($filePath)) {
         // $this->opacheInvalidate($filePath, true);
         $result = unlink($filePath);
      }

      return $result;
   }


   // private function opacheInvalidate($filepath, $force = false)
// {

   //     if (!function_exists("opache_invalidate")) {
   //         return;
   //     }

   //     try {

   //         opache_invalidate($filepath, $force);

   //     } catch (Exception $e) {

   //         dd($e->getMessage());
   //     }

   // }
   public function isFile($path)
   {
      return is_file($path);
   }


   /**
    * Check whether a file or directory exists.
    */
   public function exists(string $path)
   {
      return file_exists($path);

   }


}
?>