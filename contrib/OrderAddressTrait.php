<?php

declare(strict_types=1);

namespace App\Entity\Order;

use App\Entity\Address\Address;

trait OrderAddressTrait
{
    private Address $shippingAddress;
    private ?Address $billingAddress = null;

    public function setShippingAddress(Address $address): void
    {
        $this->shippingAddress = $address;
    }
    public function setBillingAddress(?Address $address): void
    {
        $this->billingAddress = $address;
    }
    public function shippingAddress(): Address
    {
        return $this->shippingAddress;
    }
    public function billingAddress(): ?Address
    {
        return $this->billingAddress;
    }
}
