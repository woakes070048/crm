<?php

namespace Oro\Bundle\AccountBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroAccountBundle_Entity_Account;
use Oro\Bundle\AccountBundle\Form\Type\AccountSelectType;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\EmailBundle\Model\EmailHolderInterface;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\ConfigField;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\LocaleBundle\Model\NameInterface;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Represents a person, a company or a group of people.
 * The account aggregates details of all the customer identities assigned to it,
 * providing for a 360-degree view of the customer activity.
 *
 * @mixin OroAccountBundle_Entity_Account
 */
#[ORM\Entity]
#[ORM\Table(name: 'orocrm_account')]
#[ORM\Index(columns: ['name', 'id'], name: 'account_name_idx')]
#[ORM\HasLifecycleCallbacks]
#[Config(
    routeName: 'oro_account_index',
    routeView: 'oro_account_view',
    routeCreate: 'oro_account_create',
    defaultValues: [
        'entity' => [
            'icon' => 'fa-suitcase',
            'contact_information' => ['email' => [['fieldName' => 'contactInformation']]]
        ],
        'ownership' => [
            'owner_type' => 'USER',
            'owner_field_name' => 'owner',
            'owner_column_name' => 'user_owner_id',
            'organization_field_name' => 'organization',
            'organization_column_name' => 'organization_id'
        ],
        'security' => [
            'type' => 'ACL',
            'group_name' => '',
            'category' => 'account_management',
            'field_acl_supported' => true
        ],
        'merge' => ['enable' => true],
        'form' => ['form_type' => AccountSelectType::class, 'grid_name' => 'accounts-select-grid'],
        'dataaudit' => ['auditable' => true],
        'grid' => ['default' => 'accounts-grid', 'context' => 'accounts-for-context-grid'],
        'tag' => ['enabled' => true]
    ]
)]
class Account implements EmailHolderInterface, NameInterface, ExtendEntityInterface
{
    use ExtendEntityTrait;

    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ConfigField(defaultValues: ['importexport' => ['order' => 10]])]
    protected ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[ConfigField(
        defaultValues: [
            'merge' => ['display' => true],
            'dataaudit' => ['auditable' => true],
            'importexport' => ['identity' => true, 'order' => 20]
        ]
    )]
    protected ?string $name = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_owner_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ConfigField(
        defaultValues: [
            'merge' => ['display' => true],
            'dataaudit' => ['auditable' => true],
            'importexport' => ['order' => 30, 'short' => true]
        ]
    )]
    protected ?User $owner = null;

    /**
     * Contacts storage
     *
     * @var Collection<int, Contact> $contacts
     */
    #[ORM\ManyToMany(targetEntity: Contact::class, inversedBy: 'accounts')]
    #[ORM\JoinTable(name: 'orocrm_account_to_contact')]
    #[ConfigField(defaultValues: ['merge' => ['display' => true], 'importexport' => ['order' => 50, 'short' => true]])]
    protected ?Collection $contacts = null;

    /**
     * Default contact entity
     */
    #[ORM\ManyToOne(targetEntity: Contact::class, inversedBy: 'defaultInAccounts')]
    #[ORM\JoinColumn(name: 'default_contact_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ConfigField(
        defaultValues: [
            'merge' => ['display' => true, 'inverse_display' => false],
            'importexport' => ['order' => 40, 'short' => true]
        ]
    )]
    protected ?Contact $defaultContact = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[ConfigField(
        defaultValues: ['entity' => ['label' => 'oro.ui.created_at'], 'importexport' => ['excluded' => true]]
    )]
    protected ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[ConfigField(
        defaultValues: ['entity' => ['label' => 'oro.ui.updated_at'], 'importexport' => ['excluded' => true]]
    )]
    protected ?\DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: Organization::class)]
    #[ORM\JoinColumn(name: 'organization_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?OrganizationInterface $organization = null;

    #[ORM\ManyToOne(targetEntity: Account::class)]
    #[ORM\JoinColumn(name: 'referred_by_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?Account $referredBy = null;

    public function __construct()
    {
        $this->contacts = new ArrayCollection();
    }

    /**
     * Returns the account unique id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param  int     $id
     * @return Account
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    #[\Override]
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set account name
     *
     * @param string $name New name
     *
     * @return Account
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get created date/time
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $created
     *
     * @return Account
     */
    public function setCreatedAt($created)
    {
        $this->createdAt = $created;

        return $this;
    }

    /**
     * Get last update date/time
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updated
     *
     * @return Account
     */
    public function setUpdatedAt($updated)
    {
        $this->updatedAt = $updated;

        return $this;
    }

    /**
     * Get contacts collection
     *
     * @return Collection|Contact[]
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * Add specified contact
     *
     * @param Contact $contact
     *
     * @return Account
     */
    public function addContact(Contact $contact)
    {
        if (!$this->getContacts()->contains($contact)) {
            $this->getContacts()->add($contact);
            $contact->addAccount($this);
        }

        return $this;
    }

    /**
     * Set contacts collection
     *
     * @param Collection $contacts
     *
     * @return Account
     */
    public function setContacts(Collection $contacts)
    {
        $this->contacts = $contacts;

        return $this;
    }

    /**
     * Remove specified contact
     *
     * @param Contact $contact
     *
     * @return Account
     */
    public function removeContact(Contact $contact)
    {
        if ($this->getContacts()->contains($contact)) {
            $this->getContacts()->removeElement($contact);
            $contact->removeAccount($this);
        }

        return $this;
    }

    #[\Override]
    public function __toString()
    {
        return (string) $this->getName();
    }

    /**
     * Pre persist event listener
     */
    #[ORM\PrePersist]
    public function beforeSave()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updatedAt = clone $this->createdAt;
    }

    /**
     * Pre update event handler
     */
    #[ORM\PreUpdate]
    public function doPreUpdate()
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param User $owningUser
     *
     * @return Account
     */
    public function setOwner($owningUser)
    {
        $this->owner = $owningUser;

        return $this;
    }

    /**
     * @param Contact $defaultContact
     *
     * @return Account
     */
    public function setDefaultContact($defaultContact)
    {
        if ($this->defaultContact === $defaultContact) {
            return $this;
        }

        /**
         * As resolving of $this->defaultContact->getDefaultInAccounts() lazy collection will
         * overwrite $this->defaultContact to value from db, make sure the collection is resolved
         */
        if ($this->defaultContact) {
            $this->defaultContact->getDefaultInAccounts()->toArray();
        }

        $originalContact = $this->defaultContact;
        $this->defaultContact = $defaultContact;

        if ($defaultContact) {
            $defaultContact->addDefaultInAccount($this);
        }

        if ($originalContact) {
            $originalContact->removeDefaultInAccount($this);
        }

        if ($defaultContact && !$this->contacts->contains($defaultContact)) {
            $this->addContact($defaultContact);
        }

        return $this;
    }

    /**
     * @return Contact
     */
    public function getDefaultContact()
    {
        return $this->defaultContact;
    }

    /**
     * Get the primary email address of the default contact
     *
     * @return string
     */
    #[\Override]
    public function getEmail()
    {
        $contact = $this->getDefaultContact();
        if (!$contact) {
            return null;
        }

        return $contact->getEmail();
    }

    /**
     * Set organization
     *
     * @param Organization|null $organization
     *
     * @return Account
     */
    public function setOrganization(?Organization $organization = null)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * Get organization
     *
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @return Account
     */
    public function getReferredBy()
    {
        return $this->referredBy;
    }

    /**
     * @param Account|null $referredBy
     *
     * @return Account
     */
    public function setReferredBy(?Account $referredBy = null)
    {
        $this->referredBy = $referredBy;

        return $this;
    }
}
