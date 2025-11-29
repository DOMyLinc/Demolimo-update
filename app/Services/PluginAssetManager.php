<?php

namespace App\Services;

class PluginAssetManager
{
    protected $styles = [];
    protected $scripts = [];
    protected $inlineStyles = [];
    protected $inlineScripts = [];

    /**
     * Enqueue a stylesheet
     *
     * @param string $handle Unique identifier for the stylesheet
     * @param string $src URL to the stylesheet
     * @param array $deps Array of handles this stylesheet depends on
     * @param string|null $version Version number
     * @param string $media Media type (e.g., 'all', 'print', 'screen')
     * @return void
     */
    public function enqueueStyle(string $handle, string $src, array $deps = [], ?string $version = null, string $media = 'all'): void
    {
        $this->styles[$handle] = [
            'src' => $src,
            'deps' => $deps,
            'version' => $version,
            'media' => $media,
        ];
    }

    /**
     * Enqueue a script
     *
     * @param string $handle Unique identifier for the script
     * @param string $src URL to the script
     * @param array $deps Array of handles this script depends on
     * @param string|null $version Version number
     * @param bool $inFooter Whether to load in footer
     * @return void
     */
    public function enqueueScript(string $handle, string $src, array $deps = [], ?string $version = null, bool $inFooter = true): void
    {
        $this->scripts[$handle] = [
            'src' => $src,
            'deps' => $deps,
            'version' => $version,
            'in_footer' => $inFooter,
        ];
    }

    /**
     * Add inline CSS
     *
     * @param string $handle Unique identifier
     * @param string $css CSS code
     * @return void
     */
    public function addInlineStyle(string $handle, string $css): void
    {
        $this->inlineStyles[$handle] = $css;
    }

    /**
     * Add inline JavaScript
     *
     * @param string $handle Unique identifier
     * @param string $js JavaScript code
     * @return void
     */
    public function addInlineScript(string $handle, string $js): void
    {
        $this->inlineScripts[$handle] = $js;
    }

    /**
     * Get all enqueued styles
     *
     * @return array
     */
    public function getStyles(): array
    {
        return $this->resolveDependencies($this->styles);
    }

    /**
     * Get all enqueued scripts
     *
     * @param bool $footer Whether to get footer scripts
     * @return array
     */
    public function getScripts(bool $footer = false): array
    {
        $scripts = array_filter($this->scripts, function ($script) use ($footer) {
            return $script['in_footer'] === $footer;
        });

        return $this->resolveDependencies($scripts);
    }

    /**
     * Get inline styles
     *
     * @return array
     */
    public function getInlineStyles(): array
    {
        return $this->inlineStyles;
    }

    /**
     * Get inline scripts
     *
     * @return array
     */
    public function getInlineScripts(): array
    {
        return $this->inlineScripts;
    }

    /**
     * Resolve dependencies and return assets in correct order
     *
     * @param array $assets
     * @return array
     */
    protected function resolveDependencies(array $assets): array
    {
        $resolved = [];
        $unresolved = [];

        foreach ($assets as $handle => $asset) {
            $this->resolveDependency($handle, $asset, $assets, $resolved, $unresolved);
        }

        return $resolved;
    }

    /**
     * Recursively resolve a single dependency
     *
     * @param string $handle
     * @param array $asset
     * @param array $all
     * @param array &$resolved
     * @param array &$unresolved
     * @return void
     */
    protected function resolveDependency(string $handle, array $asset, array $all, array &$resolved, array &$unresolved): void
    {
        $unresolved[$handle] = true;

        foreach ($asset['deps'] as $dep) {
            if (!isset($resolved[$dep])) {
                if (isset($unresolved[$dep])) {
                    // Circular dependency detected
                    continue;
                }

                if (isset($all[$dep])) {
                    $this->resolveDependency($dep, $all[$dep], $all, $resolved, $unresolved);
                }
            }
        }

        $resolved[$handle] = $asset;
        unset($unresolved[$handle]);
    }

    /**
     * Dequeue a style
     *
     * @param string $handle
     * @return void
     */
    public function dequeueStyle(string $handle): void
    {
        unset($this->styles[$handle]);
    }

    /**
     * Dequeue a script
     *
     * @param string $handle
     * @return void
     */
    public function dequeueScript(string $handle): void
    {
        unset($this->scripts[$handle]);
    }
}
