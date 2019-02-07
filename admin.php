<?php

use dokuwiki\Form\Form;

/**
 * Pagepacks Plugin: Initial installation of page structure for openschulportfolio
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Frank Schiebel <frank@linuxmuster.net>
 * @author     Andreas Gohr <gohr@cosmocode.de>
 */
class admin_plugin_pagepacks extends DokuWiki_Admin_Plugin
{
    protected $packdir;

    /**
     * admin_plugin_pagepacks constructor.
     */
    public function __construct()
    {
        $this->packdir = dirname(mediaFN($this->getConf('pagepacks_src') . ':foo'));
    }

    /** @inheritdoc */
    public function handle()
    {
        global $INPUT;

        $pack = $INPUT->post->str('pack');
        if ($pack && checkSecurityToken()) {
            $packs = $this->listPacks();
            if (isset($packs[$pack])) {
                try {
                    $this->installPack($pack);
                    msg($this->getLang('success'), 1);
                } catch (\splitbrain\PHPArchive\ArchiveIOException $e) {
                    msg($this->getLang('fail') . ' ' . $e->getMessage(), -1);
                }

            }
        }
    }


    /** @inheritdoc */
    public function html()
    {
        global $ID;
        echo sprintf($this->locale_xhtml('intro'), $this->getConf('pagepacks_src'));

        $packs = $this->listPacks();

        $form = new Form([
            'class' => 'pagepack',
            'method' => 'POST',
            'action' => wl($ID, ['do' => 'admin', 'page' => 'pagepacks'], false, '&')
        ]);
        $form->addFieldsetOpen($this->getLang('pagepacks'));
        foreach ($packs as $pack => $info) {

            $form->addRadioButton('pack', $pack)->val($pack)->addClass('radioleft');
            $form->addHTML("<p>$info</p>");
        }
        $form->addButton('unzip', $this->getLang('btn_unzip'));
        echo $form->toHTML();

    }

    /**
     * @param string $pack
     * @throws \splitbrain\PHPArchive\ArchiveIOException
     */
    protected function installPack($pack)
    {
        global $conf;

        $file = $this->packdir . '/' . $pack;

        // FIXME relying on savedir instead of using individual conf values is not ideal
        $target = $conf['savedir'];
        if (preg_match("/^\.\//", $target)) {
            $target = DOKU_INC . $target;
        }
        $target = rtrim($target, '/');


        $zip = new \splitbrain\PHPArchive\Zip();
        $zip->open($file);
        $zip->extract($target);
        $zip->close();

        // refresh cache
        // FIXME, it would be better to update the timestamps of the extracted files, but that's currently not possible
        @touch(DOKU_CONF . 'local.php');
    }

    /**
     * Returns a list of packs and their info
     *
     * @return array
     */
    protected function listPacks()
    {
        $packs = [];

        $files = glob($this->packdir . '/*.zip');
        foreach ($files as $file) {
            $fileinfo = $file . '.info';
            if (file_exists($fileinfo)) {
                $info = io_readfile($fileinfo);
            } else {
                $info = '';
            }
            $base = basename($file);
            $packs[$base] = $info;
        }

        ksort($packs);
        return $packs;
    }


}
