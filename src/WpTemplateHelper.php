<?php

namespace Lxbdr\WpTemplateHelper;

class WpTemplateHelper implements \ArrayAccess
{

    protected array $data = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public static function fromObject(object $data): WpTemplateHelper
    {
        return new self(get_object_vars($data));
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function has(string $key): bool
    {
        return $this->getNested($key) !== null;
    }

    public function notEmpty(string $key): bool
    {
        return !$this->empty($key);
    }

    public function empty(string $key): bool
    {
        return empty($this->getNested($key));
    }

    public function get($key)
    {
        return $this->getNested($key) ?? '';
    }

    public function set($key, $value)
    {
        if ($key === null) {
            return;
        }
        $this->data[$key] = $value;
    }

    /**
     * Get a nested value by specifying the key as parent.child
     *
     * return null if value does not exist
     *
     * @param string $key
     * @param string $separator
     * @return mixed
     */
    protected function getNested(string $key, string $separator = '.')
    {
        return array_reduce(
            explode($separator, $key),
            function ($agg, $value) {
                return $agg[$value] ?? null;
            },
            $this->data
        );
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }

    public function dump($key = null)
    {
        if (!$key) {
            var_dump($this->data);

            return;
        }
        var_dump($this->getNested($key));
    }

    public function _attr(string $key): string
    {
        return \esc_attr($this->getNested($key) ?? '');
    }

    public function attr(string $key)
    {
        echo $this->_attr($key);
    }

    public function _url(string $key): string
    {
        return \esc_url($this->getNested($key) ?? '');
    }

    public function url(string $key): void
    {
        echo $this->_url($key);
    }

    public function _html(string $key): string
    {
        return \esc_html($this->getNested($key) ?? '');
    }

    public function html(string $key)
    {
        echo $this->_html($key);
    }

    public function _safeHtml(string $key): string
    {
        return \wp_kses_post($this->getNested($key) ?? '');
    }

    public function safeHtml(string $key)
    {
        echo $this->_safeHtml($key);
    }

    public function sprintf($str, $key): string
    {
        return sprintf($str, $this->getNested($key) ?? '');
    }

    public function printf($str, $key)
    {
        echo $this->sprintf($str, $key);
    }

    public function raw($key)
    {
        echo $this->getNested($key) ?? '';
    }

    public function __invoke($key)
    {
        echo \esc_html($this->getNested($key) ?? '');
    }

    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetExists($offset): bool
    {
        return !!$this->getNested($offset);
    }

    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->getNested($offset);
    }

    public static function _clsx($value)
    {
        if (is_string($value)) {
            return $value;
        } elseif (is_array($value)) {
            $tmp = [];
            foreach ($value as $k => $v) {
                if (is_numeric($k)) {
                    // non-associative array
                    // recurse each value
                    $tmp[] = static::_clsx($v);
                } else {
                    // associative array
                    // add key if value is truthy
                    if ($v) {
                        $tmp[] = $k;
                    }
                }
            }

            return implode(' ', array_filter($tmp));
        }

        return '';
    }


    public static function clsx(...$arguments)
    {
        echo static::_clsx($arguments);
    }

    public static function _style(array $arr): string
    {
        if (empty($arr)) {
            return '';
        }
        $styles = [];

        foreach ($arr as $prop => $value) {
            // strict check empty string and false
            if ($value !== '' && $value !== false) {
                $styles[] = "${prop}: ${value};";
            }
        }

        return \esc_attr(implode(" ", $styles));
    }

    public static function style(array $arr)
    {
        echo static::_style($arr);
    }

    protected static function _attributes(array $arr): string
    {
        if (empty($arr)) {
            return '';
        }
        $atts = [];

        foreach ($arr as $att => $value) {
            $value = \esc_attr($value);
            $atts[] = "${att}=\"${value}\"";
        }

        return implode(" ", $atts);
    }

    public static function attributes($arr)
    {
        echo static::_attributes($arr);
    }

    /**
     * Returns a class for an anchor tag if link is not empty
     *
     * Returns a class that hat the methods open() and close()
     *
     * @param $link
     * @param $atts
     * @param $alternative_tag
     */
    public static function maybeAnchorTagStatic(string $link, $atts = '', string $alternative_tag = 'div')
    {
        if (is_array($atts)) {
            $atts = self::_attributes($atts);
        }

        $is_link = !empty($link);

        if ($is_link) {
            $link = \esc_url($link);

            return new class($link, $atts) {
                protected $link;
                protected $atts;

                public function __construct($link, $atts)
                {
                    $this->link = $link;
                    $this->atts = $atts;
                }

                public function open()
                {
                    echo "<a href=\"{$this->link}\" {$this->atts}>";
                }

                public function close()
                {
                    echo '</a>';
                }
            };
        }

        return new class($atts, $alternative_tag) {
            protected $atts;
            protected $tag;

            public function __construct(string $atts, $tag = 'div')
            {
                $this->atts = $atts;
                $this->tag = $tag;
            }

            public function open()
            {
                echo "<{$this->tag} {$this->atts}>";
            }

            public function close()
            {
                echo "</{$this->tag}>";
            }
        };
    }

    public function maybeAnchorTag(string $key, $atts = '', string $alternative_tag = 'div')
    {
        return self::maybeAnchorTagStatic($this->getNested($key), $atts, $alternative_tag);
    }
}
