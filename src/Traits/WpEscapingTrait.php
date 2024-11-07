<?php

namespace Lxbdr\WpTemplateHelper\Traits;

trait WpEscapingTrait
{

    public function _attr( string $key ): string {
        return \esc_attr( $this->getNested( $key ) ?? '' );
    }

    public function attr( string $key ) {
        echo $this->_attr( $key );
    }

    public function _url( string $key ): string {
        return \esc_url( $this->getNested( $key ) ?? '' );
    }

    public function url( string $key ): void {
        echo $this->_url( $key );
    }

    public function _html( string $key ): string {
        return \esc_html( $this->getNested( $key ) ?? '' );
    }

    public function html( string $key ) {
        echo $this->_html( $key );
    }

    public function _safeHtml( string $key ): string {
        return \wp_kses_post( $this->getNested( $key ) ?? '' );
    }

    public function safeHtml( string $key ) {
        echo $this->_safeHtml( $key );
    }

    public function _js(string $key): string {
        return \esc_js($this->getNested($key) ?? '');
    }

    public function js(string $key): void {
        echo $this->_js($key);
    }

    public function _xml(string $key): string {
        return \esc_xml($this->getNested($key) ?? '');
    }

    public function xml(string $key): void {
        echo $this->_xml($key);
    }

    /**
     * Get nested value from data array.
     * This method should be implemented by the using class.
     *
     * @param string $key The nested key to retrieve
     * @return mixed The value
     */
    #[\ReturnTypeWillChange]
    abstract protected function getNested(string $key, string $separator = '.'): mixed;

}
