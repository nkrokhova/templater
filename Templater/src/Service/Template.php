<?php

namespace Templater\Service;

/**
 * Class Template
 * @package Templater\Service
 */
class Template
{
    /** @var string $conditionalRegex */
    protected $conditionalRegex = '/\{\s*(if|elseif)\s*((?:\()?(.*?)(?:\))?)\s*\}/ms';

    /** @var string $conditionalElseRegex */
    protected $conditionalElseRegex = '/\{\s*else\s*\}/ms';

    /** @var string $conditionalEndRegex */
    protected $conditionalEndRegex = '/\{\s*\/if\s*\}/ms';

    /** @var string $variableRegex */
    protected $variableRegex = '[a-zA-Z0-9_]+';

    /** @var string $variableTagRegex */
    protected $variableTagRegex = '/\{\s*([a-zA-Z0-9_]+)\s*\}/m';

    /** @var array $conditionalData */
    protected $conditionalData = [];

    /**
     * @param string $view
     * @param array $params
     * @throws \Exception
     */
    public function render(string $view, array $params = [])
    {
        ob_start();
        $file = __DIR__ . '/../../' . $view;

        $content = file_get_contents($file);

        $content = $this->parseConditionals($content, $params);
        $content = $this->parseVariables($content, $params);

        file_put_contents($file, $content);

        include $file;
        ob_end_flush();
    }

    /**
     * @param string $text
     * @param array $data
     * @return null|string|string[]
     * @throws \Exception
     */
    protected function parseConditionals(string $text, array $data = [])
    {
        preg_match_all($this->conditionalRegex, $text, $matches, PREG_SET_ORDER);

        $this->conditionalData = $data;

        foreach ($matches as $match) {
            $condition = $match[2];

            $condition = preg_replace_callback('/\b('.$this->variableRegex.')\b/', [$this, 'processConditionVar'], $condition);

            $conditional = '<?php ';
            $conditional .= $match[1].' ('.$condition.')';
            $conditional .= ': ?>';

            $text = preg_replace('/'.preg_quote($match[0], '/').'/m', addcslashes($conditional, '\\$'), $text, 1);
        }

        $text = preg_replace($this->conditionalElseRegex, '<?php else: ?>', $text);
        $text = preg_replace($this->conditionalEndRegex, '<?php endif; ?>', $text);

        return $text;
    }

    /**
     * @param string $text
     * @param array $data
     * @return mixed|string
     */
    public function parseVariables(string $text, array $data)
    {
        if (preg_match_all($this->variableTagRegex, $text, $data_matches)) {
            foreach ($data_matches[1] as $index => $var) {
                $val = $this->getVariable($var, $data);
                if (!empty($var)) {
                    $text = str_replace($data_matches[0][$index], $val, $text);
                }
            }
        }

        return $text;
    }

    /**
     * @param $match
     * @return mixed|null
     */
    protected function processConditionVar($match)
    {
        $var = is_array($match) ? $match[0] : $match;

        return $this->getVariable($var, $this->conditionalData);
    }

    /**
     * @param $key
     * @param $data
     * @param null $default
     * @return mixed|null
     */
    protected function getVariable($key, $data, $default = null)
    {
        if (is_array($data)) {
            if (!array_key_exists($key, $data)) {
                return $default;
            }

            $data = $data[$key];
        } elseif (is_object($data)) {
            if (!isset($data->{$key})) {
                return $default;
            }

            $data = $data->{$key};
        } else {
            return $default;
        }

        return $data;
    }
}
