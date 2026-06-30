<?php

namespace Rbs\Moysklad\Services;

use Rbs\Moysklad\LangMsg;

class AssetsLoader
{
    private static $instances = [];
    private $appName;
    private $modulePath;
    private $jsLibsPath;
    private $assetsDir;
    
    private function __construct(string $appName)
    {
        $this->appName = $appName;
        $this->modulePath = dirname(__DIR__, 2);
        $this->jsLibsPath = $this->modulePath . '/options/js_libs/';
        $this->assetsDir = $this->jsLibsPath . $this->appName . '/';
    }
    
    private function __clone() {}
    private function __wakeup() {}
    
    public static function getInstance(string $appName): AssetsLoader
    {
        if (!isset(self::$instances[$appName])) {
            self::$instances[$appName] = new self($appName);
        }
        
        return self::$instances[$appName];
    }
    
    private function loadAssets(): array
    {
        if (!is_dir($this->assetsDir)) {
            return [
                'success' => false,
                'error' => LangMsg::get('DIAGNOSTIC_ASSETS_NOT_FOUND'),
                'css_files' => [],
                'js_files' => []
            ];
        }
        
        $assetJsFiles = glob($this->assetsDir . '*.js');
        $assetCssFiles = glob($this->assetsDir . '*.css');
        
        return [
            'success' => true,
            'css_files' => $assetCssFiles ?: [],
            'js_files' => $assetJsFiles ?: [],
            'assets_dir' => $this->assetsDir
        ];
    }
    
    private function checkAssets(): array
    {
        $assets = $this->loadAssets();
        
        if (!$assets['success']) {
            return $assets;
        }
        
        $errors = [];
        
        if (empty($assets['css_files'])) {
            $errors[] = LangMsg::get('DIAGNOSTIC_CSS_NOT_FOUND');
        }
        
        if (empty($assets['js_files'])) {
            $errors[] = LangMsg::get('DIAGNOSTIC_JS_NOT_FOUND');
        }
        
        return [
            'success' => empty($errors),
            'errors' => $errors,
            'css_files' => $assets['css_files'],
            'js_files' => $assets['js_files'],
            'assets_dir' => $assets['assets_dir']
        ];
    }

    private function renderCss(): string
    {
        $assets = $this->loadAssets();
        
        if (!$assets['success'] || empty($assets['css_files'])) {
            return '';
        }
        
        $html = "<style>\n";
        foreach ($assets['css_files'] as $cssFile) {
            if (file_exists($cssFile)) {
                $html .= file_get_contents($cssFile) . "\n";
            }
        }
        $html .= "</style>\n";
        
        return $html;
    }
 
    private function renderJs(): string
    {
        $assets = $this->loadAssets();
        
        if (!$assets['success'] || empty($assets['js_files'])) {
            return '';
        }
        
        $html = "<script>\n";
        foreach ($assets['js_files'] as $jsFile) {
            if (file_exists($jsFile)) {
                $html .= file_get_contents($jsFile) . "\n";
            }
        }
        $html .= "</script>\n";
        
        return $html;
    }
    
    public function renderApp(bool $showErrors = true): string
    {
        $check = $this->checkAssets();
        $html = '';
        
        if (!$check['success']) {
            if ($showErrors && class_exists('CAdminMessage')) {
                foreach ($check['errors'] as $error) {
                    \CAdminMessage::ShowMessage([
                        'MESSAGE' => $error,
                        'TYPE' => 'ERROR',
                        'HTML' => true
                    ]);
                }
            }
            return $html;
        }
        
        $html .= $this->renderCss();
        $html .= "<div id='app'></div>";
        $html .= $this->renderJs();
        
        return $html;
    }

}