<?php

namespace Wise\GPSR\ApiAdmin\Service\GpsrSupplier;

use Wise\Core\ApiAdmin\Helper\AdminApiShareMethodsHelper;
use Wise\Core\ApiAdmin\Service\AbstractGetListAdminApiService;
use Wise\Core\Dto\AbstractDto;
use Wise\Core\Model\Address;
use Wise\GPSR\ApiAdmin\Dto\GpsrSupplier\GetGpsrSupplierDto;
use Wise\GPSR\ApiAdmin\Service\GpsrSupplier\Interfaces\GetGpsrSupplierServiceInterface;
use Wise\GPSR\Service\GpsrSupplier\Interfaces\ListGpsrSupplierServiceInterface;

class GetGpsrSupplierService extends AbstractGetListAdminApiService implements GetGpsrSupplierServiceInterface
{
    public function __construct(
        AdminApiShareMethodsHelper $adminApiShareMethodsHelper,
        private readonly ListGpsrSupplierServiceInterface $listSupplierService,
    ){
        parent::__construct($adminApiShareMethodsHelper, $listSupplierService);
    }

    /**
     * Metoda definiuje mapowanie pól z Response DTO, których nazwy NIE SĄ ZGODNE z domeną i wymagają mapowania.
     * @param array $fieldMapping
     * @return array
     */
    protected function prepareCustomFieldMapping(array $fieldMapping = []): array
    {
        $fieldMapping = parent::prepareCustomFieldMapping($fieldMapping);

        return array_merge($fieldMapping,[
            'address' => 'address',
            'quality' => 'quality',
        ]);
    }

    /**
     * Metoda pozwala przekształcić poszczególne obiekty serviceDto przed transformacją do responseDto
     * @param array|null $elementData
     * @return void
     */
    protected function prepareElementServiceDtoBeforeTransform(?array &$elementData): void
    {
        unset($this->fields['address']);
        $this->fields = array_merge($this->fields, [
            'address.name' => 'address.name',
            'address.street' => 'address.street',
            'address.houseNumber' => 'address.houseNumber',
            'address.apartmentNumber' => 'address.apartmentNumber',
            'address.city' => 'address.city',
            'address.postalCode' => 'address.postalCode',
            'address.state' => 'address.state',
            'address.countryCode' => 'address.countryCode',
        ]);
    }

    protected function fillResponseDto(GetGpsrSupplierDto|AbstractDto $responseDtoItem, array $cacheData, ?array $serviceDtoItem = null): void
    {
        $responseDtoItem->setQuality($this->calcQuality($responseDtoItem));
    }

    /**
     *
     * Na podstawie poprawności i obecności poszczególnych pól obliczy wynik (zaczynając od 0 punktów):
     * Tax Number: Jeśli poprawny – dodaj 10 punktów.
     * Email: Jeśli poprawny – dodaj 10 punktów.
     * registeredTradeName: Jeśli pole jest podane – dodaj 5 punktów.
     * Address: Jeśli adres został podany i zawiera wszystkie wymagane pola – dodaj 10 punktów.
     * Phone: Jeśli pole jest podane i poprawne – dodaj 5 punktów.
     * Email: Jeśli domena e-mail (część po „@”) znajduje się na liście zaufanych to są domeny:    example.com, my-company.eu, wiseb2b.eu     – dodaj dodatkowe 5 punktów.
     * Lokalizacja adresu: Jeśli adres został podany, a pole city równa się np. "Warszawa" – dodaj 5 punktów.
     * @return int
     */
    private function calcQuality(GetGpsrSupplierDto|AbstractDto $responseDtoItem): int
    {
        $sum = 0;

        $fields = [
            5 => [
                $this->isNotEmpty($responseDtoItem->getRegisteredTradeName()),
                $this->isPhoneValid($responseDtoItem->getPhone()),
                $this->isEmailTrusted($responseDtoItem->getEmail()),
                $this->isCityNearest($responseDtoItem->getAddress()?->getCity())

            ],
            10 => [
                $this->isTaxNumberValid($responseDtoItem->getTaxNumber()),
                $this->isEmailValid($responseDtoItem->getEmail()),
                $this->isValidAddress($responseDtoItem->getAddress())
            ],

        ];

        foreach ($fields as $points => $checks) {
            foreach ($checks as $check) {
                if ($check) {
                    $sum += $points;
                }
            }
        }

        return $sum; // TODO: map to ENUM
    }

    private function isCityNearest(?string $email): bool
    {
        return $email === 'Warszawa'; // TODO: extract
    }

    private function isValidAddress(Address $address): bool
    {
        $requireFields = ['street', 'houseNumber', 'city', 'postalCode', 'countryCode']; // TODO: extract

        foreach ($address->toArray() as $key => $value) {
            if (in_array($key, $requireFields) && !$this->isNotEmpty($value)) {
                return false;
            }
        }

        return true;
    }

    private function isEmailTrusted(?string $email): bool
    {
        if (empty($email)) {
            return false;
        }

        $trusted = ['example.com', 'my-company.eu', 'wiseb2b.eu']; // TODO: extract
        $domain = substr(strrchr($email, "@"), 1);

        return in_array($domain, $trusted);
    }

    private function isPhoneValid(?string $phone): bool
    {
        if (empty($phone)) {
            return false;
        }

        $phone = preg_replace('/[^0-9]/', '', $phone);

        return strlen($phone) >= 9 && strlen($phone) <= 12;
    }

    private function isTaxNumberValid(?string $taxId): bool
    {
        $nip = preg_replace('/[^0-9]/', '', $taxId);

        if (strlen($nip) !== 10) {
            return false;
        }

        $weights = [6, 5, 7, 2, 3, 4, 5, 6, 7];
        $sum = 0;

        for ($i = 0; $i < 9; $i++) {
            $sum += $weights[$i] * $nip[$i];
        }

        $control = $sum % 11;

        return $control === (int)$nip[9];
    }

    private function isEmailValid(?string $email): bool
    {
        if (empty($email)) {
            return false;
        }

        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    private function isNotEmpty(?string $value): bool
    {
        return !empty($value);
    }
}
