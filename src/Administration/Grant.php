<?php

declare(strict_types=1);

namespace Nylas\Administration;

use Nylas\Utilities\API;
use Nylas\Utilities\Options;
use Nylas\Utilities\Validator as V;

class Grant
{
    public function __construct(private Options $options)
    {
    }

    public function getGrant(string $grantId)
    {
        V::doValidate(V::stringType()->notEmpty(), $grantId);

        $header = ['Authorization' => sprintf('Bearer %s', $this->options->getApiKey())];

        return $this->options
            ->getSync()
            ->setHeaderParams($header)
            ->setPath($grantId)
            ->get(API::LIST['grant']);
    }
}
