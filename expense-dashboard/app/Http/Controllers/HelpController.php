<?php

namespace App\Http\Controllers;

use App\Services\HelpArticleService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\View\View;

/**
 * Renders the help / onboarding article listing (`GET /help`).
 */
class HelpController extends Controller
{
    /**
     * Fetch help articles from WordPress and render the listing.
     *
     * If WordPress is unreachable, renders the same view with an empty
     * article list and an `error` message instead of letting the exception
     * propagate (see `HelpArticleService::getArticles()` for what can throw).
     *
     * @param  HelpArticleService  $service  Injected by the container.
     * @return View The `help.index` view, given:
     *              - `articles`: help articles
     *              - `error`: a user-facing message if WordPress was unreachable, else null
     */
    public function index(HelpArticleService $service): View
    {
        try {
            $articles = $service->getArticles();
        } catch (ConnectionException|RequestException) {
            return view('help.index', [
                'articles' => collect(),
                'error' => 'Unable to connect to the WordPress API. Make sure it is running and try again.',
            ]);
        }

        return view('help.index', [
            'articles' => $articles,
            'error' => null,
        ]);
    }
}
