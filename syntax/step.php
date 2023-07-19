<?php

/**
 * DokuWiki Plugin stepbystep (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  saggi <saggi@gmx.de>
 */
class syntax_plugin_stepbystep_step extends \dokuwiki\Extension\SyntaxPlugin
{
    protected $tagcount = 1;

    /**
     * Get plugin and component name
     * @return string
     */
    public function getMode(): string
    {
        return 'plugin_' . $this->getPluginName() . '_' . $this->getPluginComponent();
    }

    /** @inheritDoc */
    public function getType()
    {
        return 'substition';
    }

    /** @inheritDoc */
    public function getPType()
    {
        return 'normal';
    }

    /** @inheritDoc */
    public function getSort()
    {
        return 410;
    }

    function getAllowedTypes()
    {
        return array('container', 'formatting', 'substition', 'protected', 'disabled', 'paragraphs');
    }

    /**
     * Set the EntryPattern
     * @param string $mode
     */
    public function connectTo($mode)
    {
        $this->Lexer->addEntryPattern(
            "^#{{$this->tagcount}}:.*?(?=\n.*?^:#{{$this->tagcount}}$)",
            $mode,
            $this->getmode()
        );
    }

    /**
     * Set the ExitPattern
     */
    public function postConnect()
    {
        $this->Lexer->addExitPattern("^:#{{$this->tagcount}}$", $this->getmode());
    }

    /**
     * Handle the match
     * @param string       $match   The match of the syntax
     * @param int          $state   The state of the handler
     * @param int          $pos     The position in the document
     * @param Doku_Handler $handler The handler
     * @return array Data for the renderer
     */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        //var_dump($match);
        switch ($state) {
            case DOKU_LEXER_ENTER :
                $check = false;
                $match          = trim(substr($match, $this->tagcount + 1));// returns match after '#{$tagcount}:'
                $data['title']  = hsc(trim($match));
                $data['anchor'] = str_replace([':', '.'], '_', cleanID($data['title']));
                return [$state, $data];
            case DOKU_LEXER_UNMATCHED :
                return [$state, $match];
            case DOKU_LEXER_EXIT :
                return [$state, ''];
        }
        return [];
    }

    /**
     * Create output
     *
     * @param string        $mode     string     output format being rendered
     * @param Doku_Renderer $renderer the current renderer object
     * @param array         $data     data created by handler()
     * @return  boolean                 rendered correctly?
     */
    public function render($mode, Doku_Renderer $renderer, $data)
    {
        if ($mode !== 'xhtml') {
            return false;
        }
        list($state, $indata) = $data;
        switch ($state) {
            case DOKU_LEXER_ENTER :
                $renderer->doc .= '<div class="stepbystep">' . DOKU_LF;
                if (is_a($renderer, 'renderer_plugin_dw2pdf')) {
                    $renderer->doc .= '<div id="' . $indata['anchor'] . '" class="steptitle">' . $indata['title'] . '</div>' . DOKU_LF;
                } else {
                    $renderer->doc .= '<button id="' . $indata['anchor'] . '" class="collapsible">' . $indata['title'] . '</button>' . DOKU_LF;
                }
                //$renderer->doc .= '<button class="collapsible"><span class="steptitle">' . $indata['title'] . '</span></button>' . DOKU_LF;
                $renderer->doc .= '<div class="content">' . DOKU_LF;
                break;
            case DOKU_LEXER_UNMATCHED :
                $renderer->cdata($indata);
                break;
            case DOKU_LEXER_EXIT :
                $renderer->doc .= '</div>' . DOKU_LF;
                $renderer->doc .= '</div>' . DOKU_LF;
                break;
        }
        return true;
    }
}

