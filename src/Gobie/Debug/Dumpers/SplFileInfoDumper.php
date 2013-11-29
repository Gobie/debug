<?php

namespace Gobie\Debug\Dumpers;

use Gobie\Debug\Helpers;

/**
 * SplFileInfo dumper.
 */
class SplFileInfoDumper extends ObjectDumper
{

    public function getReplacedClasses()
    {
        return array(
            '\Gobie\Debug\Dumpers\ObjectDumper' => true
        );
    }

    /**
     * Dumps file statistics.
     *
     * @param \SplFileInfo $var   Soubor
     * @param integer      $level Level
     * @param integer      $depth Depth
     * @param array        $out   Storage
     * @return bool Returns true, if dump should be ended.
     */
    protected function dumpBody(&$var, $level, $depth, &$out)
    {
        $indentation = Helpers::indent($level);

        $perms = $this->humanReadablePermissions($var->getPerms());
        $owner = posix_getpwuid($var->getOwner());
        $group = posix_getgrgid($var->getGroup());

        /* @var $var \SplFileInfo */
        $input = array(
            'pathname'    => $var->getPathname(),
            'realpath'    => $var->getRealPath(),
            'type'        => $var->getType(),
            'size'        => $var->getSize(),
            'permissions' => array(
                'perms' => $perms,
                'owner' => $owner['name'],
                'group' => $group['name']
            ),
            'times'       => array(
                'created'  => $var->getCTime(),
                'accessed' => $var->getATime(),
                'modified' => $var->getMTime(),
            )
        );

        foreach ($input as $key => $value) {
            $key   = Helpers::encodeKey($key);
            $value = $this->getManager()->dump($value, $level + 1, $depth - 1);
            $out[] = PHP_EOL . $indentation . $key . '<span class="dump_arg_keyword"> =&gt; </span>' . $value;
        }

        return true;
    }

    protected function verifyCustomCondition($var)
    {
        return $var instanceof \SplFileInfo;
    }

    private function humanReadablePermissions($perms)
    {
        if (($perms & 0xC000) == 0xC000) {
            // Socket
            $info = 's';
        } elseif (($perms & 0xA000) == 0xA000) {
            // Symbolic Link
            $info = 'l';
        } elseif (($perms & 0x8000) == 0x8000) {
            // Regular
            $info = '-';
        } elseif (($perms & 0x6000) == 0x6000) {
            // Block special
            $info = 'b';
        } elseif (($perms & 0x4000) == 0x4000) {
            // Directory
            $info = 'd';
        } elseif (($perms & 0x2000) == 0x2000) {
            // Character special
            $info = 'c';
        } elseif (($perms & 0x1000) == 0x1000) {
            // FIFO pipe
            $info = 'p';
        } else {
            // Unknown
            $info = 'u';
        }

        // Owner
        $info .= ($perms & 0x0100) ? 'r' : '-';
        $info .= ($perms & 0x0080) ? 'w' : '-';
        $info .= ($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x') : (($perms & 0x0800) ? 'S' : '-');

        // Group
        $info .= ($perms & 0x0020) ? 'r' : '-';
        $info .= ($perms & 0x0010) ? 'w' : '-';
        $info .= ($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x') : (($perms & 0x0400) ? 'S' : '-');

        // World
        $info .= ($perms & 0x0004) ? 'r' : '-';
        $info .= ($perms & 0x0002) ? 'w' : '-';
        $info .= ($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x') : (($perms & 0x0200) ? 'T' : '-');

        return $info . ' (' . decoct($perms & 0777) . ')';
    }
}
