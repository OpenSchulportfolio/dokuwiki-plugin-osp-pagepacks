<?php
/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Frank Schiebel <frank@linuxmuster.net>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'action.php');

class action_plugin_pagepacks extends DokuWiki_Action_Plugin {

    /**
     * return some info
     */
    function getInfo() {
        return array(
                'author' => 'Frank Schiebel',
                'email'  => 'frank@linuxmuster.net',
                'date'   => '2012-07-01',
                'name'   => 'Pagepacks Plugin',
                'desc'   => 'Pagepacks for initial Wiki-structures',
                'url'    => 'http://www.openschulportfolio.de',
                );
    }

    /**
     * register the eventhandlers
     */
    function register(Doku_Event_Handler $contr) {
        $contr->register_hook('ACTION_ACT_PREPROCESS',
                            'BEFORE',
                            $this,
                            'handle_act_preprocess',
                            array());
    }

    /**
     */
    function handle_act_preprocess(Doku_Event $event, $param) {
        global $ID;
        if ($event->data != 'unzip') return;
        if (auth_quickaclcheck($ID) < AUTH_ADMIN) return;

        // we can handle it -> prevent others
        $event->stopPropagation();
        $event->preventDefault();

        switch($event->data) {
            case 'unzip':
                $event->data = $this->_unzip();
                break;
        }
    }

    /**
     * Unzip selected pagepack file
     */
    function _unzip() {
        global $ID;
        global $conf;
        global $INFO;
        $file = $_REQUEST['packzipfile'];
        $datadir = $conf['savedir'];
        if ( preg_match("/^\.\//", "$datadir")) {
            $datadir = DOKU_INC . $datadir;
        }
        $sourcefile = $datadir .'/media/wiki/pagepacks/'. $file;
        $targetdir = $datadir;

        #msg("Unpacking $sourcefile to $targetdir");


        if (isset($file)) {
            $this->decompress($sourcefile,$targetdir);
        }
        return 'show';

    }

    /**
     * Decompress an archive (adopted from plugin manager)
     *
     * @author Christopher Smith <chris@jalakai.co.uk>
     * @author Michael Klier <chi@chimeric.de>
     */
    function decompress($file, $target) {

        // need to source plugin manager because otherwise the ZipLib doesn't work
        // FIXME fix ZipLib.class.php
        //require_once(DOKU_INC.'lib/plugins/plugin/admin.php');

        // decompression library doesn't like target folders ending in "/"
        if(substr($target, -1) == "/") $target = substr($target, 0, -1);

        $ext = substr($file, strrpos($file,'.')+1);

        if ($ext == 'zip') {

            require_once(DOKU_INC."inc/ZipLib.class.php");

            $zip = new ZipLib();
            $ok  = $zip->Extract($file, $target);

            if($ok) {
                #msg("Unpacking OK");
                touch(DOKU_CONF . "/dokuwiki.php");
                return true;
            } else {
                return false;
            }

        }

        // unsupported file type
        return false;
    }

    /**
     * Changes the status of a task
     */
    function _checkpack() {
        global $ID;
        global $INFO;

        $status = $_REQUEST['status'];
        return 'show';
    }
}
// vim:ts=4:sw=4:et:enc=utf-8:
