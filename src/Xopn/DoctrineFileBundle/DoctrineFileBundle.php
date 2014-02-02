<?php

namespace Xopn\DoctrineFileBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DoctrineFileBundle extends Bundle
{
	public function boot() {
		$em = $this->container->get('doctrine.orm.entity_manager');
		if(!Type::hasType('file')) {
			Type::addType('file', 'Xopn\\DoctrineFileBundle\\Types\\FileType');
		}

        $em->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('file','file');
	}
}
