<?php


namespace MIT\CustomShipment\Plugin\Magento\Quote\Model;

class ShippingMethodManagement
{

    private function shipingforuse($shippingMethodManagement, $output)
    {
        usort($output, fn ($a, $b) => strcmp($a->getAmount(), $b->getAmount()));
        return $output;
    }

    public function afterEstimateByAddressId(\Magento\Quote\Model\ShippingMethodManagement $shippingMethodManagement, $output)
    {
        $result = $this->shipingforuse($shippingMethodManagement, $output);
        return $result;
    }

    public function afterExtractAddressData(\Magento\Quote\Model\ShippingMethodManagement $shippingMethodManagement, $output)
    {
        $result = $this->shipingforuse($shippingMethodManagement, $output);
        return $result;
    }

    public function afterEstimateByExtendedAddress(\Magento\Quote\Model\ShippingMethodManagement $shippingMethodManagement, $output)
    {
        $result = $this->shipingforuse($shippingMethodManagement, $output);
        return $result;
    }
}
