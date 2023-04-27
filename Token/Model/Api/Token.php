<?php

namespace MIT\Token\Model\Api;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory;
use MIT\Token\Api\TokenInterface;

class Token implements TokenInterface
{

    const TOKEN_KEY = "PLaZBU2rphfDepAxLvkwx8Bttfk36R3Sp3Q6NfZKFeUEGL5Fj6sJZNjUTT9hLxqZ7Qa5qahsm9ztKhEpnz2Gt7zG5HJyxuC9qEcTLyBsDS8J5Rtw8GUk9VX4CYtfNMV9";

    /**
     * @var CollectionFactory
     */
    private $tokenCollection;

    /**
     * Initialize dependencies.
     * @param CollectionFactory $tokenCollection
     */
    public function __construct(
        CollectionFactory $tokenCollection
    ) {
        $this->tokenCollection = $tokenCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken($consumerToken)
    {
        if ($this->checkValidToken($consumerToken)) {
            $collection = $this->tokenCollection->create();
            $collection->getSelect()->join('integration', 'integration.consumer_id = main_table.consumer_id', 'name');
            $collection->getSelect()->where('integration.status = ?', 1)
                                    ->where('integration.name = ? ', 'mobile');
            $collection->setPageSize(1);
            $collection->setCurPage(1);
            if ($collection->getSize() > 0) {
                $item = $collection->getFirstItem();
                if ($item->getData()['consumer_id'] && $item->getData()['type'] === 'access') {
                    return $item->getData()['token'];
                }
            }
        } else {
            throw new LocalizedException(__("The token couldn't be revoked."));
        }
        throw new LocalizedException(__("The token couldn't be revoked."));
    }

    /**
     * check token valid or not
     * @param string $token
     * @param string $secret
     * @return bool
     */
    private function checkValidToken($token, $secret = self::TOKEN_KEY)
    {
        try {
            $tokenParts = explode('.', $token);
            $header = base64_decode($tokenParts[0]);
            $payload = base64_decode($tokenParts[1]);
            $signature_provided = $tokenParts[2];

            if ($payload) {
                $body = (json_decode($payload));
                $is_token_expired = ($body->exp - time()) < 0;

                $base64_url_header = $this->base64url_encode($header);
                $base64_url_payload = $this->base64url_encode($payload);
                $signature = hash_hmac('SHA256', $base64_url_header . "." . $base64_url_payload, $secret, true);
                $base64_url_signature = $this->base64url_encode($signature);
                if ($base64_url_signature === $signature_provided && !$is_token_expired) {
                    return true;
                }
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
        return false;
    }

    /**
     * base64 url encoding
     * @param string $str
     * @return string
     */
    private function base64url_encode($str)
    {
        return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
    }
}

