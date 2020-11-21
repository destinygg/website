<?php
namespace Destiny\Common\Utils;

abstract class Compare {
    /**
     * Compares two arrays by first converting them into JSON strings. Meant
     * for use as a callback in `array_udiff` when the arguments are
     * multidimensional arrays.
     */
    static public function json_compare($a, $b) {
        $json_a = json_encode($a);
        $json_b = json_encode($b);

        if ($json_a < $json_b) {
            return -1;
        } elseif ($json_a > $json_b) {
            return 1;
        } else {
            return 0;
        }
    }
}