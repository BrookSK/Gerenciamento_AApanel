<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $template, array $data = []): string
    {
        $basePath = dirname(__DIR__, 2);
        $viewPath = $basePath . '/app/Views/' . ltrim($template, '/') . '.php';

        if (!is_file($viewPath)) {
            throw new \RuntimeException('View not found: ' . $viewPath);
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewPath;
        $html = (string)ob_get_clean();

        if (stripos($html, '</head>') !== false && stripos($html, '/assets/app.css') === false) {
            $linkTag = "    <link rel=\"stylesheet\" href=\"/assets/app.css\">\n";
            $html = preg_replace('/\s*<\/head>/i', "\n" . $linkTag . "</head>", $html, 1) ?? $html;
        }

        $isLogin = stripos($template, 'login') !== false;
        $uri = (string)($_SERVER['REQUEST_URI'] ?? '');
        $isPortal = str_starts_with($uri, '/portal') || str_starts_with($template, 'portal/');
        $area = $isPortal ? 'portal' : 'admin';

        if (stripos($html, '<body') !== false && stripos($html, '</body>') !== false && stripos($html, 'data-app-layout=') === false) {
            $html = preg_replace_callback('/<body\b([^>]*)>([\s\S]*?)<\/body>/i', function (array $m) use ($isLogin, $area) {
                $bodyAttrs = (string)($m[1] ?? '');
                $bodyInner = (string)($m[2] ?? '');

                $hasClass = stripos($bodyAttrs, 'class=') !== false;
                $classAdd = $isLogin ? ' page-login' : ' page-app';
                if ($hasClass) {
                    $bodyAttrs = preg_replace_callback('/\bclass=("|\')([^"\']*)(\1)/i', function (array $cm) use ($classAdd) {
                        $q = (string)($cm[1] ?? '"');
                        $existing = trim((string)($cm[2] ?? ''));
                        $extra = trim((string)$classAdd);
                        $merged = trim($existing . ' ' . $extra);
                        return 'class=' . $q . $merged . $q;
                    }, $bodyAttrs, 1) ?? $bodyAttrs;
                } else {
                    $bodyAttrs .= ' class="' . trim($classAdd) . '"';
                }

                $brand = $area === 'portal' ? 'Portal do Cliente' : 'Painel';
                $nav = '';
                if ($area === 'portal') {
                    $nav = '<a href="/portal">Início</a><a href="/portal/emails">Meus e-mails</a><a href="/portal/plans">Planos</a><a href="/portal/tickets">Chamados</a>';
                } else {
                    $nav = '<a href="/">Início</a><a href="/clients">Clientes</a><a href="/subscriptions">Assinaturas</a><a href="/plans">Planos</a><a href="/tickets">Chamados</a><a href="/aapanel-servers">Servidores</a><a href="/integration-logs">Logs</a><a href="/settings">Configurações</a>';
                }

                $topbar = $isLogin ? '' : (
                    '<header class="app-topbar" data-app-layout="1">'
                    . '<div class="app-topbar-inner">'
                    . '<div class="app-brand">' . htmlspecialchars($brand, ENT_QUOTES, 'UTF-8') . '</div>'
                    . '<nav class="app-nav">' . $nav . '</nav>'
                    . '</div>'
                    . '</header>'
                );

                $wrapped = $topbar
                    . '<main class="app-main" data-app-layout="1">'
                    . '<div class="app-container" data-app-layout="1">'
                    . $bodyInner
                    . '</div>'
                    . '</main>';

                return '<body' . $bodyAttrs . '>' . $wrapped . '</body>';
            }, $html, 1) ?? $html;
        }

        return $html;
    }
}
