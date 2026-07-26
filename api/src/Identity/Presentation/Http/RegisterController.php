<?php

declare(strict_types=1);

namespace Identity\Presentation\Http;

use Identity\Application\RegisterUser\RegisterUser;
use Identity\Application\RegisterUser\RegisterUserHandlerInterface;
use Identity\Domain\Exception\EmailAlreadyRegistered;
use Identity\Domain\Exception\InvalidEmail;
use Identity\Domain\Exception\PasswordTooWeak;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class RegisterController
{
    public function __construct(private readonly RegisterUserHandlerInterface $registerUser) {}

    #[Route('/api/register', name: 'register', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        /** @var array{email?: mixed, password?: mixed} $payload */
        $payload = \json_decode($request->getContent(), true) ?? [];

        if (!\is_string($payload['email'] ?? null) || !\is_string($payload['password'] ?? null)) {
            return new JsonResponse(['error' => '"email" and "password" are required.'], 400);
        }

        try {
            $id = ($this->registerUser)(new RegisterUser($payload['email'], $payload['password']));
        } catch (InvalidEmail|PasswordTooWeak $e) {
            return new JsonResponse(['error' => $e->getMessage()], 422);
        } catch (EmailAlreadyRegistered $e) {
            return new JsonResponse(['error' => $e->getMessage()], 409);
        }

        return new JsonResponse(['userId' => $id->value], 201);
    }
}
