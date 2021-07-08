<?php

namespace Mealz\UserBundle\Service;

putenv('LDAPTLS_REQCERT=never');

use Symfony\Component\Ldap\LdapClient as SymfonyLdapClient;

class LdapClient extends SymfonyLdapClient
{
    public function __construct(
        $host = null,
        $port = 389,
        $version = 3,
        $useSsl = false,
        $useStartTls = false,
        $optReferrals = false
    ) {
        parent::__construct($host, $port, $version, $useSsl, $useStartTls, $optReferrals);
    }
}
