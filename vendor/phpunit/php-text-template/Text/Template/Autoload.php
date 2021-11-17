<?php
spl_autoload_register(
  function ($class) {
      static $classes = NULL;
      static $path = NULL;
      if ($classes === NULL) {
          $classes = array(
            'text_template' => '/Template.php'
          );
          $path = dirname(dirname(__FILE__));
      }
      $cn = strtolower($class);
      if (isset($classes[$cn])) {
          require $path . $classes[$cn];
      }
  }
);
