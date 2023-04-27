<?php

namespace MIT\Delivery\Plugin\Model;

class ShippingMethodManagement
{

    public function afterEstimateByExtendedAddress($shippingMethodManagement, $output)
    {
        return $this->filterOutput($output);
    }
    private function filterOutput($output)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/freeshipping.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $free = [];
        foreach ($output as $shippingMethod) {
            $body = json_encode($shippingMethod);
            $logger->info($body);
            if ($shippingMethod->getCarrierCode() == 'freeshipping' && $shippingMethod->getMethodCode() == 'freeshipping') {
                $free[] = $shippingMethod;
            }
        }
        if ($free) {
            return $free;
        }
        return $output;
    }
}
