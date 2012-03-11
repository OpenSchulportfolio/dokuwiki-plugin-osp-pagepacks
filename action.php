<?php
/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Esther Brunner <wikidesign@gmail.com>
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
                'author' => 'Gina Häußge, Michael Klier, Esther Brunner',
                'email'  => 'dokuwiki@chimeric.de',
                'date'   => @file_get_contents(DOKU_PLUGIN.'task/VERSION'),
                'name'   => 'Task Plugin (action component)',
                'desc'   => 'Brings task management to DokuWiki',
                'url'    => 'http://wiki.splitbrain.org/plugin:task',
                );
    }

    /**
     * register the eventhandlers
     */
    function register(&$contr) {
        $contr->register_hook('ACTION_ACT_PREPROCESS',
                            'BEFORE',
                            $this,
                            'handle_act_preprocess',
                            array());
    }

    /**
     * Checks if 'newentry' was given as action, if so we
     * do handle the event our self and no further checking takes place
     */
    function handle_act_preprocess(&$event, $param) {
        if ($event->data != 'unzip' && $event->data != 'checkpack') return;

        // we can handle it -> prevent others
        $event->stopPropagation();
        $event->preventDefault();    

        switch($event->data) {
            case 'unzip':
                $event->data = $this->_unzip();
                break;
            case 'checkpack':
                $event->data = $this->_checkpack();
                break;
        }
    }

    /**
     * Unzip selected pagepack file
     */
    function _unzip() {
        global $ID;
        global $INFO;
        $file = $_REQUEST['packzipfile'];
        msg("hallo $file");

        if (isset($file)) {
            # die Bestimmung des pfads sollte in eine helper funktion, das ist nur proof of function
            system('unzip -o -d /home/linuxmuster-portfolio/data/ '  . '/home/linuxmuster-portfolio/data/media/wiki/pagepacks/'.$file .' > /dev/null 2>&1');
        }
        return 'show';

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
