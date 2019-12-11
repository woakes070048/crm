<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Formatter;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactEmail;
use Oro\Bundle\ContactBundle\Entity\ContactPhone;
use Oro\Bundle\ContactBundle\Handler\ContactEmailDeleteHandlerExtension;
use Oro\Bundle\EntityBundle\Handler\EntityDeleteAccessDeniedExceptionFactory;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContactEmailDeleteHandlerExtensionTest extends \PHPUnit\Framework\TestCase
{
    /** @var AuthorizationCheckerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $authorizationChecker;

    /** @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $translator;

    /** @var ContactEmailDeleteHandlerExtension */
    private $extension;

    protected function setUp()
    {
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->extension = new ContactEmailDeleteHandlerExtension(
            $this->authorizationChecker,
            $this->translator
        );
        $this->extension->setDoctrine($this->createMock(ManagerRegistry::class));
        $this->extension->setAccessDeniedExceptionFactory(new EntityDeleteAccessDeniedExceptionFactory());
    }

    public function testAssertDeleteGrantedWhenNoOwner()
    {
        $contactEmail = new ContactEmail();

        $this->authorizationChecker->expects($this->never())
            ->method('isGranted');
        $this->translator->expects($this->never())
            ->method('trans');

        $this->extension->assertDeleteGranted($contactEmail);
    }

    public function testAssertDeleteGrantedWhenAccessGranted()
    {
        $contactEmail = new ContactEmail();
        $contact = new Contact();
        $contactEmail->setOwner($contact);

        $contact->setFirstName('fn');

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('EDIT', $this->identicalTo($contact))
            ->willReturn(true);
        $this->translator->expects($this->never())
            ->method('trans');

        $this->extension->assertDeleteGranted($contactEmail);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @expectedExceptionMessage The delete operation is forbidden. Reason: access denied.
     */
    public function testAssertDeleteGrantedWhenAccessDenied()
    {
        $contactEmail = new ContactEmail();
        $contact = new Contact();
        $contactEmail->setOwner($contact);

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('EDIT', $this->identicalTo($contact))
            ->willReturn(false);
        $this->translator->expects($this->never())
            ->method('trans');

        $this->extension->assertDeleteGranted($contactEmail);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @expectedExceptionMessage The delete operation is forbidden. Reason: translated exception message.
     */
    public function testAssertDeleteGrantedWhenPrimaryEmailIsDeletedAndThereIsOtherEmails()
    {
        $contactEmail = new ContactEmail();
        $contact = new Contact();
        $contactEmail->setOwner($contact);

        $contactEmail->setPrimary(true);
        $contact->addEmail($contactEmail);
        $contact->addEmail(new ContactEmail());

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('EDIT', $this->identicalTo($contact))
            ->willReturn(true);
        $this->translator->expects($this->once())
            ->method('trans')
            ->with('oro.contact.validators.emails.delete.more_one', [], 'validators')
            ->willReturn('translated exception message');

        $this->extension->assertDeleteGranted($contactEmail);
    }

    public function testAssertDeleteGrantedWhenPrimaryEmailIsDeletedIfThereIsNoOtherEmails()
    {
        $contactEmail = new ContactEmail();
        $contact = new Contact();
        $contactEmail->setOwner($contact);

        $contact->setFirstName('fn');
        $contactEmail->setPrimary(true);
        $contact->addEmail($contactEmail);

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('EDIT', $this->identicalTo($contact))
            ->willReturn(true);
        $this->translator->expects($this->never())
            ->method('trans');

        $this->extension->assertDeleteGranted($contactEmail);
    }

    public function testAssertDeleteGrantedWhenNotPrimaryEmailIsDeleted()
    {
        $contactEmail = new ContactEmail();
        $contact = new Contact();
        $contactEmail->setOwner($contact);

        $contact->setFirstName('fn');
        $contact->addEmail($contactEmail);

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('EDIT', $this->identicalTo($contact))
            ->willReturn(true);
        $this->translator->expects($this->never())
            ->method('trans');

        $this->extension->assertDeleteGranted($contactEmail);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @expectedExceptionMessage The delete operation is forbidden. Reason: translated exception message.
     */
    public function testAssertDeleteGrantedWhenLastEmailIsDeletedAndContactDoesNotHaveOtherIdentification()
    {
        $contactEmail = new ContactEmail();
        $contact = new Contact();
        $contactEmail->setOwner($contact);

        $contactEmail->setPrimary(true);
        $contact->addEmail($contactEmail);

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('EDIT', $this->identicalTo($contact))
            ->willReturn(true);
        $this->translator->expects($this->once())
            ->method('trans')
            ->with('oro.contact.validators.contact.has_information', [], 'validators')
            ->willReturn('translated exception message');

        $this->extension->assertDeleteGranted($contactEmail);
    }

    public function testAssertDeleteGrantedWhenLastEmailIsDeletedAndContactHasFirstName()
    {
        $contactEmail = new ContactEmail();
        $contact = new Contact();
        $contactEmail->setOwner($contact);

        $contact->setFirstName('fn');
        $contactEmail->setPrimary(true);
        $contact->addEmail($contactEmail);

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('EDIT', $this->identicalTo($contact))
            ->willReturn(true);
        $this->translator->expects($this->never())
            ->method('trans');

        $this->extension->assertDeleteGranted($contactEmail);
    }

    public function testAssertDeleteGrantedWhenLastEmailIsDeletedAndContactHasLastName()
    {
        $contactEmail = new ContactEmail();
        $contact = new Contact();
        $contactEmail->setOwner($contact);

        $contact->setLastName('ln');
        $contactEmail->setPrimary(true);
        $contact->addEmail($contactEmail);

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('EDIT', $this->identicalTo($contact))
            ->willReturn(true);
        $this->translator->expects($this->never())
            ->method('trans');

        $this->extension->assertDeleteGranted($contactEmail);
    }

    public function testAssertDeleteGrantedWhenLastEmailIsDeletedAndContactHasPhone()
    {
        $contactEmail = new ContactEmail();
        $contact = new Contact();
        $contactEmail->setOwner($contact);

        $contact->addPhone(new ContactPhone());
        $contactEmail->setPrimary(true);
        $contact->addEmail($contactEmail);

        $this->authorizationChecker->expects($this->once())
            ->method('isGranted')
            ->with('EDIT', $this->identicalTo($contact))
            ->willReturn(true);
        $this->translator->expects($this->never())
            ->method('trans');

        $this->extension->assertDeleteGranted($contactEmail);
    }
}