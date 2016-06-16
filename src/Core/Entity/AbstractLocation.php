<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license   MIT
 */

namespace Core\Entity;

use GeoJson\GeoJson;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

abstract class AbstractLocation extends AbstractEntity implements LocationInterface
{
    /**
     * city name of a job location
     *
     * @ODM\Field(type="string")
     */
    protected $city;

    /**
     * region of a job location. E.g "Hessen" is a region in germany
     *
     * @ODM\Field(type="string")
     */
    protected $region;

    /**
     * postalcode of a job location.
     *
     * @var String
     * @ODM\Field(type="string")
     */
    protected $postalcode;

    /**
     * coordinates of a job location.
     *
     * @var GeoJson
     * @ODM\EmbedOne(discriminatorField="_entity")
     */
    protected $coordinates;

    /**
     * Country of a job location
     * @var String
     *
     * @ODM\Field(type="string")
     */
    protected $country;

    public function __construct()
    {
    }

    public function preUpdate()
    {
    }

    public function getCoordinates()
    {
        return $this->coordinates;
    }

    /**
     * @param GeoJson $coordinates
     *
     * @return $this
     */
    public function setCoordinates(GeoJson $coordinates)
    {
        $this->coordinates = $coordinates;
        return $this;
    }

    /**
     * @return String
     */
    public function getPostalCode()
    {
        return $this->postalcode;
    }

    /**
     * @param $postalcode
     *
     * @return $this
     */
    public function setPostalCode($postalcode)
    {
        $this->postalcode = $postalcode;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param $country
     *
     * @return $this
     */
    public function setCity($country)
    {
        $this->city = $country;
        return $this;
    }

    /**
     * @return String
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param $country
     *
     * @return $this
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @param $region
     *
     * @return $this
     */
    public function setRegion($region)
    {
        $this->region = $region;
        return $this;
    }
}