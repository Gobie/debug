<?php

namespace Gobie\Debug\Message\Start\GlobalDumpers;

/**
 * Dumper pro PHP extenze.
 */
class ExtensionsGlobalDumper implements IGlobalDumper
{

    /**
     * Vrátí data k dumpnutí.
     *
     * @return mixed
     */
    public function getData()
    {
        $extensions       = array();
        $loadedExtensions = \get_loaded_extensions();
        foreach ($loadedExtensions as $extension) {
            $extension     = new \ReflectionExtension($extension);
            $version       = $extension->getVersion();
            $extensionInfo = array(
                'name' => $extension->getName() . ($version ? ' (' . $version . ')' : '')
            );

            $classes = $extension->getClassNames();
            if ($classes) {
                $extensionInfo['classes'] = \implode(', ', $classes);
            }

            $dependencies = $extension->getDependencies();
            if ($dependencies) {
                $extensionInfo['dependencies'] = $dependencies;
            }

            $extensions[] = $extensionInfo;
        }

        return $extensions;
    }

    /**
     * Vrátí název dumperu.
     *
     * @return string
     */
    public function getName()
    {
        return 'Extensions';
    }
}
