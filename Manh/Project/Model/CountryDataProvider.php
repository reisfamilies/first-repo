<?php

namespace Mageplus\Project\Model;

use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Directory\Model\CountryFactory;

class CountryDataProvider
{
    /**
     * @var CountryInformationAcquirerInterface
     */
    private $countryInformationAcquirer;

    /**
     * @var array
     */
    private $countriesList;

    /**
     * @var ResolverInterface
     */
    private $resolver;

    /**
     * @var ArrayUtils
     */
    private $arrayUtils;

    private $countryFactory;

    public function __construct(
        CountryInformationAcquirerInterface $countryInformationAcquirer,
        CountryFactory $countryFactory,
        ArrayUtils                          $arrayUtils,
        ResolverInterface                   $resolver
    )
    {
        $this->countryInformationAcquirer = $countryInformationAcquirer;
        $this->countryFactory = $countryFactory;
        $this->arrayUtils = $arrayUtils;
        $this->resolver = $resolver;
    }

    /**
     * @return array
     */
    public function getCountriesList()
    {
        if (!$this->countriesList) {
            $this->countriesList = [];
            $countries = $this->countryInformationAcquirer->getCountriesInfo();
            if ($countries) {
                foreach ($countries as $country) {
                    $this->countriesList[$country->getFullNameLocale()] = $country->getId();
                }
            }

            $this->arrayUtils->ksortMultibyte($this->countriesList, $this->resolver->getLocale());
            $this->countriesList = array_flip($this->countriesList);
        }

        return $this->countriesList;
    }

    /**
     * @param string $code
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCountryNameByCode($code)
    {
        $countryName = '';
        if ($code) {
            $country = $this->countryInformationAcquirer->getCountryInfo($code);
            if ($country && $country->getId()) {
                $countryName = $country->getFullNameLocale();
            }
        }

        return $countryName;
    }

    /**
     * @param int $countryId
     * @param int $regionId
     * @param string $regionName
     * @return string
     * @throws NoSuchEntityException
     */
    public function getRegionName($countryId, $regionId, $regionName)
    {
        $country = $this->countryInformationAcquirer->getCountryInfo($countryId);
        $regions = $country->getAvailableRegions();
        if ($regions && count($regions)) {
            foreach ($regions as $region) {
                if ($regionId == $region->getId()) {
                    $regionName = $region->getName();
                    break;
                }
            }
        }

        return $regionName;
    }

    public function getCountryName($countryId)
    {
        $country = $this->countryFactory->create()->load($countryId);
        return $country->getName();
    }
}
