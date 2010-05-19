<?php
/**
 * Validator
 *
 * @author Lars Olesen <lars@legestue.net>
 * @author Sune Jensen <sj@sunet.dk>
 */

require_once 'Validate.php';

/**
 * Basically a collection of validator methods which logs to a logger
 *
 * @author Lars Olesen <lars@legestue.net>
 * @author Sune Jensen <sj@sunet.dk>
 */
class Ilib_Validator {

    /**
     * @var object
     */
    private $error;

    /**
     * @var array options
     */
    private $option;

    /**
     * Constructor
     *
     * @param object $error Error object
     *
     * @return void
     */
    public function __construct($error = NULL, $options = array())
    {
        if ($error != NULL) {
            $this->error = $error;
        } else {
            $this->error = new Ilib_Error;
        }

        $default_options = array('connection_internet' => true);
        $this->option = array_merge($default_options, $options);

    }

    /**
     * Validates an email
     *
     * @param string $email emailen which should be validated
     * @param string $msg error message
     * @param string $param streng indholdende en eller flere af:
     *      "allow_empty": allows the string to be empty
     *
     * @return boolean true or false
     */
    public function isEmail($email, $msg = '', $params = '') {

        $params = $this->parseParams($params, array('allow_empty'));

        if (in_array('allow_empty', $params) AND empty($email)) {
            return true;
        }

        if (!Validate::email($email, $this->option['connection_internet'])) {
            $this->error->set($msg);
            return false;
        }
        return true;
    }

    /**
     * Validates a date
     *
     * @param string $date  date which should be validated
     * @param string $msg   error message
     * @param string $param streng indholdende en eller flere af:
     *      "allow_empty": allows the string to be empty
     *
     * @return boolean true or false
     */
    public function isDate($date, $msg = '', $params = '')
    {

        $params = $this->parseParams($params, array('allow_empty', 'allow_no_year'));

        if (in_array("allow_empty", $params) !== false && empty($date)) {
            return true;
        }

        // Gyldig datoformater
        // d: 01-31, 1-31
        // m: 01-12, 1-12
        // y: 0000-9999, 01-99, 1-99

        // HUSK AT RETTE I BÅDE VALIDATOR OG DATE

        $d = "([0-3]?[0-9])";
        $m = "([0-1]?[0-9])";
        $y = "([0-9][0-9][0-9][0-9]|[0-9]?[0-9])";
        $s = "(-|\.|/| )";

        if (preg_match_all("/^".$d.$s.$m.$s.$y."$/", $date, $parts)) {
            // true
        } elseif (preg_match_all("/^".$d.$s.$m."$/", $date, $parts) && in_array("allow_no_year", $params) !== false) {
            $parts[5] = date("Y");
            // true
        } else {
            $this->error->set($msg);
            return false;
        }

        if (checkdate($parts[3], $parts[1], $parts[5])) {
            return true;
        } else {
            $this->error->set($msg);
            return false;
        }
    }

    /**
     * Validates a time
     *
     * @param string $time  time which should be validated
     * @param string $msg   error message
     * @param string $param streng indholdende en eller flere af:
     *      "allow_empty": allows the string to be empty
     *
     * @return boolean true or false
     */
    public function isTime($time, $msg = '', $params = '')
    {
        $params = $this->parseParams($params, array('allow_empty', 'must_have_second'));

        if (in_array("allow_empty", $params) !== false && empty($time)) {
            return true;
        }

        // Gyldig datoformater
        // t: 00-23, 0-23
        // m: 00-59
        // s: 00-59


        $t = "([0-2]?[0-9])";
        $m = "([0-5][0-9])";
        $s = "([0-5][0-9])";
        $i = "(\:)";

        if (preg_match_all("/^".$t.$i.$m.$i.$s."$/", $time, $parts)) {
            // true

        } elseif (preg_match_all("/^".$t.$i.$m."$/", $time, $parts) && in_array("must_have_second", $params) === false) {

            $parts[5] = '00';
            // true
        } else {
            $this->error->set($msg);
            return(false);
        }

        if (intval($parts[1] > 23)) {
            $this->error->set($msg);
            return false;
        } else {
            return true;
        }
    }

    /**
     * Validates an url
     *
     * @param string $url   url which should be validated
     * @param string $msg   error message
     * @param string $param streng indholdende en eller flere af:
     *      "allow_empty": allows the string to be empty
     *
     * @return boolean
     */
    public function isUrl($url, $msg = '', $params = '')
    {
        $params = $this->parseParams($params, array('allow_empty'));
        if (in_array('allow_empty', $params) AND empty($url)) {
            return true;
        }
        return Validate::uri($url);
    }

    /**
     * Validering af streng
     *
     * @param string $string       strengen der skal valideres
     * @param string $msg          fejlbeskeden
     * @param string $allowed_tags html tags der er tilladte at benytte.
     * @param string $param        streng indholdende en eller flere af:
     *      "allow_empty": tillader den er tom
     *
     * @return boolean
     */
    public function isString($string, $msg = '', $allowed_tags = '', $params = '')
    {
        $params = $this->parseParams($params, array('allow_empty'));
        if (in_array('allow_empty', $params) AND empty($string)) {
            return true;
        } elseif (empty($string)) {
            $this->error->set($msg);
            return false;
        }

        $test_string = strip_tags($string, $allowed_tags);

        if ($test_string != $string) {
            $this->error->set($msg);
            return false;
        }
        return true;
    }

    /**
     * Validates a password
     *
     * @param string  $password   which should be validated
     * @param integer $min_length how short
     * @param integer $max_length how long
     * @param string  $msg        error msg
     * @param string  $param      streng indholdende en eller flere af:
     *      "allow_empty": allows the string to be empty
     *
     * @return boolean true or false
     */
    public function isPassword($password, $min_length, $max_length, $msg = "", $params = "") {

        $params = $this->parseParams($params, array('allow_empty'));
        if (in_array('allow_empty', $params) !== false && empty($password)) {
            return true;
        }

        if (preg_match_all("/^[a-zA-Z0-9]+$/", $password)) {
            if (strlen($password) >= intval($min_length) && strlen($password) <= intval($max_length)) {
                return true;
            }
        }
        $this->error->set($msg);
        return false;
    }

    /**
     * Kontroller om den er numerisk
     *
     * @param string $string strengen der skal valideres
     * @param string $msg    fejlbeskeden
     * @param string $param  streng indholdende en eller flere af:
     *      "allow_empty": tillader større
     *      "greater_than_zero": kun større end nul
     *      "zero_or_greater": 0 eller større
     *	    "integer": tillader kun heltal
     *
     * @return boolean
     */
    public function isNumeric($string, $msg = '', $params = '')
    {
        $params = $this->parseParams($params, array('allow_empty', 'zero_or_greater', 'integer', 'greater_than_zero'));

        $string = str_replace(".", "", $string);
        $string = str_replace(",", ".", $string);

        if (in_array('allow_empty', $params) !== false && empty($string)) {
            return true;
        } elseif (is_numeric($string)) {

            if (in_array('integer', $params) !== false) {
                if (intval($string) != $string) {
                    $this->error->set($msg);
                    return false;
                }
            }

            if (in_array('zero_or_greater', $params) !== false) {
                if ($string >= 0) {
                    return true;
                } else {
                    $this->error->set($msg);
                    return false;
                }
            } elseif (in_array('greater_than_zero', $params) !== false) {
                if ($string > 0) {
                    return true;
                } else {
                    $this->error->set($msg);
                    return false;
                }
            } else {
                return true;
            }
        } else {
            $this->error->set($msg);
            return false;
        }
    }

    /**
     * Kontroller om den er double
     *
     * @param string $number strengen der skal valideres
     * @param string $msg    fejlbeskeden
     * @param string $param  streng indholdende en eller flere af:
     *      "allow_empty": tillader større
     *      "greater_than_zero": kun større end nul
     *      "zero_or_greater": 0 eller større
     *          "integer": tillader kun heltal
     *
     * @return boolean
     */
    public function isDouble($number, $msg = "", $params = "") {
        $params = $this->parseParams($params, array('allow_empty', 'zero_or_greater', 'greater_than_zero'));

        if (in_array('allow_empty', $params) !== false && empty($number)) {
            return true;
        } elseif (preg_match_all("/^-?[0-9]+(\.[0-9]{3})*(,[0-9]{1,2})?$/", $number)) {

            // $^
            $number = str_replace(".", "", $number);
            $number = str_replace(",", ".", $number);
            settype($number, "double");

            if (in_array('zero_or_greater', $params) !== false) {
                if ($number >= 0) {
                    return true;
                } else {
                    $this->error->set($msg);
                    return false;
                }
            } elseif (in_array('greater_than_zero', $params) !== false) {
                if ($number > 0) {
                    return true;
                } else {
                    $this->error->set($msg);
                    return false;
                }
            } else {
                return true;
            }
        } else {
            $this->error->set($msg);
            return false;
        }
    }

    /**
     * Validates as identifier, eg. in an url
     *
     * @param string $string To validate
     * @param string $msg    Error msg
     * @param string $param  Extra parameters
     *
     * @return boolean
     */
    public function isIdentifier($string, $msg, $params = '')
    {
        $params = $this->parseParams($params, array('allow_empty'));
        if (in_array('allow_empty', $params) !== false && empty($string)) {
            return true;
        } elseif (preg_match_all("/^[a-z0-9_-]+$/", $string)) {
            return true;
        }
        return false;
    }

    /**
     * parses the params and returns an array
     *
     * @param string $params with the params to be parsed
     * @param array $valid_params an array with the valid params
     * @return array with valid params
     */
    private function parseParams($param, $valid_params)
    {
        $params = explode(",", $param);
        $params = array_filter($params, 'trim');

        if (!is_array($valid_params)) {
            throw new Exception('second parameter in Ilib_Validator->parseParams needs to be an array');
            exit;
        }

        $used_valid_params = array_intersect($params, $valid_params);
        if (count($used_valid_params) != count($params)) {
            throw new Exception('Invalid param '.implode(', ', array_diff($params, $used_valid_params)));
            exit;
        }
        return $used_valid_params;
    }
}