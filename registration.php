
<?php
/**
 * OpenTracing Integration Module
 *
 * @package Trysoft\OpenTracing
 * @author Maciej Trybuła <maciej.trybula@gmail.com>
 * @copyright 2018 Trysoft Maciej Trybuła
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'Trysoft_OpenTracing',
    __DIR__
);