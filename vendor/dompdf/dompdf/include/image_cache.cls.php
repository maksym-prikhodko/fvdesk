<?php
class Image_Cache {
  static protected $_cache = array();
  public static $broken_image;
  static function resolve_url($url, $protocol, $host, $base_path, DOMPDF $dompdf) {
    $parsed_url = explode_url($url);
    $message = null;
    $remote = ($protocol && $protocol !== "file:
    $data_uri = strpos($parsed_url['protocol'], "data:") === 0;
    $full_url = null;
    $enable_remote = $dompdf->get_option("enable_remote");
    try {
      if ( !$enable_remote && $remote && !$data_uri ) {
        throw new DOMPDF_Image_Exception("DOMPDF_ENABLE_REMOTE is set to FALSE");
      } 
      else if ( $enable_remote && $remote || $data_uri ) {
        $full_url = build_url($protocol, $host, $base_path, $url);
        if ( isset(self::$_cache[$full_url]) ) {
          $resolved_url = self::$_cache[$full_url];
        }
        else {
          $tmp_dir = $dompdf->get_option("temp_dir");
          $resolved_url = tempnam($tmp_dir, "ca_dompdf_img_");
          $image = "";
          if ($data_uri) {
            if ($parsed_data_uri = parse_data_uri($url)) {
              $image = $parsed_data_uri['data'];
            }
          }
          else {
            set_error_handler("record_warnings");
            $image = file_get_contents($full_url);
            restore_error_handler();
          }
          if ( strlen($image) == 0 ) {
            $msg = ($data_uri ? "Data-URI could not be parsed" : "Image not found");
            throw new DOMPDF_Image_Exception($msg);
          }
          else {
            file_put_contents($resolved_url, $image);
          }
        }
      }
      else {
        $resolved_url = build_url($protocol, $host, $base_path, $url);
      }
      if ( !is_readable($resolved_url) || !filesize($resolved_url) ) {
        throw new DOMPDF_Image_Exception("Image not readable or empty");
      }
      else {
        list($width, $height, $type) = dompdf_getimagesize($resolved_url);
        if ( $width && $height && in_array($type, array(IMAGETYPE_GIF, IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_BMP)) ) {
          if ( $enable_remote && $remote || $data_uri ) {
            self::$_cache[$full_url] = $resolved_url;
          }
        }
        else {
          throw new DOMPDF_Image_Exception("Image type unknown");
        }
      }
    }
    catch(DOMPDF_Image_Exception $e) {
      $resolved_url = self::$broken_image;
      $type = IMAGETYPE_PNG;
      $message = $e->getMessage()." \n $url";
    }
    return array($resolved_url, $type, $message);
  }
  static function clear() {
    if ( empty(self::$_cache) || DEBUGKEEPTEMP ) return;
    foreach ( self::$_cache as $file ) {
      if (DEBUGPNG) print "[clear unlink $file]";
      unlink($file);
    }
    self::$_cache = array();
  }
  static function detect_type($file) {
    list(, , $type) = dompdf_getimagesize($file);
    return $type;
  }
  static function type_to_ext($type) {
    $image_types = array(
      IMAGETYPE_GIF  => "gif",
      IMAGETYPE_PNG  => "png",
      IMAGETYPE_JPEG => "jpeg",
      IMAGETYPE_BMP  => "bmp",
    );
    return (isset($image_types[$type]) ? $image_types[$type] : null);
  }
  static function is_broken($url) {
    return $url === self::$broken_image;
  }
}
Image_Cache::$broken_image = DOMPDF_LIB_DIR . "/res/broken_image.png";
