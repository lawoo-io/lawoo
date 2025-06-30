<?php

namespace Modules\Web\Http\Livewire\Breadcrumbs;


use Illuminate\Support\Facades\Route;
use Livewire\Component;
use Illuminate\Support\Facades\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Breadcrumbs extends Component
{
    public $breadcrumbs = [];

    public string $pageTitle = '';

    protected $listeners = ['routeChanged' => 'buildBreadcrumbs'];

    /**
     * Mounts the component, receiving the page title.
     * @param string|null $pageTitle The title from the parent layout.
     */
    public function mount($pageTitle = null)
    {
        $this->pageTitle = $pageTitle;
        $this->buildBreadcrumbs();
    }

    /**
     * Baut das Breadcrumbs-Array ausschließlich basierend auf der aktuellen URL.
     */
    public function buildBreadcrumbs()
    {
        $this->breadcrumbs = []; // Immer neu initialisieren
        $currentFullUrl = Request::fullUrl(); // Die komplette URL mit Query-Parametern
        $path = parse_url($currentFullUrl, PHP_URL_PATH); // Nur der Pfadteil (z.B. /lawoo/users/1)
        $queryString = parse_url($currentFullUrl, PHP_URL_QUERY); // Nur der Query-String (z.B. f[a]=true)
        $queryString = $queryString ? '?' . $queryString : ''; // Mit führendem '?'

        // 1. Home-Breadcrumb (Basispunkt für /lawoo)
        $this->breadcrumbs[] = [
            'name'   => 'Home',
            'url'    => url('/lawoo') . $queryString, // URL mit Query-Parametern
            'active' => (Request::path() === 'lawoo' || Request::path() === '/'), // Aktiv, wenn direkt auf Basis-URL
        ];

        // 2. Zerlegen des Pfades NACH '/lawoo' in Segmente
        $normalizedPath = trim($path, '/'); // Entfernt führende/nachfolgende Slashes
        $segmentsToProcess = [];
        $foundLawooBase = false;

        foreach (explode('/', $normalizedPath) as $part) {
            if (empty($part)) continue; // Leere Teile überspringen

            if ($part === 'lawoo' && !$foundLawooBase) {
                $foundLawooBase = true;
                continue; // 'lawoo' Segment selbst nicht als eigenen Breadcrumb hinzufügen
            }

            if ($foundLawooBase || $normalizedPath === 'lawoo') { // Nur Segmente nach 'lawoo' verarbeiten
                $segmentsToProcess[] = $part;
            }
        }

        $currentRelativePathAccumulator = ''; // Baut Pfad relativ zu /lawoo auf (z.B. /users, /users/1)
        foreach ($segmentsToProcess as $index => $segment) {
            if (empty($segment)) continue;

            $currentRelativePathAccumulator .= '/' . $segment;
            $absoluteSegmentPath = '/lawoo' . $currentRelativePathAccumulator; // Absoluter Pfad (z.B. /lawoo/users)

            // Name für den Breadcrumb bestimmen
            $name = ucwords(str_replace(['-', '_'], ' ', $segment)); // Standard: "users" -> "Users"

            // Die URL für den Breadcrumb muss den gesamten Query-String der aktuellen Seite enthalten.
            $breadcrumbLinkUrl = url($absoluteSegmentPath) . $queryString;

            $this->breadcrumbs[] = [
                // Für den letzten Breadcrumb verwenden wir den übergebenen Seitentitel, falls vorhanden.
                'name'   => ($index === count($segmentsToProcess) - 1 && $this->pageTitle) ? $this->pageTitle : $name,
                'url'    => $breadcrumbLinkUrl,
                'active' => false, // Später gesetzt
            ];
        }

        // 3. 'active'-Status für den letzten Breadcrumb setzen
        if (!empty($this->breadcrumbs)) {
            $lastIndex = count($this->breadcrumbs) - 1;
            foreach ($this->breadcrumbs as $idx => $crumb) {
                $this->breadcrumbs[$idx]['active'] = ($idx === $lastIndex);
            }
        }
    }

    public function render()
    {
        return view('livewire.web.breadcrumbs.breadcrumbs');
    }
}
