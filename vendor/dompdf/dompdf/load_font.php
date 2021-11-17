#!/usr/bin/php
<?php
require_once "dompdf_config.inc.php";
function usage() {
  echo <<<EOD
Usage: {$_SERVER["argv"][0]} font_family [n_file [b_file] [i_file] [bi_file]]
font_family:      the name of the font, e.g. Verdana, 'Times New Roman',
                  monospace, sans-serif. If it equals to "system_fonts", 
                  all the system fonts will be installed.
n_file:           the .ttf or .otf file for the normal, non-bold, non-italic
                  face of the font.
{b|i|bi}_file:    the files for each of the respective (bold, italic,
                  bold-italic) faces.
If the optional b|i|bi files are not specified, load_font.php will search
the directory containing normal font file (n_file) for additional files that
it thinks might be the correct ones (e.g. that end in _Bold or b or B).  If
it finds the files they will also be processed.  All files will be
automatically copied to the DOMPDF font directory, and afm files will be
generated using php-font-lib (https:
Examples:
./load_font.php silkscreen /usr/share/fonts/truetype/slkscr.ttf
./load_font.php 'Times New Roman' /mnt/c_drive/WINDOWS/Fonts/times.ttf
EOD;
exit;
}
if ( $_SERVER["argc"] < 3 && @$_SERVER["argv"][1] != "system_fonts" ) {
  usage();
}
function install_font_family($fontname, $normal, $bold = null, $italic = null, $bold_italic = null) {
  Font_Metrics::init();
  if ( !is_readable($normal) )
    throw new DOMPDF_Exception("Unable to read '$normal'.");
  $dir = dirname($normal);
  $basename = basename($normal);
  $last_dot = strrpos($basename, '.');
  if ($last_dot !== false) {
    $file = substr($basename, 0, $last_dot);
    $ext = strtolower(substr($basename, $last_dot));
  } else {
    $file = $basename;
    $ext = '';
  }
  if ( !in_array($ext, array(".ttf", ".otf")) ) {
    throw new DOMPDF_Exception("Unable to process fonts of type '$ext'.");
  }
  $path = "$dir/$file";
  $patterns = array(
    "bold"        => array("_Bold", "b", "B", "bd", "BD"),
    "italic"      => array("_Italic", "i", "I"),
    "bold_italic" => array("_Bold_Italic", "bi", "BI", "ib", "IB"),
  );
  foreach ($patterns as $type => $_patterns) {
    if ( !isset($$type) || !is_readable($$type) ) {
      foreach($_patterns as $_pattern) {
        if ( is_readable("$path$_pattern$ext") ) {
          $$type = "$path$_pattern$ext";
          break;
        }
      }
      if ( is_null($$type) )
        echo ("Unable to find $type face file.\n");
    }
  }
  $fonts = compact("normal", "bold", "italic", "bold_italic");
  $entry = array();
  foreach ($fonts as $var => $src) {
    if ( is_null($src) ) {
      $entry[$var] = DOMPDF_FONT_DIR . mb_substr(basename($normal), 0, -4);
      continue;
    }
    if ( !is_readable($src) )
      throw new DOMPDF_Exception("Requested font '$src' is not readable");
    $dest = DOMPDF_FONT_DIR . basename($src);
    if ( !is_writeable(dirname($dest)) )
      throw new DOMPDF_Exception("Unable to write to destination '$dest'.");
    echo "Copying $src to $dest...\n";
    if ( !copy($src, $dest) )
      throw new DOMPDF_Exception("Unable to copy '$src' to '$dest'");
    $entry_name = mb_substr($dest, 0, -4);
    echo "Generating Adobe Font Metrics for $entry_name...\n";
    $font_obj = Font::load($dest);
    $font_obj->saveAdobeFontMetrics("$entry_name.ufm");
    $entry[$var] = $entry_name;
  }
  Font_Metrics::set_font_family($fontname, $entry);
  Font_Metrics::save_font_families();
}
if ( $_SERVER["argv"][1] === "system_fonts" ) {
  $fonts = Font_Metrics::get_system_fonts();
  foreach ( $fonts as $family => $files ) {
    echo " >> Installing '$family'... \n";
    if ( !isset($files["normal"]) ) {
      echo "No 'normal' style font file\n";
    }
    else {
      install_font_family( $family, @$files["normal"], @$files["bold"], @$files["italic"], @$files["bold_italic"]);
      echo "Done !\n";
    }
    echo "\n";
  }
}
else {
  call_user_func_array("install_font_family", array_slice($_SERVER["argv"], 1));
}
