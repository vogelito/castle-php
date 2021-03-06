<?php

class CastleJWTTest extends \PHPUnit_Framework_TestCase
{
  public static function setUpBeforeClass() {
    Castle::setApiKey('secretkey');
  }

  public function invalidJWTs() {
    return array(
      array('1234.123.432'),
      array(null)
    );
  }

  public function validJWTs() {
    return array(
      array('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImlhdCI6MTM5ODIzOTIwMywiZXhwIjoxMzk4MjQyODAzfQ.eyJ1c2VyX2lkIjoiZUF3djVIdGRiU2s4Yk1OWVpvanNZdW13UXlLcFhxS3IifQ.Apa7EmT5T1sOYz4Af0ERTDzcnUvSalailNJbejZ2ddQ', 'user_id', 'eAwv5HtdbSk8bMNYZojsYumwQyKpXqKr')
    );
  }

  /**
   * @dataProvider invalidJWTs
   * @expectedException Castle_SecurityError
   */
  public function testInvalidJWT($data) {
    $jwt = new Castle_JWT($data);
    $jwt->isValid();
  }

  /**
   * @expectedException Castle_SecurityError
   */
  public function testInvalidConstructorArgument()
  {
    $jwt = new Castle_JWT('1234');
  }

  /**
   * @dataProvider validJWTs
   */
  public function testValidJWT($data) {
    $jwt = new Castle_JWT($data);
    $this->assertTrue($jwt->isValid());
  }

  /**
   * @dataProvider validJWTs
   */
  public function testValidJWTPayload($data, $key, $value) {
    $jwt = new Castle_JWT($data);
    $payload = $jwt->getBody();
    $this->assertEquals($payload[$key], $value);
  }

  public function testSetHeader()
  {
    $jwt = new Castle_JWT();
    $now = time();
    $jwt->setHeader('exp', $now);
    $header = $jwt->getHeader();
    $this->assertEquals($header['exp'], $now);
  }

  public function testGetSetBody()
  {
    $jwt = new Castle_JWT();
    $jwt->setBody('chg', '1234');
    $this->assertEquals($jwt->getBody('chg'), '1234');
    $this->assertTrue($jwt->isValid());
  }

  public function testGetNonExistantBodyKey()
  {
    $jwt = new Castle_JWT();
    $this->assertNull($jwt->getBody('chg'));
  }

  public function testSetEmptyHeader()
  {
    $jwt = new Castle_JWT();
    $jwt->setHeader('tmp', 1);
    $this->assertEquals(1, $jwt->getHeader('tmp'));
    $jwt->setHeader('tmp');
    $this->assertNull($jwt->getHeader('tmp'));
  }

  /**
   * @dataProvider validJWTs
   */
  public function testGetheaderWithKey($data)
  {
    $jwt = new Castle_JWT($data);
    $this->assertEquals($jwt->getHeader('typ'), 'JWT');
  }

  public function testHasExpired()
  {
    $jwt = new Castle_JWT();
    $jwt->setHeader(array('exp' => time() + 60));
    $this->assertFalse($jwt->hasExpired());
  }
}
