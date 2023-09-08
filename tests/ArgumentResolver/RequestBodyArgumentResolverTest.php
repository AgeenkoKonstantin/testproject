<?php

namespace App\Tests\ArgumentResolver;

use _PHPStan_dfcaa3082\Nette\Neon\Exception;
use App\ArgumentResolver\RequestBodyArgumentResolver;
use App\Attribute\RequestBody;
use App\Exception\RequestBodyConvertException;
use App\Exception\ValidationException;
use App\Tests\AbstractTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestBodyArgumentResolverTest extends AbstractTestCase
{
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
    }

    public function testSupports(): void
    {
        $argument = new ArgumentMetadata('some', null, false, false, null, false, [
           new RequestBody(),
        ]);
        $this->assertTrue($this->createResolver()->supports(new Request(), $argument));
    }

    public function testNoSupports(): void
    {
        $argument = new ArgumentMetadata('some', null, false, false, null);
        $this->assertFalse($this->createResolver()->supports(new Request(), $argument));
    }

    public function testResolveThrowsWhenDeserialize(): void
    {
        $this->expectException(RequestBodyConvertException::class);
        $request = new Request([], [], [], [], [], [], 'testing content');
        $argument = new ArgumentMetadata('some', \stdClass::class, false, false, null, false, [
            new RequestBody(),
        ]);

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with('testing content', \stdClass::class, JsonEncoder::FORMAT)
            ->willThrowException(new Exception());

        $this->createResolver()->resolve($request, $argument)->next();
    }

    public function testResolveThrowsWhenValidationFails(): void
    {
        $this->expectException(ValidationException::class);

        $body = ['test' => true];
        $encodedBody = json_encode($body);
        $request = new Request([], [], [], [], [], [], $encodedBody);
        $argument = new ArgumentMetadata('some', \stdClass::class, false, false, null, false, [
            new RequestBody(),
        ]);

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with($encodedBody, \stdClass::class, JsonEncoder::FORMAT)
            ->willReturn($body);


        $this->validator->expects($this->once())
            ->method('validate')
            ->with($body)
            ->willReturn(new ConstraintViolationList([
                new ConstraintViolation('error', null, [], null, 'some', null),
            ]));

        $this->createResolver()->resolve($request, $argument)->next();
    }

    public function testResolve(): void
    {
        $body = ['test' => true];
        $encodedBody = json_encode($body);
        $request = new Request([], [], [], [], [], [], $encodedBody);
        $argument = new ArgumentMetadata('some', \stdClass::class, false, false, null, false, [
            new RequestBody(),
        ]);

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with($encodedBody, \stdClass::class, JsonEncoder::FORMAT)
            ->willReturn($body);


        $this->validator->expects($this->once())
            ->method('validate')
            ->with($body)
            ->willReturn(new ConstraintViolationList([]));

        $actual = $this->createResolver()->resolve($request, $argument)->current();

        $this->assertEquals($body, $actual);
    }

    private function createResolver(): RequestBodyArgumentResolver
    {
        return new RequestBodyArgumentResolver($this->serializer, $this->validator);
    }
}
