<?php

namespace App\Tests\Functional\Security;

use App\Entity\User as AppUser;
use App\Security\UserChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserInterface;

class UserCheckerTest extends TestCase
{
    /** Instance of the UserChecker under test */
    private UserChecker $userChecker;

    /**
     * Initialize a fresh UserChecker before each test.
     */
    protected function setUp(): void
    {
        $this->userChecker = new UserChecker();
    }

    /**
     * Ensure checkPreAuth() throws an exception when the user is restricted (inactive).
     */
    public function testCheckPreAuthThrowsExceptionWhenUserIsRestricted(): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject&UserInterface */
        $user = $this->createMock(AppUser::class);

        // Simulate inactive account
        $user->method('isActive')->willReturn(false);

        // Expected exception and message
        $this->expectException(CustomUserMessageAccountStatusException::class);
        $this->expectExceptionMessage('Votre compte a été bloqué.');

        // Trigger pre-auth check
        $this->userChecker->checkPreAuth($user);
    }

    /**
     * Ensure checkPreAuth() does NOT throw an exception when the account is active.
     */
    public function testCheckPreAuthDoesNotThrowExceptionWhenUserIsNotRestricted(): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject&UserInterface */
        $user = $this->createMock(AppUser::class);

        // Simulate active account
        $user->method('isActive')->willReturn(true);

        // Should not throw any exception
        $this->userChecker->checkPreAuth($user);

        // Explicit assertion to mark test as successful
        $this->addToAssertionCount(1);
    }

    /**
     * Ensure checkPostAuth() does NOT throw when the user account is not expired.
     */
    public function testCheckPostAuthDoesNotThrowExceptionWhenUserAccountIsNotExpired(): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject&UserInterface */
        $user = $this->createMock(AppUser::class);

        // Account marked active for consistency
        $user->method('isActive')->willReturn(true);

        // Should not throw
        $this->userChecker->checkPostAuth($user);

        // Explicit assertion to avoid risky test warnings
        $this->addToAssertionCount(1);
    }

    public function testCheckPostAuthDoesNotThrowExceptionWhenUserIsNotRestricted(): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject&UserInterface */
        $user = $this->createMock(AppUser::class);

        // Simulate active account
        $user->method('isActive')->willReturn(true);

        // Should not throw any exception
        $this->userChecker->checkPostAuth($user);

        // Explicit assertion to mark test as successful
        $this->addToAssertionCount(1);
    }
}
