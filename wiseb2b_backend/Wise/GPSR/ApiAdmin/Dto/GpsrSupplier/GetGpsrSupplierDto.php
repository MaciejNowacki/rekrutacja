<?php

namespace Wise\GPSR\ApiAdmin\Dto\GpsrSupplier;

use Wise\Core\Api\Attributes\OpenApi\EndpointElement as OA;
use Wise\Core\Api\Dto\Attributes\FieldEntityMapping;
use Wise\Core\Api\Fields\FieldHandlingEnum;
use Wise\Core\ApiAdmin\Dto\CommonAdminApiDto;
use Wise\Core\ApiAdmin\Dto\CommonDateInsertUpdateDtoTrait;
use Wise\Core\Model\Address;

class GetGpsrSupplierDto extends CommonAdminApiDto
{
    use CommonDateInsertUpdateDtoTrait;

    #[OA\Property(
        description: 'Id dostawcy w ERP',
        example: 'CONTRACT-123',
    )]
    #[FieldEntityMapping('idExternal')]
    protected ?string $id = null;

    #[OA\Property(
        description: 'ID wewnętrzne systemu B2B. Można używać zamiennie z id (o ile jest znane). Jeśli podane, ma priorytet względem id.',
        example: 1,
    )]
    #[FieldEntityMapping('id')]
    protected ?int $internalId = null;

    #[OA\Property(
        description: 'Symbol - unikalny identyfikator, aby można było odwołać się do konkretnej umowy w kodzie',
        example: 'ELFIX',
    )]
    protected ?string $symbol = null;

    #[OA\Property(
        description: 'Numer Nip',
        example: '111222233333',
    )]
    protected ?string $taxNumber = null;

    #[OA\Property(
        description: 'Numer Nip',
        example: '698111222',
    )]
    protected ?string $phone = null;

    #[OA\Property(
        description: 'Adres e-mail',
        example: 'contact@example.com',
    )]
    protected ?string $email = null;

    #[OA\Property(
        description: 'Zarejestrowana nazwa handlowa',
        example: 'Moja marka',
    )]
    protected ?string $registeredTradeName = null;

    #[OA\Property(
        description: 'Adres dostawcy'
    )]
    protected ?Address $address;

    #[OA\Property(
        description: 'Wyliczona jakość dostawcy',
        example: 80,
    )]
    #[FieldEntityMapping(FieldHandlingEnum::HANDLE_ONLY_BY_FILL_RESPONSE_DTO)]
    protected ?int $quality = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getInternalId(): ?int
    {
        return $this->internalId;
    }

    public function setInternalId(?int $internalId): self
    {
        $this->internalId = $internalId;

        return $this;
    }

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setSymbol(?string $symbol): self
    {
        $this->symbol = $symbol;

        return $this;
    }

    public function getTaxNumber(): ?string
    {
        return $this->taxNumber;
    }

    public function setTaxNumber(?string $taxNumber): self
    {
        $this->taxNumber = $taxNumber;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getRegisteredTradeName(): ?string
    {
        return $this->registeredTradeName;
    }

    public function setRegisteredTradeName(?string $registeredTradeName): self
    {
        $this->registeredTradeName = $registeredTradeName;

        return $this;
    }

    public function getQuality(): ?int
    {
        return $this->quality;
    }

    public function setQuality(?int $quality): self
    {
        $this->quality = $quality;
        
        return $this;
    }
}
