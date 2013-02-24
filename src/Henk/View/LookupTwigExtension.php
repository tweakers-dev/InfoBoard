<?php

namespace Henk\View;

class LookupTwigExtension extends \Twig_Extension
{

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'lookup';
    }

    public function getFilters()
    {
        return array(
            'lookupStatus' => new \Twig_SimpleFilter('lookupStatus', '\Henk\Util\Lookup::lookupStatus'),
            'lookupPriority' => new \Twig_SimpleFilter('lookupPriority', '\Henk\Util\Lookup::lookupPriority'),
        );
    }
}
