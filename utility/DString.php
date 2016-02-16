<?php
//
// Copyright (c) 2008-2016 Decibel Technology Limited.
//
namespace app\decibel\utility;

/**
 * Provides static functions to assist in the handling of strings.
 *
 * @author        Timothy de Paris
 */
class DString
{
    /**
     * Lorem ipsum string used for generating random content.
     *
     * @var        string
     */
    const LOREM_IPSUM = 'Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam eaque ipsa, quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt, explicabo. Nemo enim ipsam voluptatem, quia voluptas sit, aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos, qui ratione voluptatem sequi nesciunt, neque porro quisquam est, qui dolorem ipsum, quia dolor sit amet, consectetur, adipiscing velit, sed quia non numquam do eius modi tempora incididunt, ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit, qui in ea voluptate velit esse, quam nihil molestiae consequatur, vel illum, qui dolorem eum fugiat, quo voluptas nulla pariatur? At vero eos et accusamus et iusto odio dignissimos ducimus, qui blanditiis praesentium voluptatum deleniti atque corrupti, quos dolores et quas molestias excepturi sint, obcaecati cupiditate non provident, similique sunt in culpa, qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio, cumque nihil impedit, quo minus id, quod maxime placeat, facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet, ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat.';
    /**
     * Cached array of name => decimal entities used for converting html
     * encoded strings for RSS and other XML tasks.
     *
     * @var        array
     */
    private static $htmlEntities = array();

    /**
     * Prepares a string to be used as content within an XML document.
     *
     * @param    string $string The string to escape.
     *
     * @return    string
     */
    public static function escapeForXml($string)
    {
        $escaped = htmlentities($string, ENT_QUOTES, 'utf-8', false);

        return DString::namedEntitiesToDecimal($escaped);
    }

    /**
     * Converts any named entities in a string to their decimal equivalents.
     * Should be used when sending encoded html content to RSS feeds.
     *
     * @param    string $string The string.
     *
     * @return    string
     */
    public static function namedEntitiesToDecimal($string)
    {
        // Create entity array if not yet done.
        if (empty(DString::$htmlEntities)) {
            // Entities in translation table.
            foreach (get_html_translation_table(HTML_ENTITIES, ENT_QUOTES) as $key => $value) {
                DString::$htmlEntities["/$value/"] = sprintf('&#%s;', ord($key));
            }
            // Additional entities.
            DString::$htmlEntities = array_merge(DString::$htmlEntities, array(
                '/&apos;/u'   => '&#39;', '/&minus;/u' => '&#45;', '/&circ;/u' => '&#94;', '/&tilde;/u' => '&#126;', '/&Scaron;/u' => '&#138;',
                '/&lsaquo;/u' => '&#139;', '/&OElig;/u' => '&#140;', '/&lsquo;/u' => '&#145;', '/&rsquo;/u' => '&#146;', '/&ldquo;/u' => '&#147;',
                '/&rdquo;/u'  => '&#148;', '/&bull;/u' => '&#149;', '/&ndash;/u' => '&#150;', '/&mdash;/u' => '&#151;', '/&tilde;/u' => '&#152;',
                '/&trade;/u'  => '&#153;', '/&scaron;/u' => '&#154;', '/&rsaquo;/u' => '&#155;', '/&oelig;/u' => '&#156;', '/&Yuml;/u' => '&#159;',
                '/&yuml;/u'   => '&#255;', '/&OElig;/u' => '&#338;', '/&oelig;/u' => '&#339;', '/&Scaron;/u' => '&#352;', '/&scaron;/u' => '&#353;',
                '/&Yuml;/u'   => '&#376;', '/&fnof;/u' => '&#402;', '/&circ;/u' => '&#710;', '/&tilde;/u' => '&#732;', '/&Alpha;/u' => '&#913;',
                '/&Beta;/u'   => '&#914;', '/&Gamma;/u' => '&#915;', '/&Delta;/u' => '&#916;', '/&Epsilon;/u' => '&#917;', '/&Zeta;/u' => '&#918;',
                '/&Eta;/u'    => '&#919;', '/&Theta;/u' => '&#920;', '/&Iota;/u' => '&#921;', '/&Kappa;/u' => '&#922;', '/&Lambda;/u' => '&#923;',
                '/&Mu;/u'     => '&#924;', '/&Nu;/u' => '&#925;', '/&Xi;/u' => '&#926;', '/&Omicron;/u' => '&#927;', '/&Pi;/u' => '&#928;',
                '/&Rho;/u'    => '&#929;', '/&Sigma;/u' => '&#931;', '/&Tau;/u' => '&#932;', '/&Upsilon;/u' => '&#933;', '/&Phi;/u' => '&#934;',
                '/&Chi;/u'    => '&#935;', '/&Psi;/u' => '&#936;', '/&Omega;/u' => '&#937;', '/&alpha;/u' => '&#945;', '/&beta;/u' => '&#946;',
                '/&gamma;/u'  => '&#947;', '/&delta;/u' => '&#948;', '/&epsilon;/u' => '&#949;', '/&zeta;/u' => '&#950;', '/&eta;/u' => '&#951;',
                '/&theta;/u'  => '&#952;', '/&iota;/u' => '&#953;', '/&kappa;/u' => '&#954;', '/&lambda;/u' => '&#955;', '/&mu;/u' => '&#956;',
                '/&nu;/u'     => '&#957;', '/&xi;/u' => '&#958;', '/&omicron;/u' => '&#959;', '/&pi;/u' => '&#960;', '/&rho;/u' => '&#961;',
                '/&sigmaf;/u' => '&#962;', '/&sigma;/u' => '&#963;', '/&tau;/u' => '&#964;', '/&upsilon;/u' => '&#965;', '/&phi;/u' => '&#966;',
                '/&chi;/u'    => '&#967;', '/&psi;/u' => '&#968;', '/&omega;/u' => '&#969;', '/&thetasym;/u' => '&#977;', '/&upsih;/u' => '&#978;',
                '/&piv;/u'    => '&#982;', '/&ensp;/u' => '&#8194;', '/&emsp;/u' => '&#8195;', '/&thinsp;/u' => '&#8201;', '/&zwnj;/u' => '&#8204;',
                '/&zwj;/u'    => '&#8205;', '/&lrm;/u' => '&#8206;', '/&rlm;/u' => '&#8207;', '/&ndash;/u' => '&#8211;', '/&mdash;/u' => '&#8212;',
                '/&lsquo;/u'  => '&#8216;', '/&rsquo;/u' => '&#8217;', '/&sbquo;/u' => '&#8218;', '/&ldquo;/u' => '&#8220;', '/&rdquo;/u' => '&#8221;',
                '/&bdquo;/u'  => '&#8222;', '/&dagger;/u' => '&#8224;', '/&Dagger;/u' => '&#8225;', '/&bull;/u' => '&#8226;', '/&hellip;/u' => '&#8230;',
                '/&permil;/u' => '&#8240;', '/&prime;/u' => '&#8242;', '/&Prime;/u' => '&#8243;', '/&lsaquo;/u' => '&#8249;', '/&rsaquo;/u' => '&#8250;',
                '/&oline;/u'  => '&#8254;', '/&frasl;/u' => '&#8260;', '/&euro;/u' => '&#8364;', '/&image;/u' => '&#8465;', '/&weierp;/u' => '&#8472;',
                '/&real;/u'   => '&#8476;', '/&trade;/u' => '&#8482;', '/&alefsym;/u' => '&#8501;', '/&larr;/u' => '&#8592;', '/&uarr;/u' => '&#8593;',
                '/&rarr;/u'   => '&#8594;', '/&darr;/u' => '&#8595;', '/&harr;/u' => '&#8596;', '/&crarr;/u' => '&#8629;', '/&lArr;/u' => '&#8656;',
                '/&uArr;/u'   => '&#8657;', '/&rArr;/u' => '&#8658;', '/&dArr;/u' => '&#8659;', '/&hArr;/u' => '&#8660;', '/&forall;/u' => '&#8704;',
                '/&part;/u'   => '&#8706;', '/&exist;/u' => '&#8707;', '/&empty;/u' => '&#8709;', '/&nabla;/u' => '&#8711;', '/&isin;/u' => '&#8712;',
                '/&notin;/u'  => '&#8713;', '/&ni;/u' => '&#8715;', '/&prod;/u' => '&#8719;', '/&sum;/u' => '&#8721;', '/&minus;/u' => '&#8722;',
                '/&lowast;/u' => '&#8727;', '/&radic;/u' => '&#8730;', '/&prop;/u' => '&#8733;', '/&infin;/u' => '&#8734;', '/&ang;/u' => '&#8736;',
                '/&and;/u'    => '&#8743;', '/&or;/u' => '&#8744;', '/&cap;/u' => '&#8745;', '/&cup;/u' => '&#8746;', '/&int;/u' => '&#8747;',
                '/&there4;/u' => '&#8756;', '/&sim;/u' => '&#8764;', '/&cong;/u' => '&#8773;', '/&asymp;/u' => '&#8776;', '/&ne;/u' => '&#8800;',
                '/&equiv;/u'  => '&#8801;', '/&le;/u' => '&#8804;', '/&ge;/u' => '&#8805;', '/&sub;/u' => '&#8834;', '/&sup;/u' => '&#8835;',
                '/&nsub;/u'   => '&#8836;', '/&sube;/u' => '&#8838;', '/&supe;/u' => '&#8839;', '/&oplus;/u' => '&#8853;', '/&otimes;/u' => '&#8855;',
                '/&perp;/u'   => '&#8869;', '/&sdot;/u' => '&#8901;', '/&lceil;/u' => '&#8968;', '/&rceil;/u' => '&#8969;', '/&lfloor;/u' => '&#8970;',
                '/&rfloor;/u' => '&#8971;', '/&lang;/u' => '&#9001;', '/&rang;/u' => '&#9002;', '/&loz;/u' => '&#9674;', '/&spades;/u' => '&#9824;',
                '/&clubs;/u'  => '&#9827;', '/&hearts;/u' => '&#9829;', '/&diams;/u' => '&#9830;',
            ));
        }

        return preg_replace(
            array_keys(DString::$htmlEntities),
            array_values(DString::$htmlEntities),
            $string
        );
    }

    /**
     * Returns a random string for testing purposes.
     *
     * The string is based on 'lorem ipsum'.
     *
     * @param    int $charLength The character length of the random string.
     *
     * @return    string
     */
    public static function getRandomString($charLength)
    {
        $startPosition = strpos(DString::LOREM_IPSUM, ' ', rand(0, 500));

        return ucfirst(substr(DString::LOREM_IPSUM, $startPosition, $charLength));
    }

    /**
     * Converts a qualified name to a path.
     *
     * @param    string $qualifiedName    Qualified name to convert.
     * @param    string $prefix           Directory prefix that will replace the
     *                                    'app' component of the qualified name.
     *
     * @return    string
     */
    public static function qualifiedNameToPath($qualifiedName, $prefix = '')
    {
        return str_replace(
            array('app\\', '\\'),
            array($prefix, '/'),
            $qualifiedName
        );
    }

    /**
     * Adds slashes to all single quotes in the given string.
     *
     * @param    string $input The string to encode.
     *
     * @return    string
     */
    public static function singleQuoteSlashes($input)
    {
        return preg_replace("/(')/", "\\'", $input);
    }

    /**
     * Inserts a space before each capital letters in the given string.
     *
     * @note
     * This function works for any language able to be represented in UTF-8.
     *
     * @param    string $text The string to spacify.
     *
     * @return    string
     */
    public static function spacify($text)
    {
        return preg_replace('/(?<!^)(?<![\s\p{Lu}])[\p{Lu}]/u', ' $0', $text);
    }

    /**
     * Strips all HTML from a string.
     *
     * @param    string $string   The HTML encoded string.
     * @param    array  $options  Options determining how the string will be stripped.
     *                            Available options:
     *                            - <code>keepLinks</code> - URLs in anchor tags will be left in the string, surrounded
     *                            with parenthesis. Default: false
     *                            - <code>keepLineBreaks</code> - Paragraph and break tags will be converted to hard
     *                            line breaks. Default: false
     *                            - <code>keepImageDescriptions</code> - Image alt text will be substituted for images.
     *                            Default: true
     *                            - <code>discardH1Values</code> - Array of H1 values that will be removed from the
     *                            html. Values are case insensitive. If true is provided, all H1 tags will be removed.
     *                            Default: Blank array
     *                            - <code>useEllipsis</code> - Whether ellipsis will be used to denote sections from
     *                            the html. Default: true
     *
     * @return    The string without HTML.
     */
    public static function stripHtml($string, array $options = array())
    {
        set_default($options['keepLinks'], false);
        set_default($options['keepLineBreaks'], false);
        set_default($options['keepImageDescriptions'], true);
        set_default($options['discardH1Values'], array());
        set_default($options['useEllipsis'], false);
        // Replace images with alt text.
        if ($options['keepImageDescriptions']) {
            $string = preg_replace('/<img\s+.*?alt="([^"]*)"[^>]*>/is', '\1', $string);
        }
        // Remove formatting tags.
        $formattingTags = 'b|strong|i|em|center';
        $string = preg_replace('/<(?:\/)?(?:' . $formattingTags . ')(?:.|\s)*?>/is', '', $string);
        // Discard unwanted heading elements.
        if ($options['discardH1Values'] === true) {
            $string = preg_replace('/<h1>[^<]*<\/h1>/Uis', '', $string);
        } else {
            foreach ($options['discardH1Values'] as $h1Value) {
                $string = preg_replace('/<h1>' . $h1Value . '<\/h1>/Uis', '', $string);
            }
        }
        // Remove comments, head block, script/style blocks, html and body tags.
        $string = preg_replace('/(?:<!--.*-->)|(?:<head[^>]*>.*<\/head>)|(?:<script[^>]*>.*<\/script>)|(?:<style[^>]*>.*<\/style>)|(?:<[\/]?html[^>]*>)|(?:<[\/]?body[^>]*>)/Uis',
                               '', $string);
        // Convert paragraphs and breaks to line breaks.
        if ($options['keepLineBreaks']) {
            $string = preg_replace('/<p[^>]*?>/is', '', $string);
            $string = preg_replace('/<\/p>/is', "\n\n", $string);
            $string = preg_replace('/<[bh]r(\s*\/)?>/is', "\n", $string);
        } else {
            $string = preg_replace('/<[bh]r(\s*\/)?>/is', ' ', $string);
        }
        // Convert links.
        if ($options['keepLinks']) {
            $string = preg_replace('/<a\s+.*?href="([^"]+)"[^>]*>([^<]+)<\/a>/is', '\2 [\1]', $string);
            // Remove mailto: links.
            $string = preg_replace('/\[mailto:[^\]]+]/is', '', $string);
        }
        // Remove unwanted tags, replacing with an ellipsis, space or line break.
        if ($options['keepLineBreaks']) {
            $replace = "\n\n";
        } else {
            if ($options['useEllipsis']) {
                $replace = ' &hellip;';
            } else {
                $replace = ' ';
            }
        }
        $hellipTags = 'h1|h2|h3|h4|h5|h6|td';
        $spaceTags = 'a|caption|img|p|div|span|ol|ul|li|table|tbody|thead|tr|td|th|object|iframe';
        $string = preg_replace('/<(?:' . $hellipTags . '|' . $spaceTags . ')(?:.|\s)*?>/is', ' ', $string);
        $string = preg_replace('/<\/(?:' . $hellipTags . ')(?:.|\s)*?>/is', $replace, $string);
        $string = preg_replace('/<\/(?:' . $spaceTags . ')(?:.|\s)*?>/is', ' ', $string);
        // Convert html entities.
        $string = str_replace('&nbsp;', ' ', $string);
        $string = html_entity_decode($string, ENT_QUOTES, 'UTF-8');
        // Reduce multiple white space to single spaces and clean up line breaks.
        if (!$options['keepLineBreaks']) {
            $string = preg_replace('/\s{2,}|\n/', ' ', $string);
        } else {
            $string = preg_replace('/[\t ]{2,}/', ' ', $string);
            $string = preg_replace('/\n[\t ]+/', '', $string);
        }

        return trim($string);
    }

    /**
     * Converts text to a value suitable for use in a URL.
     *
     * All non-alpha-numeric characters are removed (except for underscores),
     * as are any instances of the word 'and', or 'a'. Spaces will be converted
     * into the value provided for the spacer parameter after being grouped.
     *
     * @note
     * This function works for any language able to be represented in UTF-8.
     *
     * @param    string $text     The text to convert.
     * @param    string $spacer   The text replacement for space characters.
     *                            By default this is a blank string.
     *
     * @return    string    The converted value.
     */
    public static function textToUrl($text, $spacer = '')
    {
        return preg_replace(
            array('/\s+(and|a)\s+/i', '/[^\s\pL\pN_]+/ui', '/\s+/'),
            array(' ', '', $spacer),
            $text
        );
    }

    /**
     * Returns the first specified number of characters from a string.
     *
     * @param    string $string       The string to limit.
     * @param    int    $charLimit    The number of characters to return.
     * @param    string $suffix       If specified, this suffix will be added to the
     *                                shortened string.
     * @param    bool   $clean        If true or not specified, the
     *                                string will be broken at the first non-alphabetic
     *                                and non-numeric character before the limit.
     *
     * @return    string    The character limited string.
     */
    public static function charLimit($string, $charLimit, $suffix = '', $clean = true)
    {
        // Don't do anything if the string is shorter than the limit.
        if (strlen($string) < $charLimit) {
            return $string;
        }
        $matches = array();
        $clean = $clean ? '[^\pL\pN]' : '';
        if (preg_match('/^(.{1,' . $charLimit . '})' . $clean . '/u', $string, $matches)) {
            return $matches[1] . $suffix;
        }

        return '';
    }

    /**
     * Returns the first specified number of words from a string.
     *
     * @param    string $string       The string to limit.
     * @param    int    $wordLimit    The number of words to return.
     * @param    string $suffix       If specified, this suffix will be added to the
     *                                shortened string.
     *
     * @return    string    The word limited string.
     */
    public static function wordLimit($string, $wordLimit, $suffix = '&hellip;')
    {
        // Don't do anything to a blank string or an invalid word limit.
        if (empty($string) || !$wordLimit) {
            return $string;
        }
        // Split the string on word breaks.
        $regSuffix = $suffix === '' ? '' : '|' . $suffix;
        $words = preg_split("/((?:[[:space:]\.,]|&nbsp;{$regSuffix})+)/", $string, -1, PREG_SPLIT_DELIM_CAPTURE);
        $wordLimitWithSpaces = $wordLimit * 2;
        // If there are fewer words than the limit,
        // just return the original string.
        if (count($words) < $wordLimitWithSpaces) {
            return $string;
        }
        // Reduce the split string to the maximum required limit.
        $limitedWords = array_slice($words, 0, $wordLimitWithSpaces - 1);
        // Append the suffix, unless the string contains fewer words than
        // the limit or the last word is the suffix already.
        if (count($limitedWords) === ($wordLimitWithSpaces - 1)
            && $limitedWords[ $wordLimitWithSpaces - 2 ] !== $suffix
        ) {
            $limitedWords[] = $suffix;
        }

        return implode('', $limitedWords);
    }

    /**
     * Strips a specific tag and its contents from the given html
     *
     * @param    string $html
     * @param    mixed  $tags
     *
     * @return    string
     */
    public static function stripTag($html, $tags)
    {
        $tags = (array)$tags;
        foreach ($tags as $tag) {
            $html = preg_replace("/<$tag>.*(<\\/$tag>|$)/U", '', $html);
        }

        return $html;
    }

    /**
     * Join array elements with a string, optionaly including a different
     * string for the final element.
     *
     * @param    array  $pieces       The array of strings to implode.
     * @param    string $glue         The glue used to join pieces together.
     * @param    string $lastGlue     Optional glue used to join the final
     *                                two pieces together.
     *
     * @return    string
     */
    public static function implode(array $pieces, $glue = ', ', $lastGlue = null)
    {
        if ($lastGlue === null) {
            return implode($glue, $pieces);
        }
        switch (count($pieces)) {
            case 0:
                $joined = '';
                break;
            case 1:
                $joined = (string)array_pop($pieces);
                break;
            default:
                $lastItem = (string)array_pop($pieces);
                $joined = implode($glue, $pieces) . $lastGlue . $lastItem;
                break;
        }

        return $joined;
    }
}
