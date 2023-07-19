<?php
/**
 * DokuWiki Plugin stepbystep (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  saggi <saggi@gmx.de>
 */

class syntax_plugin_stepbystep_innerstep extends syntax_plugin_stepbystep_step
{
    protected $tagcount = 2;

    public function getMode(): string
    {
        return 'plugin_' . $this->getPluginName() . '_' . $this->getPluginComponent();
    }

}