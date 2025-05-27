<?php
/**
 * This file is a part of the Digital Garden gotenberg bundle.
 */

 namespace DigitalGarden\SonataAttributeBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

/**
 * Gotenberg bundle test kernel.
 */
final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function __construct()
    {
        parent::__construct('test', false);
    }

    /**
     * {@inheritDoc}
     */
    public function getProjectDir(): string
    {
        return __DIR__;
    }

    /**
     * @see MicroKernelTrait::getConfigDir()
     */
    private function getConfigDir(): string
    {
        return __DIR__ . '/Resources/config';
    }
}
 