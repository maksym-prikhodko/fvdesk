<?
if(isset($_FILES['upload'])){
   ------ Process your file upload code -------
        $filen = $_FILES['upload']['tmp_name']; 
        $con_images = "uploaded/".$_FILES['upload']['name'];
        move_uploaded_file($filen, $con_images );
       $url = "http:
   $funcNum = $_GET['CKEditorFuncNum'] ;
   $CKEditor = $_GET['CKEditor'] ;
   $langCode = $_GET['langCode'] ;
   $message = '';
   echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($funcNum, '$url', '$message');</script>";
}
?>
