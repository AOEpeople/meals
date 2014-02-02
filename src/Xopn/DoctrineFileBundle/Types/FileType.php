<?php

namespace Xopn\DoctrineFileBundle\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\HttpFoundation\File\File;

class FileType extends Type {

	/**
	 * Gets the SQL declaration snippet for a field of this type.
	 *
	 * @param array $fieldDeclaration The field declaration.
	 * @param AbstractPlatform $platform The currently used database platform.
	 */
	public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
	{
		return $platform->getVarcharTypeDeclarationSQL($fieldDeclaration);
	}

	/**
	 * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
	 * @return int|null
	 */
	public function getDefaultLength(AbstractPlatform $platform)
    {
        return $platform->getVarcharDefaultLength();
    }

	/**
     * Converts a value from its PHP representation to its database representation
     * of this type.
     *
     * @param mixed $value The value to convert.
     * @param AbstractPlatform $platform The currently used database platform.
     * @return mixed The database representation of the value.
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
	    if($value instanceof File) {
            return $value->getBasename();
        } else {
            return $value;
        }
    }

	/**
	 * Gets the name of this type.
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'file';
	}
}