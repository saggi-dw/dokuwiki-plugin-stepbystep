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

    // default name and style definitions
    protected $options = [
        'collapsible_class'        => ' class="stepbystep_collapsible"',
        'collapsible_class_active' => ' class="stepbystep_collapsible active"',
        'content_height'           => '',
        'content_height_max'       => ' style="max-height: fit-content;"',
        'height_preview'           => ' style="--preview: %s;"',
        'container'                => 'stepbystep',
        'container-noback'         => 'nobackground-stepbystep'
    ];

    /**
     * Get plugin and component name
     * @return string
     */
    public function getMode(): string
    {
        return sprintf("plugin_%s_%s", $this->getPluginName(), $this->getPluginComponent());
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
     * accept nesting
     * @param $mode
     * @return bool
     */
    function accepts($mode)
    {
        if ($mode == $this->getMode()) {
            return true;
        }
        return parent::accepts($mode);
    }

    /**
     * Set the EntryPattern
     * @param string $mode
     */
    public function connectTo($mode)
    {
        $this->Lexer->addEntryPattern(
            sprintf('^#{%1$d}:.*?(?=\n.*?^:#{%1$d}$)', $this->tagcount),
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
        switch ($state) {
            case DOKU_LEXER_ENTER :
                $match = trim(substr($match, $this->tagcount + 1));// returns match after '#{$tagcount}:'
                $check = sexplode('||', $match, 2);
                // set default values
                $data = [
                    'title'             => '',
                    'anchor'            => '',
                    'options'           => [],
                    'collapsible_class' => $this->options['collapsible_class'],
                    'content_height'    => $this->options['content_height'],
                    'preview'           => '',
                    'container'         => $this->options['container']
                ];
                if ($check[0]) {
                    $data['title']  = hsc($check[0]);
                    $data['anchor'] = str_replace([':', '.'], '_', cleanID($data['title']));
                    $data['anchor'] = substr($data['anchor'], 0, 40);
                }
                $data = $this->checkOptions($check[1], $data);
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
     * @return  bool                 rendered correctly?
     */
    public function render($mode, Doku_Renderer $renderer, $data)
    {
        if ($mode !== 'xhtml') {
            return false;
        }
        list($state, $indata) = $data;
        switch ($state) {
            case DOKU_LEXER_ENTER :
                $type          = 'button';
                $renderer->doc .= '<div class="' . $indata['container'] . '">' . DOKU_LF;
                if (is_a($renderer, 'renderer_plugin_dw2pdf')) {
                    $type = 'div';
                }
                // Create the Button/Div e.g.: <button id="anchor"  class="stepbystep_collapsible">title</button>
                $renderer->doc .= sprintf(
                    '<%s id="%s"%s>%s</%s>%s',
                    $type,
                    $indata['anchor'],
                    $indata['collapsible_class'],
                    $indata['title'],
                    $type,
                    DOKU_LF
                );
                $renderer->doc .= '<div class="stepbystep_content"' . $indata['content_height'] . $indata['preview'] . '>' . DOKU_LF;
                $renderer->doc .= '<p>' . DOKU_LF;
                break;
            case DOKU_LEXER_UNMATCHED :
                $renderer->cdata($indata);
                break;
            case DOKU_LEXER_EXIT :
                $renderer->doc .= DOKU_LF . '</p>' . DOKU_LF;
                $renderer->doc .= '</div>' . DOKU_LF;
                $renderer->doc .= '</div>' . DOKU_LF;
                break;
        }
        return true;
    }

    /**
     * check if value of size is a valid length
     * @param $size
     * @return string|null
     */
    public function checkSize($size): ?string
    {
        $size = hsc($size);
        // check if value of size is a valid length
        $result = preg_match(
            '/^(\d*\.?\d)+(|em|ex|ch|rem|vw|vh|vmin|vmax|cm|mm|in|px|pt|pc)$/i',
            $size,
            $matches
        );
        if (!$result) {
            return null;
        }
        $return_size = $matches[0];
        // check if a unit is given, add px if not
        if (preg_match('/^(\d*\.?\d)+$/', $return_size)) {
            $return_size .= 'px';
        }
        return $return_size;
    }

    /**
     * check and pass options to the data array
     * @param $options
     * @param $data
     * @return array
     */
    public function checkOptions($options, $data): array
    {
        if (!$options) {
            return $data;
        }
        // pass all options to the renderer
        $data['options'] = explode(' ', $options);
        // update default values by option
        foreach ($data['options'] as $option) {
            switch ($option) {
                case 'open':
                    $data['collapsible_class'] = $this->options['collapsible_class_active'];
                    $data['content_height']    = $this->options['content_height_max'];
                    $data['preview']           = '';
                    break;
                case (preg_match('/preview:.*/', $option) ? true : false) :
                    $preview = sexplode(':', $option, 2);
                    $size    = $this->checkSize($preview[1]);
                    if (($preview[1]) && ($size)) {
                        $data['collapsible_class'] = $this->options['collapsible_class'];
                        $data['content_height']    = $this->options['content_height'];
                        $data['preview']           = sprintf($this->options['height_preview'], $size);
                    }
                    break;
                case 'noframe' :
                    $data['container'] = $this->options['container-noback'];
                    break;
            }
        }
        return $data;
    }
}

