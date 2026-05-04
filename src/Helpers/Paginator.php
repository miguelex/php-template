<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Generador de paginación.
 *
 * Uso en un controller:
 *   $paginator = new Paginator(total: 150, perPage: 15, currentPage: 2);
 *   $paginator->offset(); // 15 — usar en la query SQL
 *
 * En la vista:
 *   echo Paginator::render($paginator);
 *
 * O con el repositorio:
 *   $result = $repo->paginate(page: $page, perPage: 15);
 *   $paginator = Paginator::fromArray($result);
 */
final class Paginator
{
    public readonly int $lastPage;
    public readonly int $offset;
    public readonly bool $hasPages;
    public readonly bool $hasPrevious;
    public readonly bool $hasNext;

    public function __construct(
        public readonly int $total,
        public readonly int $perPage,
        public readonly int $currentPage,
        public readonly string $urlPattern = '?page={page}',
    ) {
        $this->lastPage   = max(1, (int) ceil($total / $perPage));
        $this->offset     = ($currentPage - 1) * $perPage;
        $this->hasPages   = $this->lastPage > 1;
        $this->hasPrevious = $currentPage > 1;
        $this->hasNext    = $currentPage < $this->lastPage;
    }

    /**
     * @param array{total: int, page: int, perPage: int, lastPage: int} $data
     */
    public static function fromArray(array $data, string $urlPattern = '?page={page}'): self
    {
        return new self(
            total:       $data['total'],
            perPage:     $data['perPage'],
            currentPage: $data['page'],
            urlPattern:  $urlPattern,
        );
    }

    public static function fromRequest(int $total, int $perPage = 15): self
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));

        return new self(total: $total, perPage: $perPage, currentPage: $page);
    }

    public function url(int $page): string
    {
        return str_replace('{page}', (string) $page, $this->urlPattern);
    }

    public function previousUrl(): string
    {
        return $this->url(max(1, $this->currentPage - 1));
    }

    public function nextUrl(): string
    {
        return $this->url(min($this->lastPage, $this->currentPage + 1));
    }

    /**
     * Genera los números de página visibles (con '...' para rangos).
     *
     * @return list<int|string>
     */
    public function pages(int $window = 2): array
    {
        if ($this->lastPage <= 1) {
            return [1];
        }

        $pages = [];
        $from  = max(1, $this->currentPage - $window);
        $to    = min($this->lastPage, $this->currentPage + $window);

        if ($from > 1) {
            $pages[] = 1;
            if ($from > 2) {
                $pages[] = '...';
            }
        }

        for ($i = $from; $i <= $to; $i++) {
            $pages[] = $i;
        }

        if ($to < $this->lastPage) {
            if ($to < $this->lastPage - 1) {
                $pages[] = '...';
            }
            $pages[] = $this->lastPage;
        }

        return $pages;
    }

    /**
     * Renderiza HTML de paginación básico.
     * Sobreescribir en la vista si se necesita más control.
     */
    public static function render(self $p, string $class = 'pagination'): string
    {
        if (!$p->hasPages) {
            return '';
        }

        $html  = "<nav class='{$class}' aria-label='Paginación'><ul>";

        // Anterior
        if ($p->hasPrevious) {
            $html .= "<li><a href='{$p->previousUrl()}' aria-label='Anterior'>&laquo;</a></li>";
        }

        // Números
        foreach ($p->pages() as $page) {
            if ($page === '...') {
                $html .= "<li><span>&hellip;</span></li>";
            } else {
                $active = $page === $p->currentPage ? " class='active' aria-current='page'" : '';
                $html  .= "<li{$active}><a href='{$p->url((int)$page)}'>{$page}</a></li>";
            }
        }

        // Siguiente
        if ($p->hasNext) {
            $html .= "<li><a href='{$p->nextUrl()}' aria-label='Siguiente'>&raquo;</a></li>";
        }

        $html .= '</ul></nav>';

        return $html;
    }
}
