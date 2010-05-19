<?php
error_reporting(0);

require_once 'PHPUnit/Framework.php';

require_once 'Ilib/Validator.php';
require_once 'Ilib/Error.php';

class Ilib_ValidatorTest extends PHPUnit_Framework_TestCase
{
    private $validator;
    private $error;

    function setUp()
    {
        // $this->validator = new Validator(new Ilib_Error);
    }

    function testConstructWithNoErrorObject()
    {
        $validator = new Ilib_Validator();
        $this->assertEquals('Ilib_Validator', get_class($validator));
    }

    function testConstructWithCustomErrorObject()
    {
        $error = new Ilib_Error;
        $validator = new Ilib_Validator($error);
        $validator->isDate('nodate', 'invalid date');
        $this->assertEquals(array('invalid date'), $error->getMessage());
    }

    function testEmailReturnsFalseOnInvalidEmail()
    {
        $validator = new Ilib_Validator(new Ilib_Error);
        $this->assertFalse($validator->isEmail('all_worong', 'not valid'));
    }

    function testEmailReturnsFalseOnInvalidDomain()
    {
        $validator = new Ilib_Validator(new Ilib_Error);
        $this->assertFalse($validator->isEmail('test@this-cant-be-a-valid-domain.com', 'not valid'));
    }

    function testEmailReturnsTrueOnValidEmail()
    {
        $validator = new Ilib_Validator(new Ilib_Error);
        $this->assertTrue($validator->isEmail('support@intraface.dk', 'valid'));
    }

    function testEmailReturnsTrueOnAllowedEmpty()
    {
        $validator = new Ilib_Validator(new Ilib_Error);
        $this->assertTrue($validator->isEmail('', 'valid', 'allow_empty'));
    }

    function testEmailThrowsExceptionOnInvalidParameter()
    {
        $validator = new Ilib_Validator(new Ilib_Error);
        try {
            $this->assertTrue($validator->isEmail('', 'valid', 'not_valid_param'));
        }
        catch (Exception $e) {
            $this->assertTrue(true);
            return;
        }
        $this->assertTrue(false);
    }

    function testIdentifierReturnsFalseOnInvalidIdentifier()
    {
        $validator = new Ilib_Validator(new Ilib_Error);
        $this->assertFalse($validator->isIdentifier('this.*.is.pretty/invalid', 'Not valid'));
    }

    function testIdentifierReturnsFalseOnEmptyIdentifier()
    {
        $validator = new Ilib_Validator(new Ilib_Error);
        $this->assertFalse($validator->isIdentifier('', 'Not valid'));
    }

    function testIdentifierReturnsTrueOnValidIdentifier()
    {
        $validator = new Ilib_Validator(new Ilib_Error);
        $this->assertTrue($validator->isIdentifier('this-is-a-valid-identifier', 'Not valid'));
    }

    function testIsDoubleReturnsTrueOnInteger()
    {
        $validator = new Ilib_Validator(new Ilib_Error);
        $this->assertTrue($validator->isDouble(10, 'Not valid'));
    }

    function testIsDoubleReturnsTrueOnIntegerAsString()
    {
        $validator = new Ilib_Validator(new Ilib_Error);
        $this->assertTrue($validator->isDouble('10', 'Not valid'));
    }

    function testIsDoubleReturnsTrueOnDouble()
    {
        $validator = new Ilib_Validator(new Ilib_Error);
        $this->assertTrue($validator->isDouble('10,10', 'Not valid'));
    }
}