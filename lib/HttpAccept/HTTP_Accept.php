<?php
/**
 * HTTP_Accept class for dealing with the HTTP 'Accept' header
 * 
 * PHP versions 4 and 5
 *
 * LICENSE:  This source file is subject to the MIT License.
 * The full text of this license is available at the following URL:
 * http://www.opensource.org/licenses/mit-license.php
 *
 * @category    HTTP
 * @package     HTTP_Accept
 * @author      Kevin Locke <kwl7@cornell.edu>
 * @copyright   2007 Kevin Locke
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 * @version     SVN: $Id: HTTP_Accept.php 22 2007-10-06 18:46:45Z kevin $
 * @link        http://pear.php.net/package/HTTP_Accept
 */

/**
 * HTTP_Accept class for dealing with the HTTP 'Accept' header
 *
 * This class is intended to be used to parse the HTTP Accept header into
 * usable information and provide a simple API for dealing with that
 * information.
 *
 * The parsing of this class is designed to follow RFC 2616 to the letter,
 * any deviations from that standard are bugs and should be reported to the
 * maintainer.
 *
 * Often the class will be used very simply as
 * <code>
 * <?php
 * $accept = new HTTP_Accept($_SERVER['HTTP_ACCEPT']);
 * if ($accept->getQuality("image/png") > $accept->getQuality("image/jpeg"))
 *     // Send PNG image
 * else
 *     // Send JPEG image
 * ?>
 * </code>
 *
 * However, for browsers which do not accurately describe their preferences,
 * it may be necessary to check if a MIME Type is explicitly listed in their
 * Accept header, in addition to being preferred to another type
 *
 * <code>
 * <?php
 *  if ($accept->isMatchExact("application/xhtml+xml"))
 *      // Client specifically asked for this type at some quality level
 * ?>
 * </code>
 *
 * 
 * @category    HTTP
 * @package     HTTP_Accept
 * @access      public
 * @link        http://pear.php.net/package/HTTP_Accept
 */
class HTTP_Accept
{
    /**
     * Array of types and their associated parameters, extensions, and quality
     * factors, as represented in the Accept: header.
     * Indexed by [type][subtype][index],
     * and contains 'PARAMS', 'QUALITY', and 'EXTENSIONS' keys for the
     * parameter set, quality factor, and extensions set respectively.
     * Note:  Since type, subtype, and parameters are case-insensitive
     * (RFC 2045 5.1) they are stored as lower-case.
     * 
     * @var     array
     * @access  private
     */
    var $acceptedtypes = array();

    /**
     * Regular expression to match a token, as defined in RFC 2045
     * 
     * @var     string
     * @access  private
     */
    var $_matchtoken = '(?:[^[:cntrl:]()<>@,;:\\\\"\/\[\]?={} \t]+)';

    /**
     * Regular expression to match a quoted string, as defined in RFC 2045
     * 
     * @var     string
     * @access  private
     */
    var $_matchqstring = '(?:"[^\\\\"]*(?:\\\\.[^\\\\"]*)*")';

    /**
     * Constructs a new HTTP_Accept object
     *
     * Initializes the HTTP_Accept class with a given accept string
     * or creates a new (empty) HTTP_Accept object if no string is given
     *
     * Note:  The behavior is a little strange here to accomodate
     * missing headers (to be interpreted as accept all) as well as
     * new empty objects which should accept nothing.  This means that
     * HTTP_Accept("") will be different than HTTP_Accept()
     * 
     * @access  public
     * @return  object  HTTP_Accept
     * @param   string  $acceptstring The value of an Accept: header
     *                                Will often be $_SERVER['HTTP_ACCEPT']
     *                                Note:  If get_magic_quotes_gpc is on,
     *                                run stripslashes() on the string first
     */
    function HTTP_Accept()
    {
        if (func_num_args() == 0) {
            // User wishes to create empty HTTP_Accept object
            $this->acceptedtypes = array(
                    '*' => array(
                        '*' => array (
                            0 => array(
                                       'PARAMS' => array(),
                                       'QUALITY' => 0,
                                       'EXTENSIONS' => array()
                                )
                            )
                        )
                    );
            return;
        }

        $acceptstring = trim(func_get_arg(0));
        if (empty($acceptstring)) {
            // Accept header empty or not sent, interpret as "*/*"
            $this->acceptedtypes = array(
                    '*' => array(
                        '*' => array (
                            0 => array(
                                       'PARAMS' => array(),
                                       'QUALITY' => 1,
                                       'EXTENSIONS' => array()
                                )
                            )
                        )
                    );
            return;
        }

        $matches = preg_match_all(
                    '/\s*('.$this->_matchtoken.')\/' .          // typegroup/
                    '('.$this->_matchtoken.')' .                // subtype
                    '((?:\s*;\s*'.$this->_matchtoken.'\s*' .    // parameter
                    '(?:=\s*' .                                 // optional =value
                    '(?:'.$this->_matchqstring.'|'.$this->_matchtoken.'))?)*)/',        // value
                                  $acceptstring, $acceptedtypes,
                                  PREG_SET_ORDER);
         
        if ($matches == 0) {
            // Malformed Accept header
            $this->acceptedtypes = array(
                    '*' => array(
                        '*' => array (
                            0 => array(
                                       'PARAMS' => array(),
                                       'QUALITY' => 1,
                                       'EXTENSIONS' => array()
                                )
                            )
                        )
                    );
            return;
        }

        foreach ($acceptedtypes as $accepted) {
            $typefamily = strtolower($accepted[1]);
            $subtype = strtolower($accepted[2]);

            // */subtype is invalid according to grammar in section 14.1
            // so we ignore it
            if ($typefamily == '*' && $subtype != '*')
                continue;

            // Parse all arguments of the form "key=value"
            $matches = preg_match_all('/;\s*('.$this->_matchtoken.')\s*' .
                                      '(?:=\s*' .
                                      '('.$this->_matchqstring.'|'.
                                      $this->_matchtoken.'))?/',
                                      $accepted[3], $args,
                                      PREG_SET_ORDER);

            $params = array();
            $quality = -1;
            $extensions = array();
            foreach ($args as $arg) {
                array_shift($arg);
                if (!empty($arg[1])) {
                    // Strip quotes (Note:  Can't use trim() in case "text\"")
                    $len = strlen($arg[1]);
                    if ($arg[1][0] == '"' && $arg[1][$len-1] == '"'
                        && $len > 1) {
                        $arg[1] = substr($arg[1], 1, $len-2);
                        $arg[1] = stripslashes($arg[1]);
                    }
                } else if (!isset($arg[1])) {
                    $arg[1] = null;
                }

                // Everything before q=# is a parameter, after is an extension
                if ($quality >= 0) {
                    $extensions[$arg[0]] = $arg[1];
                } else if ($arg[0] == 'q') {
                    $quality = (float)$arg[1];

                    if ($quality < 0)
                        $quality = 0;
                    else if ($quality > 1)
                        $quality = 1;
                } else {
                    $arg[0] = strtolower($arg[0]);
                    // Values required for parameters,
                    // assume empty-string for missing values
                    if (isset($arg[1]))
                               $params[$arg[0]] = $arg[1];
                    else
                        $params[$arg[0]] = "";
                }
            }

            if ($quality < 0)
                $quality = 1;
            else if ($quality == 0)
                continue;

            if (!isset($this->acceptedtypes[$typefamily]))
                $this->acceptedtypes[$typefamily] = array();
            if (!isset($this->acceptedtypes[$typefamily][$subtype]))
                $this->acceptedtypes[$typefamily][$subtype] = array();

            $this->acceptedtypes[$typefamily][$subtype][] =
                       array('PARAMS' => $params,
                             'QUALITY' => $quality,
                             'EXTENSIONS' => $extensions);
        }

        if (!isset($this->acceptedtypes['*']))
            $this->acceptedtypes['*'] = array();
        if (!isset($this->acceptedtypes['*']['*']))
            $this->acceptedtypes['*']['*'] = array(
                    0 => array(
                               'PARAMS' => array(),
                               'QUALITY' => 0,
                               'EXTENSIONS' => array()
                        )
                    );
    }
    
    /**
     * Gets the accepted quality factor for a given MIME Type
     *
     * Note:  If there are multiple best matches
     * (e.g. "text/html;level=4;charset=utf-8" matching both "text/html;level=4"
     * and "text/html;charset=utf-8"), it returns the lowest quality factor as
     * a conservative estimate.  Further, if the ambiguity is between parameters
     * and extensions (e.g. "text/html;level=4;q=1;ext=foo" matching both
     * "text/html;level=4" and "text/html;q=1;ext=foo") the parameters take
     * precidence.
     *
     * Usage Note:  If the quality factor for all supported media types is 0,
     * RFC 2616 specifies that applications SHOULD send an HTTP 406 (not
     * acceptable) response.
     *
     * @access  public
     * @return  double  the quality value for the given MIME Type
     *                  Quality values are in the range [0,1] where 0 means
     *                  "not accepted" and 1 is "perfect quality".
     * @param   string  $mimetype   The MIME Type to query ("text/html")
     * @param   array   $params     Parameters of Type to query ([level => 4])
     * @param   array   $extensions Extension parameters to query
     */
    function getQuality($mimetype, $params = array(), $extensions = array())
    {
        $type = explode("/", $mimetype);
        $supertype = strtolower($type[0]);
        $subtype = strtolower($type[1]);

        if ($params == null)
            $params = array();
        if ($extensions == null)
            $extensions = array();

        if (empty($this->acceptedtypes[$supertype])) {
            if ($supertype == '*')
                return 0;
            else
                return $this->getQuality("*/*", $params, $extensions);
        }

        if (empty($this->acceptedtypes[$supertype][$subtype])) {
            if ($subtype == '*')
                return $this->getQuality("*/*", $params, $extensions);
            else
                return $this->getQuality("$supertype/*", $params, $extensions);
        }

        $params = array_change_key_case($params, CASE_LOWER);

        $matches = $this->_findBestMatchIndices($supertype, $subtype,
                                         $params, $extensions);

        if (count($matches) == 0) {
            if ($subtype != '*')
                return $this->getQuality("$supertype/*", $params, $extensions);
            else if ($supertype != '*')
                return $this->getQuality("*/*", $params, $extensions);
            else
                return 0;
        }

        $minquality = 1;
        foreach ($matches as $match)
            if ($this->acceptedtypes[$supertype][$subtype][$match]['QUALITY'] < $minquality)
                $minquality = $this->acceptedtypes[$supertype][$subtype][$match]['QUALITY'];

        return $minquality;
    }

    /**
     * Determines if there is an exact match for the specified MIME Type
     *
     * @access  public
     * @return  boolean true if there is an exact match to the given
     *                  values, false otherwise.
     * @param   string  $mimetype   The MIME Type to query (e.g. "text/html")
     * @param   array   $params     Parameters of Type to query (e.g. [level => 4])
     * @param   array   $extensions Extension parameters to query
     */
    function isMatchExact($mimetype, $params = array(), $extensions = array())
    {
        $type = explode("/", $mimetype);
        $supertype = strtolower($type[0]);
        $subtype = strtolower($type[1]);

        if ($params == null)
            $params = array();
        if ($extensions == null)
            $extensions = array();

        return $this->_findExactMatchIndex($supertype, $subtype,
                                           $params, $extensions) >= 0;
    }

    /**
     * Gets a list of all MIME Types explicitly accepted, sorted by quality
     *
     * @access  public
     * @return  array   list of MIME Types explicitly accepted, sorted
     *                  in decreasing order of quality factor
     */
    function getTypes()
    {
        $qvalues = array();
        $types = array();
        foreach ($this->acceptedtypes as $typefamily => $subtypes) {
            if ($typefamily == '*')
                continue;

            foreach ($subtypes as $subtype => $variants) {
                if ($subtype == '*')
                    continue;

                $maxquality = 0;
                foreach ($variants as $variant)
                    if ($variant['QUALITY'] > $maxquality)
                        $maxquality = $variant['QUALITY'];

                if ($maxquality > 0) {
                    $qvalues[] = $maxquality;
                    $types[] = $typefamily.'/'.$subtype;
                }
            }
        }

        array_multisort($qvalues, SORT_DESC, SORT_NUMERIC,
                        $types, SORT_DESC, SORT_STRING);

        return $types;
    }

    /**
     * Gets the parameter sets for a given mime type, sorted by quality.
     * Only parameter sets where the extensions set is empty will be returned.
     *
     * @access  public
     * @return  array   list of sets of name=>value parameter pairs
     *                  in decreasing order of quality factor
     * @param   string  $mimetype   The MIME Type to query ("text/html")
     */
    function getParameterSets($mimetype)
    {
        $type = explode("/", $mimetype);
        $supertype = strtolower($type[0]);
        $subtype = strtolower($type[1]);

        if (!isset($this->acceptedtypes[$supertype])
            || !isset($this->acceptedtypes[$supertype][$subtype]))
            return array();

        $qvalues = array();
        $paramsets = array();
        foreach ($this->acceptedtypes[$supertype][$subtype] as $acceptedtype) {
            if (count($acceptedtype['EXTENSIONS']) == 0) {
                $qvalues[] = $acceptedtype['QUALITY'];
                $paramsets[] = $acceptedtype['PARAMS'];
            }
        }

        array_multisort($qvalues, SORT_DESC, SORT_NUMERIC,
                        $paramsets, SORT_DESC, SORT_STRING);

        return $paramsets;
    }

    /**
     * Gets the extension sets for a given mime type, sorted by quality.
     * Only extension sets where the parameter set is empty will be returned.
     *
     * @access  public
     * @return  array   list of sets of name=>value extension pairs
     *                  in decreasing order of quality factor
     * @param   string  $mimetype   The MIME Type to query ("text/html")
     */
    function getExtensionSets($mimetype)
    {
        $type = explode("/", $mimetype);
        $supertype = strtolower($type[0]);
        $subtype = strtolower($type[1]);

        if (!isset($this->acceptedtypes[$supertype])
            || !isset($this->acceptedtypes[$supertype][$subtype]))
            return array();

        $qvalues = array();
        $extensionsets = array();
        foreach ($this->acceptedtypes[$supertype][$subtype] as $acceptedtype) {
            if (count($acceptedtype['PARAMS']) == 0) {
                $qvalues[] = $acceptedtype['QUALITY'];
                $extensionsets[] = $acceptedtype['EXTENSIONS'];
            }
        }

        array_multisort($qvalues, SORT_DESC, SORT_NUMERIC,
                        $extensionsets, SORT_DESC, SORT_STRING);

        return $extensionsets;
    }

    /**
     * Adds a type to the set of accepted types
     *
     * @access  public
     * @param   string  $mimetype   The MIME Type to add (e.g. "text/html")
     * @param   double  $quality    The quality value for the given MIME Type
     *                              Quality values are in the range [0,1] where
     *                              0 means "not accepted" and 1 is
     *                              "perfect quality".
     * @param   array   $params     Parameters of the type to add (e.g. [level => 4])
     * @param   array   $extensions Extension parameters of the type to add
     */
    function addType($mimetype, $quality = 1,
                     $params = array(), $extensions = array())
    {
        $type = explode("/", $mimetype);
        $supertype = strtolower($type[0]);
        $subtype = strtolower($type[1]);

        if ($params == null)
            $params = array();
        if ($extensions == null)
            $extensions = array();

        $index = $this->_findExactMatchIndex($supertype, $subtype, $params, $extensions);

        if ($index >= 0) {
            $this->acceptedtypes[$supertype][$subtype][$index]['QUALITY'] = $quality;
        } else {
            if (!isset($this->acceptedtypes[$supertype]))
                $this->acceptedtypes[$supertype] = array();
            if (!isset($this->acceptedtypes[$supertype][$subtype]))
                $this->acceptedtypes[$supertype][$subtype] = array();

            $this->acceptedtypes[$supertype][$subtype][] =
                array('PARAMS' => $params,
                      'QUALITY' => $quality,
                      'EXTENSIONS' => $extensions);
        }
    }

    /**
     * Removes a type from the set of accepted types
     *
     * @access  public
     * @param   string  $mimetype   The MIME Type to remove (e.g. "text/html")
     * @param   array   $params     Parameters of the type to remove (e.g. [level => 4])
     * @param   array   $extensions Extension parameters of the type to remove
     */
    function removeType($mimetype, $params = array(), $extensions = array())
    {
        $type = explode("/", $mimetype);
        $supertype = strtolower($type[0]);
        $subtype = strtolower($type[1]);

        if ($params == null)
            $params = array();
        if ($extensions == null)
            $extensions = array();

        $index = $this->_findExactMatchIndex($supertype, $subtype, $params, $extensions);

        if ($index >= 0) {
            $this->acceptedtypes[$supertype][$subtype] =
                array_merge(array_slice($this->acceptedtypes[$supertype][$subtype],
                                        0, $index),
                            array_slice($this->acceptedtypes[$supertype][$subtype],
                                        $index+1));
        }
    }

    /**
     * Gets a string representation suitable for use in an HTTP Accept header
     *
     * @access  public
     * @return  string  a string representation of this object
     */
    function __toString()
    {
        $accepted = array();
        $qvalues = array();
        foreach ($this->acceptedtypes as $supertype => $subtypes) {
            foreach ($subtypes as $subtype => $entries) {
                foreach ($entries as $entry) {
                    $accepted[] = array('TYPE' => "$supertype/$subtype",
                            'QUALITY' => $entry['QUALITY'],
                            'PARAMS' => $entry['PARAMS'],
                            'EXTENSIONS' => $entry['EXTENSIONS']);
                    $qvalues[] = $entry['QUALITY'];
                }
            }
        }

        array_multisort($qvalues, SORT_DESC, SORT_NUMERIC,
                        $accepted);

        $str = "";
        foreach ($accepted as $accept) {
            // Skip the catchall value if it is 0, since this is implied
            if ($accept['TYPE'] == '*/*' &&
                $accept['QUALITY'] == 0 &&
                count($accept['PARAMS']) == 0 &&
                count($accept['EXTENSIONS'] == 0))
                continue;

            $str = $str.$accept['TYPE'].';';

            foreach ($accept['PARAMS'] as $param => $value) {
                if (preg_match('/^'.$this->_matchtoken.'$/', $value))
                    $str = $str.$param.'='.$value.';';
                else
                    $str = $str.$param.'="'.addcslashes($value,'"\\').'";';
            }

            if ($accept['QUALITY'] < 1 || !empty($accept['EXTENSIONS']))
                $str = $str.'q='.$accept['QUALITY'].';';

            foreach ($accept['EXTENSIONS'] as $extension => $value) {
                if (preg_match('/^'.$this->_matchtoken.'$/', $value))
                    $str = $str.$extension.'='.$value.';';
                else
                    $str = $str.$extension.'="'.addcslashes($value,'"\\').'";';
            }

            $str[strlen($str)-1] = ',';
        }

        return rtrim($str, ',');
    }

    /**
     * Finds the index of an exact match for the specified MIME Type
     *
     * @access  private
     * @return  int     the index of an exact match if found,
     *                  -1 otherwise
     * @param   string  $supertype  The general MIME Type to find (e.g. "text")
     * @param   string  $subtype    The MIME subtype to find (e.g. "html")
     * @param   array   $params     Parameters of Type to find ([level => 4])
     * @param   array   $extensions Extension parameters to find
     */
    function _findExactMatchIndex($supertype, $subtype, $params, $extensions)
    {
        if (empty($this->acceptedtypes[$supertype])
            || empty($this->acceptedtypes[$supertype][$subtype]))
            return -1;

        $params = array_change_key_case($params, CASE_LOWER);

        $parammatches = array();
        foreach ($this->acceptedtypes[$supertype][$subtype] as $index => $typematch)
            if ($typematch['PARAMS'] == $params
                && $typematch['EXTENSIONS'] == $extensions)
                return $index;

        return -1;
    }

    /**
     * Finds the indices of the best matches for the specified MIME Type
     *
     * A "match" in this context is an exact type match and no extraneous
     * matches for parameters or extensions (so the best match for
     * "text/html;level=4" may be "text/html" but not the other way around).
     *
     * "Best" is interpreted as the entries that match the most
     * parameters and extensions (the sum of the number of matches)
     *
     * @access  private
     * @return  array   an array of the indices of the best matches
     *                  (empty if no matches)
     * @param   string  $supertype  The general MIME Type to find (e.g. "text")
     * @param   string  $subtype    The MIME subtype to find (e.g. "html")
     * @param   array   $params     Parameters of Type to find ([level => 4])
     * @param   array   $extensions Extension parameters to find
     */
    function _findBestMatchIndices($supertype, $subtype, $params, $extensions)
    {
        $bestmatches = array();
        $bestlength = 0;

        if (empty($this->acceptedtypes[$supertype])
            || empty($this->acceptedtypes[$supertype][$subtype]))
            return $bestmatches;

        foreach ($this->acceptedtypes[$supertype][$subtype] as $index => $typematch) {
            if (count(array_diff_assoc($typematch['PARAMS'], $params)) == 0
                && count(array_diff_assoc($typematch['EXTENSIONS'],
                                          $extensions)) == 0) {
                $length = count($typematch['PARAMS'])
                          + count($typematch['EXTENSIONS']);

                if ($length > $bestlength) {
                    $bestmatches = array($index);
                    $bestlength = $length;
                } else if ($length == $bestlength) {
                    $bestmatches[] = $index;
                }
            }
        }

        return $bestmatches;
    }
}

// vim: set ts=4 sts=4 sw=4 et:
?>
