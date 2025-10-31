<?php

require __DIR__ . '/vendor/autoload.php';

use App\Controllers\ArcticleController;
use App\Core\FileManager;
use App\Models\Article;
use App\Views\ArticleView;
use App\Core\Helper;


$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

$h = new Helper();
$file_manager = new FileManager();
$a = $file_manager->readFile('/posts/posts2.md');
$b = $file_manager->readFile('/posts/posts1.md');
echo $h->dd($a);




$article = new Article();
$article_view = new ArticleView();
$article_controller = new ArcticleController($article, $article_view);

$uri = $_SERVER['REQUEST_URI'];
switch ($uri) {
    case '/':
        include_once('./templates/pages/index.php');
        break;
    case '/articles':
        $article_controller->showArticlesList();
        break;
    case '/calc':
        include_once('./templates/pages/calc.php');
        break;
    default:
        include_once('./templates/pages/404.php');
        break;
}
