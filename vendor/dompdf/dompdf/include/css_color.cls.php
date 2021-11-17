<?php
class CSS_Color {
  static $cssColorNames = array(
    "aliceblue" => "F0F8FF",
    "antiquewhite" => "FAEBD7",
    "aqua" => "00FFFF",
    "aquamarine" => "7FFFD4",
    "azure" => "F0FFFF",
    "beige" => "F5F5DC",
    "bisque" => "FFE4C4",
    "black" => "000000",
    "blanchedalmond" => "FFEBCD",
    "blue" => "0000FF",
    "blueviolet" => "8A2BE2",
    "brown" => "A52A2A",
    "burlywood" => "DEB887",
    "cadetblue" => "5F9EA0",
    "chartreuse" => "7FFF00",
    "chocolate" => "D2691E",
    "coral" => "FF7F50",
    "cornflowerblue" => "6495ED",
    "cornsilk" => "FFF8DC",
    "crimson" => "DC143C",
    "cyan" => "00FFFF",
    "darkblue" => "00008B",
    "darkcyan" => "008B8B",
    "darkgoldenrod" => "B8860B",
    "darkgray" => "A9A9A9",
    "darkgreen" => "006400",
    "darkgrey" => "A9A9A9",
    "darkkhaki" => "BDB76B",
    "darkmagenta" => "8B008B",
    "darkolivegreen" => "556B2F",
    "darkorange" => "FF8C00",
    "darkorchid" => "9932CC",
    "darkred" => "8B0000",
    "darksalmon" => "E9967A",
    "darkseagreen" => "8FBC8F",
    "darkslateblue" => "483D8B",
    "darkslategray" => "2F4F4F",
    "darkslategrey" => "2F4F4F",
    "darkturquoise" => "00CED1",
    "darkviolet" => "9400D3",
    "deeppink" => "FF1493",
    "deepskyblue" => "00BFFF",
    "dimgray" => "696969",
    "dimgrey" => "696969",
    "dodgerblue" => "1E90FF",
    "firebrick" => "B22222",
    "floralwhite" => "FFFAF0",
    "forestgreen" => "228B22",
    "fuchsia" => "FF00FF",
    "gainsboro" => "DCDCDC",
    "ghostwhite" => "F8F8FF",
    "gold" => "FFD700",
    "goldenrod" => "DAA520",
    "gray" => "808080",
    "green" => "008000",
    "greenyellow" => "ADFF2F",
    "grey" => "808080",
    "honeydew" => "F0FFF0",
    "hotpink" => "FF69B4",
    "indianred" => "CD5C5C",
    "indigo" => "4B0082",
    "ivory" => "FFFFF0",
    "khaki" => "F0E68C",
    "lavender" => "E6E6FA",
    "lavenderblush" => "FFF0F5",
    "lawngreen" => "7CFC00",
    "lemonchiffon" => "FFFACD",
    "lightblue" => "ADD8E6",
    "lightcoral" => "F08080",
    "lightcyan" => "E0FFFF",
    "lightgoldenrodyellow" => "FAFAD2",
    "lightgray" => "D3D3D3",
    "lightgreen" => "90EE90",
    "lightgrey" => "D3D3D3",
    "lightpink" => "FFB6C1",
    "lightsalmon" => "FFA07A",
    "lightseagreen" => "20B2AA",
    "lightskyblue" => "87CEFA",
    "lightslategray" => "778899",
    "lightslategrey" => "778899",
    "lightsteelblue" => "B0C4DE",
    "lightyellow" => "FFFFE0",
    "lime" => "00FF00",
    "limegreen" => "32CD32",
    "linen" => "FAF0E6",
    "magenta" => "FF00FF",
    "maroon" => "800000",
    "mediumaquamarine" => "66CDAA",
    "mediumblue" => "0000CD",
    "mediumorchid" => "BA55D3",
    "mediumpurple" => "9370DB",
    "mediumseagreen" => "3CB371",
    "mediumslateblue" => "7B68EE",
    "mediumspringgreen" => "00FA9A",
    "mediumturquoise" => "48D1CC",
    "mediumvioletred" => "C71585",
    "midnightblue" => "191970",
    "mintcream" => "F5FFFA",
    "mistyrose" => "FFE4E1",
    "moccasin" => "FFE4B5",
    "navajowhite" => "FFDEAD",
    "navy" => "000080",
    "oldlace" => "FDF5E6",
    "olive" => "808000",
    "olivedrab" => "6B8E23",
    "orange" => "FFA500",
    "orangered" => "FF4500",
    "orchid" => "DA70D6",
    "palegoldenrod" => "EEE8AA",
    "palegreen" => "98FB98",
    "paleturquoise" => "AFEEEE",
    "palevioletred" => "DB7093",
    "papayawhip" => "FFEFD5",
    "peachpuff" => "FFDAB9",
    "peru" => "CD853F",
    "pink" => "FFC0CB",
    "plum" => "DDA0DD",
    "powderblue" => "B0E0E6",
    "purple" => "800080",
    "red" => "FF0000",
    "rosybrown" => "BC8F8F",
    "royalblue" => "4169E1",
    "saddlebrown" => "8B4513",
    "salmon" => "FA8072",
    "sandybrown" => "F4A460",
    "seagreen" => "2E8B57",
    "seashell" => "FFF5EE",
    "sienna" => "A0522D",
    "silver" => "C0C0C0",
    "skyblue" => "87CEEB",
    "slateblue" => "6A5ACD",
    "slategray" => "708090",
    "slategrey" => "708090",
    "snow" => "FFFAFA",
    "springgreen" => "00FF7F",
    "steelblue" => "4682B4",
    "tan" => "D2B48C",
    "teal" => "008080",
    "thistle" => "D8BFD8",
    "tomato" => "FF6347",
    "turquoise" => "40E0D0",
    "violet" => "EE82EE",
    "wheat" => "F5DEB3",
    "white" => "FFFFFF",
    "whitesmoke" => "F5F5F5",
    "yellow" => "FFFF00",
    "yellowgreen" => "9ACD32",
  );
  static function parse($color) {
    if ( is_array($color) ) {
      return $color;
    }
    static $cache = array();
    $color = strtolower($color);
    if ( isset($cache[$color]) ) {
      return $cache[$color];
    }
    if ( in_array($color, array("transparent", "inherit")) ) {
      return $cache[$color] = $color;
    }
    if ( isset(self::$cssColorNames[$color]) ) {
      return $cache[$color] = self::getArray(self::$cssColorNames[$color]);
    }
    $length = mb_strlen($color);
    if ( $length == 4 && $color[0] === "#" ) {
      return $cache[$color] = self::getArray($color[1].$color[1].$color[2].$color[2].$color[3].$color[3]);
    }
    else if ( $length == 7 && $color[0] === "#" ) {
      return $cache[$color] = self::getArray(mb_substr($color, 1, 6));
    }
    else if ( mb_strpos($color, "rgb") !== false ) {
      $i = mb_strpos($color, "(");
      $j = mb_strpos($color, ")");
      if ( $i === false || $j === false ) {
        return null;
      }
      $triplet = explode(",", mb_substr($color, $i+1, $j-$i-1));
      $alpha = 1;
      if ( count( $triplet ) == 4 ) {
        $alpha = (float) ( trim( array_pop( $triplet ) ) );
        if ( $alpha > 1 || $alpha < 0 ) {
          $alpha = 1;
        }
      }
      if ( count($triplet) != 3 ) {
        return null;
      }
      foreach (array_keys($triplet) as $c) {
        $triplet[$c] = trim($triplet[$c]);
        if ( $triplet[$c][mb_strlen($triplet[$c]) - 1] === "%" ) {
          $triplet[$c] = round($triplet[$c] * 2.55);
        }
      }
      return $cache[$color] = self::getArray(vsprintf("%02X%02X%02X", $triplet));
    }
    else if ( mb_strpos($color, "cmyk") !== false ) {
      $i = mb_strpos($color, "(");
      $j = mb_strpos($color, ")");
      if ( $i === false || $j === false ) {
        return null;
      }
      $values = explode(",", mb_substr($color, $i+1, $j-$i-1));
      if ( count($values) != 4 ) {
        return null;
      }
      foreach ($values as &$c) {
        $c = floatval(trim($c));
        if ($c > 1.0) $c = 1.0;
        if ($c < 0.0) $c = 0.0;
      }
      return $cache[$color] = self::getArray($values);
    }
    return null;
  }
  static function getArray($color) {
    $c = array(null, null, null, null, "hex" => null);
    if (is_array($color)) {
      $c = $color;
      $c["c"] = $c[0];
      $c["m"] = $c[1];
      $c["y"] = $c[2];
      $c["k"] = $c[3];
      $c["hex"] = "cmyk($c[0],$c[1],$c[2],$c[3])";
    }
    else {
      $c[0] = hexdec(mb_substr($color, 0, 2)) / 0xff;
      $c[1] = hexdec(mb_substr($color, 2, 2)) / 0xff;
      $c[2] = hexdec(mb_substr($color, 4, 2)) / 0xff;
      $c["r"] = $c[0];
      $c["g"] = $c[1];
      $c["b"] = $c[2];
      $c["hex"] = "#$color";
    }
    return $c;
  }
}